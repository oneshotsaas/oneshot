<?php

namespace OneShot\Auth\Services;

use OneShot\Core\Services\Base;
use OneShot\Auth\Models\User;
use OneShot\Auth\Models\Token;
use OneShot\Auth\Models\OAuthProvider;
use OneShot\Auth\Config\EmailDomains;
use CodeIgniter\Events\Events;

class Auth extends Base
{
    private User          $users;
    private Token         $tokens;
    private OAuthProvider $oauthProviders;

    public function __construct()
    {
        $this->users          = new User();
        $this->tokens         = new Token();
        $this->oauthProviders = new OAuthProvider();
    }

    // ------------------------------------------------------------------
    // Login
    // ------------------------------------------------------------------

    public function login(string $email, string $password): array
    {
        $email    = $this->normalizeEmail($email);
        $password = trim($password);
        $ip       = service('request')->getIPAddress();
        $ua       = service('request')->getUserAgent()->getAgentString();

        $user = $this->users->findByEmail($email);

        // Always run password_verify to prevent timing-based email enumeration
        $hash = $user?->password ?? '$2y$10$usesomesillystringfore2uDLvp1Ii2e./U9C8aNi2nlHsn9X0xfke';
        if ($password === '' || ! password_verify($password, $hash) || $user === null || $hash === '') {
            l(['event' => 'login_failed', 'email' => $email, 'ip' => $ip, 'ua' => $ua], 'auth');
            return ['success' => false, 'error' => __('auth.invalid_credentials', 'Invalid email or password.')];
        }

        if (($user->status ?? 'active') !== 'active') {
            return ['success' => false, 'error' => __('auth.account_inactive', 'Account is inactive.')];
        }

        // Email verification check
        $verification = option('auth.email_verification', 'disabled');
        if ($verification === 'required' && empty($user->email_verified_at)) {
            return ['success' => false, 'error' => __('auth.email_not_verified', 'Please confirm your email address before signing in.')];
        }

        $this->startSession($user);

        l(['event' => 'login_success', 'email' => $email, 'ip' => $ip, 'ua' => $ua], 'auth');
        Events::trigger('user.login', $user);

        return ['success' => true, 'user' => $user];
    }

    // ------------------------------------------------------------------
    // Register
    // ------------------------------------------------------------------

    /**
     * @param array $options {
     *   send_verification?: bool  — send verification email (default: per auth.email_verification setting)
     *   status?: string           — override user status (default: per verification setting)
     * }
     */
    public function register(array $data, array $options = []): array
    {
        $data['email'] = $this->normalizeEmail($data['email']);

        // Reserved domain used for anonymised deleted accounts — never allow registration with it
        if (str_ends_with($data['email'], '@deleted')) {
            return ['success' => false, 'error' => __('auth.email_invalid', 'Invalid email address.')];
        }

        // Disposable / blocked domain check
        $domainError = $this->checkEmailDomain($data['email']);
        if ($domainError !== null) {
            return ['success' => false, 'error' => $domainError];
        }

        if ($this->users->findByEmail($data['email']) !== null) {
            return ['success' => false, 'error' => __('auth.email_taken', 'Email already in use.')];
        }

        // Check if this email belonged to a previously deleted account
        $emailHash    = hash('sha256', $data['email']);
        $deletedMatch = $this->users->withDeleted()
            ->where('deleted_email_hash', $emailHash)
            ->where('deleted_at IS NOT NULL', null, false)
            ->first();

        if ($deletedMatch !== null) {
            $policy = option('auth.deleted_email_policy', 'allow');

            if ($policy === 'block') {
                return ['success' => false, 'error' => __('auth.email_taken', 'Email already in use.')];
            }

            if ($policy === 'flag') {
                l(['event' => 'register_deleted_email', 'email' => $data['email'], 'prev_user_id' => $deletedMatch->id], 'auth');
            }

            // 'allow' — proceed silently
        }

        $pwError = $this->validatePassword($data['password']);
        if ($pwError) {
            return ['success' => false, 'error' => $pwError];
        }

        $verification    = option('auth.email_verification', 'disabled');
        $data['password']= password_hash($data['password'], PASSWORD_DEFAULT);
        $data['role']    = $data['role'] ?? 'user';
        $data['status']  = $options['status'] ?? (($verification === 'required') ? 'pending' : 'active');

        $user = $this->users->addGet($data);

        l(['event' => 'register', 'email' => $data['email']], 'auth');
        Events::trigger('user.registered', $user);

        $sendVerification = $options['send_verification'] ?? ($verification !== 'disabled');
        if ($sendVerification) {
            $token = $this->tokens->create($user->id, 'verify_email');
            (new MailService())->sendVerification($user, $token);
        }

        return ['success' => true, 'user' => $user];
    }

    // ------------------------------------------------------------------
    // Password reset
    // ------------------------------------------------------------------

    public function resetPassword(int $userId, string $newPassword): array
    {
        $pwError = $this->validatePassword($newPassword);
        if ($pwError) {
            return ['success' => false, 'error' => $pwError];
        }

        // Invalidate all pending reset tokens for this user
        $this->tokens->invalidateAll($userId, 'reset_password');

        $this->users->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        // Destroy all sessions for this user
        session()->destroy();

        l(['event' => 'password_reset', 'user_id' => $userId], 'auth');

        return ['success' => true];
    }

    // ------------------------------------------------------------------
    // Password policy & validation
    // ------------------------------------------------------------------

    /**
     * Returns the current password policy from settings.
     * Use this anywhere you need to know the rules — controllers, views, API, JS data attributes.
     *
     * @return array{min_length:int, uppercase:bool, numbers:bool, symbols:bool}
     */
    public static function passwordPolicy(): array
    {
        return [
            'min_length' => (int)  option('auth.password_min_length',        '8'),
            'uppercase'  => (bool)(int) option('auth.password_require_uppercase', '0'),
            'numbers'    => (bool)(int) option('auth.password_require_numbers',   '0'),
            'symbols'    => (bool)(int) option('auth.password_require_symbols',   '0'),
        ];
    }

    private function validatePassword(string $password): string
    {
        $p = self::passwordPolicy();

        if (strlen($password) < $p['min_length']) {
            return sprintf(__('auth.password_too_short', 'Password must be at least %d characters.'), $p['min_length']);
        }
        if ($p['uppercase'] && (! preg_match('/[A-Z]/', $password) || ! preg_match('/[a-z]/', $password))) {
            return __('auth.password_require_uppercase_error', 'Password must contain both uppercase and lowercase letters.');
        }
        if ($p['numbers'] && ! preg_match('/[0-9]/', $password)) {
            return __('auth.password_require_numbers_error', 'Password must contain at least one number.');
        }
        if ($p['symbols'] && ! preg_match('/[^A-Za-z0-9]/', $password)) {
            return __('auth.password_require_symbols_error', 'Password must contain at least one special character.');
        }

        return '';
    }

    // ------------------------------------------------------------------
    // OAuth login / register / link
    // ------------------------------------------------------------------

    public function loginWithOAuth(string $provider, object $providerUser): array
    {
        $ip  = service('request')->getIPAddress();
        $ua  = service('request')->getUserAgent()->getAgentString();

        // Step 1: Find existing OAuth link
        $link = $this->oauthProviders->findByProvider($provider, $providerUser->id);
        if ($link !== null) {
            $user = $this->users->getById($link->user_id);
            if ($user === null || $user->status !== 'active') {
                return ['success' => false, 'error' => __('auth.account_inactive', 'Account is inactive.')];
            }

            $this->startSession($user);
            l(['event' => 'oauth_login', 'provider' => $provider, 'ip' => $ip, 'ua' => $ua], 'auth');
            return ['success' => true, 'user' => $user];
        }

        // Step 2: Link by email (only if provider verified the email)
        if (! empty($providerUser->email) && ! empty($providerUser->email_verified)) {
            $existingUser = $this->users->findByEmail($providerUser->email);
            if ($existingUser !== null) {
                $this->oauthProviders->insert([
                    'user_id'        => $existingUser->id,
                    'provider'       => $provider,
                    'provider_id'    => $providerUser->id,
                    'provider_email' => $providerUser->email,
                ]);

                l(['event' => 'account_linked', 'provider' => $provider, 'user_id' => $existingUser->id, 'ip' => $ip], 'auth');

                $this->startSession($existingUser);
                return ['success' => true, 'user' => $existingUser];
            }
        }

        // Step 3: Create new user
        $newUser = $this->users->addGet([
            'email'             => $providerUser->email,
            'email_verified_at' => ! empty($providerUser->email_verified) ? date('Y-m-d H:i:s') : null,
            'name'              => $providerUser->name ?: null,
            'password'          => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'role'              => 'user',
            'status'            => 'active',
        ]);

        $this->oauthProviders->insert([
            'user_id'        => $newUser->id,
            'provider'       => $provider,
            'provider_id'    => $providerUser->id,
            'provider_email' => $providerUser->email,
        ]);

        l(['event' => 'oauth_register', 'provider' => $provider, 'ip' => $ip, 'ua' => $ua], 'auth');
        Events::trigger('user.registered', $newUser);

        $this->startSession($newUser);
        return ['success' => true, 'user' => $newUser];
    }

    // ------------------------------------------------------------------
    // Logout
    // ------------------------------------------------------------------

    public function logout(): void
    {
        session()->destroy();
    }

    // ------------------------------------------------------------------
    // Current user
    // ------------------------------------------------------------------

    public function user(): object|null
    {
        $id = session()->get('user_id');
        if (! $id) {
            return null;
        }
        return $this->users->getById((int) $id);
    }

    // ------------------------------------------------------------------
    // Email helpers
    // ------------------------------------------------------------------

    /**
     * Normalise an email address.
     *
     * When auth.normalize_email is enabled:
     *   - strips the +tag suffix from the local part for all addresses
     *   - additionally removes dots from the local part for Gmail-family domains
     *     (per EmailDomains::$dotNormalized)
     */
    private function normalizeEmail(string $email): string
    {
        $email = mb_strtolower(trim($email));

        if (! (bool)(int) option('auth.normalize_email', '0')) {
            return $email;
        }

        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($domain === '') {
            return $email;
        }

        // Strip +tag for all providers
        if (($plusPos = strpos($local, '+')) !== false) {
            $local = substr($local, 0, $plusPos);
        }

        // Strip dots only for providers that treat them as insignificant
        if (isset(EmailDomains::$dotNormalized[$domain])) {
            $local = str_replace('.', '', $local);
        }

        return $local . '@' . $domain;
    }

    /**
     * Check whether the email domain is blocked (disposable list or custom list).
     * Returns an error string, or null if the domain is allowed.
     */
    private function checkEmailDomain(string $email): ?string
    {
        $domain = mb_strtolower(substr(strrchr($email, '@'), 1));

        // Trusted domains are always allowed — skip further checks
        if (isset(EmailDomains::$trusted[$domain])) {
            return null;
        }

        // Custom blocked domains from admin settings (any non-domain chars as separator)
        $customRaw = (string) option('auth.blocked_email_domains', '');
        if ($customRaw !== '') {
            $custom = preg_split('/[^a-z0-9.\-]+/i', $customRaw, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($custom as $blocked) {
                if (mb_strtolower($blocked) === $domain) {
                    return __('auth.email_domain_blocked', 'Registration from this email domain is not allowed.');
                }
            }
        }

        // Bundled disposable domain list
        if ((bool)(int) option('auth.block_disposable_emails', '0')) {
            if (isset(EmailDomains::$disposable[$domain])) {
                return __('auth.email_domain_blocked', 'Registration from this email domain is not allowed.');
            }
        }

        return null;
    }

    // ------------------------------------------------------------------
    // Private
    // ------------------------------------------------------------------

    private function startSession(object $user): void
    {
        session()->regenerate(true); // Prevent session fixation

        session()->set([
            'user_id'   => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->name,
            'user_lang' => $user->lang ?? 'en',
        ]);
    }
}

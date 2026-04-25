<?php

return [
    // Register page
    'create_account'    => 'Create account',
    'register_subtitle' => 'Start building your SaaS today',
    'register'          => 'Register',
    'have_account'      => 'Already have an account?',

    // Login page
    'welcome_back'      => 'Welcome back',
    'sign_in_subtitle'  => 'Sign in to your account',
    'login'             => 'Sign In',
    'no_account'        => "Don't have an account?",

    // Fields
    'name'                 => 'Name',
    'name_placeholder'     => 'Your name',
    'email'                => 'Email',
    'email_placeholder'    => 'you@example.com',
    'password'             => 'Password',
    'password_placeholder' => '••••••••',
    'new_password'         => 'New Password',
    'confirm_password'     => 'Confirm Password',

    // Errors
    'invalid_credentials' => 'Invalid email or password.',
    'account_inactive'    => 'Account is inactive.',
    'email_taken'          => 'Email already in use.',
    'email_invalid'        => 'Invalid email address.',
    'email_domain_blocked' => 'Registration from this email domain is not allowed.',
    'too_many_attempts'   => 'Too many attempts. Please try again later.',
    'password_too_short'  => 'Password must be at least %d characters.',
    'passwords_mismatch'  => 'Passwords do not match.',
    'email_not_verified'  => 'Please confirm your email address before signing in.',

    // Forgot / Reset
    'forgot_password'     => 'Forgot password?',
    'forgot_title'        => 'Reset your password',
    'forgot_subtitle'     => "Enter your email and we'll send you a reset link",
    'forgot_submit'       => 'Send Reset Link',
    'forgot_sent'         => 'If that email is registered, a reset link is on its way.',
    'reset_title'         => 'Set new password',
    'reset_subtitle'      => 'Choose a strong password',
    'reset_submit'        => 'Reset Password',
    'reset_success'       => 'Password updated. You can now sign in.',
    'reset_expired'       => 'This link has expired. Please request a new one.',

    // Email verification
    'verify_title'          => 'Check your inbox',
    'verify_subtitle'       => "We sent a confirmation link to {email}",
    'verify_success'        => "Email confirmed! You're now signed in.",
    'verify_link_invalid'   => 'Link is invalid or expired.',
    'verify_resend'         => 'Resend confirmation email',
    'verify_resent'         => 'A new confirmation email has been sent.',
    'verify_email_label'    => 'Your email address',

    // OAuth
    'or_continue_with'    => 'Or continue with',
    'oauth_error'         => 'Sign-in failed. Please try again.',

    // Email — verify
    'email_verify_subject'   => 'Verify your email for {app_name}',
    'email_verify_preheader' => 'Confirm your address — link expires in {expires}',
    'email_verify_body'      => 'Click the button below to verify your email address.',
    'email_verify_button'    => 'Verify Email Address',
    'email_verify_expires'   => 'This link expires in {expires}.',
    'email_verify_ignore'    => "If you didn't create an account, you can safely ignore this email.",

    // Email — reset
    'email_reset_subject'    => 'Reset your {app_name} password',
    'email_reset_preheader'  => 'Password reset requested — link expires in {expires}',
    'email_reset_body'       => 'We received a request to reset the password for your account.',
    'email_reset_button'     => 'Reset Password',
    'email_reset_expires'    => 'This link expires in {expires}.',
    'email_reset_ignore'     => "If you didn't request a password reset, no action is needed.",

    // Settings labels (used by Settings admin UI)
    'smtp_host'               => 'SMTP Host',
    'smtp_host_hint'          => 'e.g. smtp.mailgun.org or smtp.gmail.com',
    'smtp_port'               => 'SMTP Port',
    'smtp_port_hint'          => '587 for TLS (recommended), 465 for SSL, 25 for plain',
    'smtp_user'               => 'SMTP Username',
    'smtp_pass'               => 'SMTP Password',
    'smtp_crypto'             => 'Encryption',
    'smtp_crypto_hint'        => 'tls — port 587 (recommended); ssl — port 465; none — no encryption',
    'from_email'              => 'From Email',
    'from_email_hint'         => 'Use a subdomain address: hello@mail.yourdomain.com',
    'from_name'               => 'From Name',
    'normalize_email'             => 'Normalize Email Addresses',
    'normalize_email_hint'        => 'Strip +tags from all addresses on register and login (user+spam@gmail.com → user@gmail.com). Also removes dots from Gmail addresses (te.st@gmail.com → test@gmail.com). Prevents duplicate accounts via address tricks.',
    'block_disposable_emails'     => 'Block Disposable Emails',
    'block_disposable_emails_hint'=> 'Reject registrations from known temporary / disposable email providers (mailinator, guerrillamail, etc.). The current domain list is in oneshot/Auth/Config/EmailDomains.php. Update it with: php spark auth:update-disposable',
    'blocked_email_domains'       => 'Custom Blocked Domains',
    'blocked_email_domains_hint'  => 'Additional domains to block, separated by any non-domain characters (spaces, commas, newlines). Example: tempmail.com guerrillamail.org',
    'deleted_email_policy'        => 'Re-registration of Deleted Emails',
    'deleted_email_policy_hint'   => 'What to do when someone registers with an email that belonged to a previously deleted account. allow — register as a new user silently; flag — register but write a warning to the auth log; block — reject with "email already in use".',
    'email_verification'      => 'Email Verification',
    'email_verification_hint' => 'required (recommended) — block login until verified; optional — send email but allow login; disabled — skip',
    'password_min_length'                    => 'Min Password Length',
    'password_min_length_hint'               => 'Minimum number of characters required for passwords',
    'password_require_uppercase'             => 'Require Mixed Case',
    'password_require_uppercase_hint'        => 'Password must contain both uppercase (A–Z) and lowercase (a–z) letters',
    'password_require_numbers'               => 'Require Numbers',
    'password_require_numbers_hint'          => 'Password must contain at least one digit (0–9)',
    'password_require_symbols'               => 'Require Special Characters',
    'password_require_symbols_hint'          => 'Password must contain at least one non-alphanumeric character (e.g. !@#$%^&*)',

    // Password field actions
    'pw_show'     => 'Show',
    'pw_hide'     => 'Hide',
    'pw_copy'     => 'Copy',
    'pw_generate' => 'Generate',

    // Password rules (shown inline under the password field)
    'pw_rule_min'      => 'at least %d chars',
    'pw_rule_uppercase'=> 'upper & lowercase',
    'pw_rule_numbers'  => 'a number',
    'pw_rule_symbols'  => 'a special character',

    // Password validation error messages
    'password_require_uppercase_error'       => 'Password must contain both uppercase and lowercase letters.',
    'password_require_numbers_error'         => 'Password must contain at least one number.',
    'password_require_symbols_error'         => 'Password must contain at least one special character.',

    // OAuth — callback URL labels
    'oauth_google_callback'    => 'Google Callback URL',
    'oauth_facebook_callback'  => 'Facebook Callback URL',
    'oauth_github_callback'    => 'GitHub Callback URL',
    'oauth_apple_callback'     => 'Apple Callback URL',
    'oauth_linkedin_callback'  => 'LinkedIn Callback URL',
    'oauth_microsoft_callback' => 'Microsoft Callback URL',
    'oauth_telegram_callback'  => 'Telegram Callback URL',

    // OAuth — Google
    'oauth_google_enabled'         => 'Enable Google Login',
    'oauth_google_id'              => 'Client ID',
    'oauth_google_secret'          => 'Client Secret',
    'oauth_google_enabled_hint'    => 'Add the Callback URL above in Google Console → APIs & Services → Credentials → your OAuth client → Authorised redirect URIs. Also add {base_url} to Authorised JavaScript origins.',
    'oauth_google_id_hint'         => 'console.cloud.google.com → APIs & Services → Credentials → OAuth 2.0 Client IDs',
    'oauth_google_secret_hint'     => 'Same location as Client ID — keep this secret',

    // OAuth — Facebook
    'oauth_facebook_enabled'       => 'Enable Facebook Login',
    'oauth_facebook_id'            => 'App ID',
    'oauth_facebook_secret'        => 'App Secret',
    'oauth_facebook_enabled_hint'  => 'Add the Callback URL above in developers.facebook.com → your app → Facebook Login → Settings → Valid OAuth Redirect URIs',
    'oauth_facebook_id_hint'       => 'developers.facebook.com → My Apps → your app → Settings → Basic → App ID',
    'oauth_facebook_secret_hint'   => 'Same location as App ID — click Show to reveal',

    // OAuth — GitHub
    'oauth_github_enabled'         => 'Enable GitHub Login',
    'oauth_github_id'              => 'Client ID',
    'oauth_github_secret'          => 'Client Secret',
    'oauth_github_enabled_hint'    => 'Add the Callback URL above in github.com → Settings → Developer settings → OAuth Apps → your app → Authorization callback URL',
    'oauth_github_id_hint'         => 'github.com → Settings → Developer settings → OAuth Apps → your app → Client ID',
    'oauth_github_secret_hint'     => 'Same location → Generate a client secret',

    // OAuth — Apple
    'oauth_apple_enabled'          => 'Enable Apple Login',
    'oauth_apple_id'               => 'Services ID',
    'oauth_apple_team_id'          => 'Team ID',
    'oauth_apple_key_id'           => 'Key ID',
    'oauth_apple_secret'           => 'Private Key (.p8)',
    'oauth_apple_enabled_hint'     => 'Add the Callback URL above in developer.apple.com → Certificates, IDs & Profiles → your Services ID → Sign In with Apple → Return URLs',
    'oauth_apple_id_hint'          => 'developer.apple.com → Identifiers → Services IDs (not the App ID — create a separate Services ID)',
    'oauth_apple_team_id_hint'     => 'developer.apple.com → top-right corner next to your name (10-character string)',
    'oauth_apple_key_id_hint'      => 'developer.apple.com → Keys → your Sign in with Apple key → Key ID (10-character string)',
    'oauth_apple_secret_hint'      => 'Paste the full contents of the .p8 file downloaded from developer.apple.com → Keys (including -----BEGIN/END PRIVATE KEY----- lines)',

    // OAuth — LinkedIn
    'oauth_linkedin_enabled'       => 'Enable LinkedIn Login',
    'oauth_linkedin_id'            => 'Client ID',
    'oauth_linkedin_secret'        => 'Client Secret',
    'oauth_linkedin_enabled_hint'  => 'Add the Callback URL above in linkedin.com/developers → your app → Auth → Authorized redirect URLs',
    'oauth_linkedin_id_hint'       => 'linkedin.com/developers → My Apps → your app → Auth → Client ID',
    'oauth_linkedin_secret_hint'   => 'Same location as Client ID → Client Secret',

    // OAuth — Microsoft
    'oauth_microsoft_enabled'      => 'Enable Microsoft Login',
    'oauth_microsoft_id'           => 'Application ID',
    'oauth_microsoft_secret'       => 'Client Secret',
    'oauth_microsoft_enabled_hint' => 'Add the Callback URL above in portal.azure.com → your app → Authentication → Redirect URIs',
    'oauth_microsoft_id_hint'      => 'portal.azure.com → Azure Active Directory → App registrations → your app → Overview → Application (client) ID',
    'oauth_microsoft_secret_hint'  => 'portal.azure.com → your app → Certificates & secrets → New client secret → Value',

    // OAuth — Telegram
    'oauth_telegram_enabled'       => 'Enable Telegram Login',
    'oauth_telegram_bot_token'     => 'Bot Token',
    'oauth_telegram_bot_name'      => 'Bot Username',
    'oauth_telegram_enabled_hint'  => 'Set the Callback URL above as data-auth-url in the Login Widget. Then @BotFather → /setdomain → enter: {host}',
    'oauth_telegram_bot_token_hint'=> 'Telegram → @BotFather → /mybots → your bot → API Token',
    'oauth_telegram_bot_name_hint' => 'Bot username without @, e.g. "MyAppBot" — shown on the Login Widget button',
];

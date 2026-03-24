<?php

namespace OneShot\Auth\Services;

/**
 * Auth email service.
 * Reads SMTP config from Settings (option()) with fallback to .env.
 * Uses CI4 built-in email service. HTML emails, UTF-8.
 * All text from language file with {mask} substitution.
 */
class MailService
{
    private \CodeIgniter\Email\Email $mailer;

    public function __construct()
    {
        $this->mailer = service('email');
        $this->configure();
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    public function sendVerification(object $user, string $rawToken): bool
    {
        $appName = option('general.app_name', env('app.name', 'OneShot'));
        $link    = site_url('auth/verify/' . $rawToken);
        $expires = '24 hours';

        $subject = $this->mask(__('auth.email_verify_subject', 'Verify your email for {app_name}'), [
            '{app_name}' => $appName,
        ]);

        $body = $this->renderEmail('verify', [
            '{name}'     => esc($user->name ?? 'there'),
            '{link}'     => $link,
            '{app_name}' => $appName,
            '{expires}'  => $expires,
        ]);

        return $this->send($user->email, $subject, $body);
    }

    public function sendPasswordReset(object $user, string $rawToken): bool
    {
        $appName = option('general.app_name', env('app.name', 'OneShot'));
        $link    = site_url('auth/reset/' . $rawToken);
        $expires = '1 hour';

        $subject = $this->mask(__('auth.email_reset_subject', 'Reset your {app_name} password'), [
            '{app_name}' => $appName,
        ]);

        $body = $this->renderEmail('reset', [
            '{name}'     => esc($user->name ?? 'there'),
            '{link}'     => $link,
            '{app_name}' => $appName,
            '{expires}'  => $expires,
        ]);

        return $this->send($user->email, $subject, $body);
    }

    public function send(string $to, string $subject, string $body): bool
    {
        try {
            $fromEmail = option('mail.from_email', env('MAIL_FROM_EMAIL', ''));
            $fromName  = option('mail.from_name',  env('MAIL_FROM_NAME',  ''));

            $this->mailer->clear();
            $this->mailer->setTo($to);
            $this->mailer->setFrom($fromEmail, $fromName);
            $this->mailer->setSubject($subject);
            $this->mailer->setMessage($body);

            return $this->mailer->send(false);
        } catch (\Throwable $e) {
            l(['event' => 'mail_error', 'to' => $to, 'error' => $e->getMessage()], 'auth_mail');

            return false;
        }
    }

    // ------------------------------------------------------------------
    // Private
    // ------------------------------------------------------------------

    private function configure(): void
    {
        $host   = option('mail.smtp_host',   env('MAIL_SMTP_HOST',   'localhost'));
        $port   = (int) option('mail.smtp_port',   env('MAIL_SMTP_PORT',   '587'));
        $user   = option('mail.smtp_user',   env('MAIL_SMTP_USER',   ''));
        $pass   = option('mail.smtp_pass',   env('MAIL_SMTP_PASS',   ''));
        $crypto = option('mail.smtp_crypto', env('MAIL_SMTP_CRYPTO', 'tls'));

        $this->mailer->initialize([
            'protocol'  => 'smtp',
            'SMTPHost'  => $host,
            'SMTPPort'  => $port,
            'SMTPUser'  => $user,
            'SMTPPass'  => $pass,
            'SMTPCrypto'=> $crypto !== 'none' ? $crypto : '',
            'mailType'  => 'html',
            'charset'   => 'UTF-8',
            'wordWrap'  => true,
        ]);
    }

    /**
     * Render HTML email view with mask substitution.
     */
    private function renderEmail(string $template, array $masks): string
    {
        $viewPath = APPPATH . '../oneshot/Auth/Views/emails/' . $template . '.php';

        if (! file_exists($viewPath)) {
            return implode('<br>', array_values($masks));
        }

        ob_start();
        extract($masks, EXTR_PREFIX_ALL, 'v');
        include $viewPath;
        $html = ob_get_clean();

        return str_replace(array_keys($masks), array_values($masks), $html);
    }

    /**
     * Substitute {mask} placeholders in a string.
     */
    private function mask(string $text, array $masks): string
    {
        return str_replace(array_keys($masks), array_values($masks), $text);
    }
}

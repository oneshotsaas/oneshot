<?php

return [
    'smtp_host'       => 'SMTP Host',
    'smtp_host_hint'  => 'Popular providers: Resend — smtp.resend.com · Mailgun — smtp.mailgun.org · SendGrid — smtp.sendgrid.net · Postmark — smtp.postmarkapp.com · Brevo — smtp-relay.brevo.com · AWS SES — email-smtp.us-east-1.amazonaws.com · Gmail — smtp.gmail.com (personal use only)',
    'smtp_port'       => 'SMTP Port',
    'smtp_port_hint'  => '587 for TLS (recommended) · 465 for SSL · 25 for plain (blocked by most hosts)',
    'smtp_user'       => 'SMTP Username',
    'smtp_user_hint'  => 'Usually your email or API key. Resend: "resend" · SendGrid: "apikey" · Mailgun: "postmaster@yourdomain"',
    'smtp_pass'       => 'SMTP Password',
    'smtp_pass_hint'  => 'Your SMTP password or API key. For Resend, SendGrid, Mailgun — use the API key, not your account password.',
    'smtp_crypto'     => 'Encryption',
    'smtp_crypto_hint'=> 'TLS on port 587 (recommended) · SSL on port 465 · None — for local dev only',
    'from_email'      => 'From Email',
    'from_email_hint' => 'Must be a verified sender address. Use a subdomain for deliverability: noreply@mail.yourdomain.com',
    'from_name'       => 'From Name',
    'from_name_hint'  => 'Displayed as the sender name in email clients, e.g. "MyApp" or "MyApp Support"',
];

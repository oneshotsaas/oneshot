<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{app_name} — Verify your email</title>
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { width: 100%; background-color: #f4f4f5; padding: 32px 16px; }
        .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .header { background-color: #18181b; padding: 24px 32px; text-align: center; }
        .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: -0.3px; }
        .body { padding: 32px; }
        .greeting { font-size: 16px; color: #18181b; margin: 0 0 16px; }
        .text { font-size: 15px; color: #52525b; line-height: 1.6; margin: 0 0 24px; }
        .btn-wrap { text-align: center; margin: 0 0 24px; }
        .btn { display: inline-block; background-color: #6366f1; color: #ffffff !important; text-decoration: none;
               padding: 14px 32px; border-radius: 8px; font-size: 15px; font-weight: 600; min-width: 200px;
               mso-padding-alt: 14px 32px; }
        .link-fallback { font-size: 13px; color: #a1a1aa; text-align: center; margin: 0 0 24px; word-break: break-all; }
        .link-fallback a { color: #6366f1; }
        .expires { font-size: 13px; color: #a1a1aa; text-align: center; margin: 0 0 24px; }
        .divider { border: none; border-top: 1px solid #e4e4e7; margin: 0 0 24px; }
        .ignore { font-size: 13px; color: #a1a1aa; text-align: center; margin: 0; }
        .footer { background-color: #f4f4f5; padding: 16px 32px; text-align: center; }
        .footer p { font-size: 12px; color: #a1a1aa; margin: 0; }
        @media (max-width: 600px) {
            .body { padding: 24px 20px; }
            .btn { display: block; text-align: center; }
        }
    </style>
</head>
<body>
<!-- Pre-header (hidden in email body, shown in preview) -->
<span style="display:none;font-size:1px;color:#f4f4f5;max-height:0;max-width:0;opacity:0;overflow:hidden;">
    Confirm your address — link expires in {expires}
</span>

<div class="wrapper">
    <div class="container">
        <div class="header">
            <h1>{app_name}</h1>
        </div>
        <div class="body">
            <p class="greeting">Hi {name},</p>
            <p class="text"><?= __('auth.email_verify_body', 'Click the button below to verify your email address.') ?></p>

            <div class="btn-wrap">
                <a href="{link}" class="btn"><?= __('auth.email_verify_button', 'Verify Email Address') ?></a>
            </div>

            <p class="link-fallback">
                Or copy this link:<br>
                <a href="{link}">{link}</a>
            </p>

            <p class="expires"><?= __('auth.email_verify_expires', 'This link expires in {expires}.') ?></p>

            <hr class="divider">

            <p class="ignore"><?= __('auth.email_verify_ignore', "If you didn't create an account, you can safely ignore this email.") ?></p>
        </div>
        <div class="footer">
            <p>{app_name} · Sent to {name}</p>
        </div>
    </div>
</div>
</body>
</html>

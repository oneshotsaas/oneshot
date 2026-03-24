<?php

namespace OneShot\Auth\Config;

/**
 * OAuth 2.0 provider configurations.
 * All HTTP calls go through CI4 service('curlrequest') — no external libraries.
 */
class OAuth
{
    /**
     * Returns config for all supported providers.
     * Credentials are read at runtime via option() / env() in OAuthService.
     */
    public static function providers(): array
    {
        return [
            'google' => [
                'auth_url'     => 'https://accounts.google.com/o/oauth2/v2/auth',
                'token_url'    => 'https://oauth2.googleapis.com/token',
                'userinfo_url' => 'https://www.googleapis.com/oauth2/v3/userinfo',
                'scope'        => 'openid email profile',
            ],
            'facebook' => [
                'auth_url'     => 'https://www.facebook.com/v19.0/dialog/oauth',
                'token_url'    => 'https://graph.facebook.com/v19.0/oauth/access_token',
                'userinfo_url' => 'https://graph.facebook.com/me?fields=id,name,email,email_verified',
                'scope'        => 'email',
            ],
            'github' => [
                'auth_url'     => 'https://github.com/login/oauth/authorize',
                'token_url'    => 'https://github.com/login/oauth/access_token',
                'userinfo_url' => 'https://api.github.com/user',
                'emails_url'   => 'https://api.github.com/user/emails', // primary+verified email
                'scope'        => 'read:user user:email',
            ],
            'apple' => [
                'auth_url'     => 'https://appleid.apple.com/auth/authorize',
                'token_url'    => 'https://appleid.apple.com/auth/token',
                'scope'        => 'name email',
                // Apple returns user info in id_token (JWT), not userinfo endpoint
                // client_secret must be a signed JWT — generated dynamically in OAuthService
            ],
            'linkedin' => [
                'auth_url'     => 'https://www.linkedin.com/oauth/v2/authorization',
                'token_url'    => 'https://www.linkedin.com/oauth/v2/accessToken',
                'userinfo_url' => 'https://api.linkedin.com/v2/userinfo', // OpenID Connect
                'scope'        => 'openid profile email',
            ],
            'microsoft' => [
                'auth_url'     => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
                'token_url'    => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
                'userinfo_url' => 'https://graph.microsoft.com/v1.0/me',
                'scope'        => 'openid profile email User.Read',
            ],
        ];
    }
}

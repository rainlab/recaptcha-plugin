<?php namespace RainLab\Recaptcha\Classes;

use RainLab\Recaptcha\Models\Settings;

/**
 * Recaptcha is a convenience helper for verifying response tokens and
 * querying the captcha state from components and theme templates.
 *
 * @package rainlab\recaptcha
 * @author Alexey Bobkov, Samuel Georges
 */
class Recaptcha
{
    /**
     * verify checks a response token against the Google verification API
     */
    public function verify(?string $token, ?string $action = null): bool
    {
        return (new Verifier)->verify($token, $action);
    }

    /**
     * isEnabled determines if verification is active for this environment
     */
    public static function isEnabled(): bool
    {
        return (new Verifier)->isEnabled();
    }

    /**
     * isConfigured determines if an API key pair has been supplied
     */
    public static function isConfigured(): bool
    {
        return (new Verifier)->isConfigured();
    }

    /**
     * siteKey returns the configured site key
     */
    public static function siteKey(): ?string
    {
        return Settings::get('site_key');
    }
}

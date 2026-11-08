<?php namespace RainLab\Recaptcha\Components;

use Session;
use Cms\Classes\ComponentBase;
use RainLab\Recaptcha\Classes\Verifier;
use RainLab\Recaptcha\Models\Settings;

/**
 * Recaptcha component renders a reCAPTCHA widget inside a form, holding
 * the form's own submit button back until the visitor has a token.
 *
 * @package rainlab\recaptcha
 * @author Alexey Bobkov, Samuel Georges
 */
class Recaptcha extends ComponentBase
{
    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => 'reCAPTCHA',
            'description' => 'Renders a reCAPTCHA widget inside a form'
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'type' => [
                'title' => 'Type',
                'description' => 'Visible and invisible types require a reCAPTCHA v2 key pair, v3 requires a reCAPTCHA v3 key pair.',
                'type' => 'dropdown',
                'options' => [
                    'visible' => 'Visible (v2)',
                    'invisible' => 'Invisible (v2)',
                    'v3' => 'Score-based (v3)',
                ],
                'default' => 'v3',
                'showExternalParam' => false,
                'required' => true,
            ],
            'load_script' => [
                'title' => 'Load external script',
                'description' => 'Include the reCAPTCHA JavaScript from Google. Disable this when the script is already included by the page.',
                'type' => 'checkbox',
                'default' => true,
                'showExternalParam' => false,
            ],
            'action_name' => [
                'title' => 'Action name',
                'description' => 'The action name reported to reCAPTCHA v3 for this form.',
                'type' => 'string',
                'default' => 'submitForm',
                'showExternalParam' => false,
            ],
        ];
    }

    /**
     * isEnabled determines if the captcha should render and verify
     */
    public function isEnabled(): bool
    {
        return (new Verifier)->isEnabled();
    }

    /**
     * siteKey returns the configured site key
     */
    public function siteKey(): ?string
    {
        return Settings::get('site_key');
    }

    /**
     * language returns the widget language, using the backend setting
     * first, the active translation locale second, otherwise detecting
     * the language automatically.
     */
    public function language(): string
    {
        if ($lang = Settings::get('lang')) {
            return $lang;
        }

        if ($locale = Session::get('rainlab.translate.locale')) {
            return $locale;
        }

        return '';
    }
}

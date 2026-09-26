<?php namespace RainLab\Recaptcha;

use Validator;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use RainLab\Recaptcha\Validators\RecaptchaValidator;

/**
 * Recaptcha Plugin Information File
 *
 * @package rainlab\recaptcha
 * @author Alexey Bobkov, Samuel Georges
 */
class Plugin extends PluginBase
{
    /**
     * pluginDetails about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'reCAPTCHA',
            'description' => 'Protects website forms using the Google reCAPTCHA service.',
            'author' => 'October CMS',
            'icon' => 'icon-shield',
            'homepage' => 'https://github.com/rainlab/recaptcha-plugin'
        ];
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        Validator::extend(
            'recaptcha',
            RecaptchaValidator::class . '@validate',
            __('The reCAPTCHA verification failed. Please try again.')
        );
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return [
            \RainLab\Recaptcha\Components\Recaptcha::class => 'recaptcha'
        ];
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
        return [
            'recaptcha' => [
                'label' => 'reCAPTCHA',
                'description' => 'Manage Google reCAPTCHA configuration.',
                'category' => SettingsManager::CATEGORY_SYSTEM,
                'icon' => 'icon-shield',
                'class' => \RainLab\Recaptcha\Models\Settings::class,
                'order' => 500,
                'permissions' => ['rainlab.recaptcha.access_settings']
            ]
        ];
    }

    /**
     * registerPermissions used by the backend.
     */
    public function registerPermissions()
    {
        return [
            'rainlab.recaptcha.access_settings' => [
                'tab' => 'reCAPTCHA',
                'label' => 'Manage reCAPTCHA settings'
            ]
        ];
    }
}

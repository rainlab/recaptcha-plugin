<?php namespace RainLab\Recaptcha\Models;

use System\Models\SettingModel;

/**
 * Settings for the Google reCAPTCHA service
 *
 * @package rainlab\recaptcha
 * @author Alexey Bobkov, Samuel Georges
 */
class Settings extends SettingModel
{
    /**
     * @var string settingsCode
     */
    public $settingsCode = 'rainlab_recaptcha_settings';

    /**
     * @var mixed settingsFields definitions
     */
    public $settingsFields = 'fields.yaml';

    /**
     * initSettingsData for this model. This only executes when the
     * model is first created or reset to default.
     */
    public function initSettingsData()
    {
        $this->enabled = true;
        $this->min_score = 0.5;
    }

    /**
     * getLangOptions returns languages supported by the reCAPTCHA widget
     */
    public function getLangOptions(): array
    {
        return [
            '' => 'Auto-detect',
            'ar' => 'Arabic',
            'bn' => 'Bengali',
            'bg' => 'Bulgarian',
            'ca' => 'Catalan',
            'zh-CN' => 'Chinese (Simplified)',
            'zh-TW' => 'Chinese (Traditional)',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'en-GB' => 'English (UK)',
            'en' => 'English (US)',
            'et' => 'Estonian',
            'fil' => 'Filipino',
            'fi' => 'Finnish',
            'fr' => 'French',
            'fr-CA' => 'French (Canadian)',
            'de' => 'German',
            'de-AT' => 'German (Austria)',
            'de-CH' => 'German (Switzerland)',
            'el' => 'Greek',
            'gu' => 'Gujarati',
            'iw' => 'Hebrew',
            'hi' => 'Hindi',
            'hu' => 'Hungarian',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'kn' => 'Kannada',
            'ko' => 'Korean',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'ms' => 'Malay',
            'ml' => 'Malayalam',
            'mr' => 'Marathi',
            'no' => 'Norwegian',
            'fa' => 'Persian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'pt-BR' => 'Portuguese (Brazil)',
            'pt-PT' => 'Portuguese (Portugal)',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sr' => 'Serbian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'es' => 'Spanish',
            'es-419' => 'Spanish (Latin America)',
            'sv' => 'Swedish',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'vi' => 'Vietnamese',
        ];
    }
}

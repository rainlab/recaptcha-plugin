<?php namespace RainLab\Recaptcha\Tests;

use Config;
use PluginTestCase;
use RainLab\Recaptcha\Models\Settings;
use RainLab\Recaptcha\Validators\RecaptchaValidator;

class RecaptchaValidatorTest extends PluginTestCase
{
    /**
     * setUp test case
     */
    public function setUp(): void
    {
        parent::setUp();

        (new Settings)->resetDefault();
        Settings::clearInternalCache();
        Config::set('rainlab.recaptcha::enabled', null);
    }

    public function testPassesWhenDisabled()
    {
        Config::set('rainlab.recaptcha::enabled', false);

        $rule = new RecaptchaValidator;

        $this->assertTrue($rule->passes('g-recaptcha-response', null));
        $this->assertTrue($rule->passes('g-recaptcha-response', 'anything'));
    }

    public function testFailsWithBlankToken()
    {
        Settings::set('secret_key', 'test_secret');

        $rule = new RecaptchaValidator;

        $this->assertFalse($rule->passes('g-recaptcha-response', ''));
        $this->assertStringContainsString('missing-input-response', $rule->message());
    }

    public function testValidateMirrorsPasses()
    {
        Settings::set('secret_key', 'test_secret');

        $rule = new RecaptchaValidator;

        $this->assertFalse($rule->validate('g-recaptcha-response', '', []));

        Config::set('rainlab.recaptcha::enabled', false);

        $this->assertTrue($rule->validate('g-recaptcha-response', '', []));
    }

    public function testMessageIncludesBaseText()
    {
        Settings::set('secret_key', 'test_secret');

        $rule = new RecaptchaValidator;
        $rule->passes('g-recaptcha-response', '');

        $this->assertNotEmpty($rule->message());
        $this->assertStringContainsString('reCAPTCHA', $rule->message());
    }
}

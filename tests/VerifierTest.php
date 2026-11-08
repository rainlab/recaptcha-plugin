<?php namespace RainLab\Recaptcha\Tests;

use Config;
use PluginTestCase;
use ReCaptcha\RequestMethod;
use ReCaptcha\RequestParameters;
use RainLab\Recaptcha\Classes\Verifier;
use RainLab\Recaptcha\Models\Settings;

class VerifierTest extends PluginTestCase
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

    public function testVerifyPassesWhenDisabledByConfig()
    {
        Config::set('rainlab.recaptcha::enabled', false);

        $verifier = new Verifier;

        $this->assertTrue($verifier->isEnabled() === false);
        $this->assertTrue($verifier->verify(null));
        $this->assertTrue($verifier->verify('anything'));
    }

    public function testVerifyPassesWhenDisabledBySettings()
    {
        Settings::set('enabled', false);

        $this->assertTrue((new Verifier)->verify(null));
    }

    public function testConfigOverridesSettings()
    {
        Settings::set('enabled', false);
        Config::set('rainlab.recaptcha::enabled', true);

        $this->assertTrue((new Verifier)->isEnabled());
    }

    public function testVerifyFailsWithBlankToken()
    {
        Settings::set('secret_key', 'test_secret');

        $verifier = new Verifier;

        $this->assertFalse($verifier->verify(null));
        $this->assertEquals(['missing-input-response'], $verifier->getErrorCodes());

        $this->assertFalse($verifier->verify(''));
        $this->assertEquals(['missing-input-response'], $verifier->getErrorCodes());
    }

    public function testVerifyFailsWithoutSecretKey()
    {
        $verifier = new Verifier;

        $this->assertFalse($verifier->verify('some-token'));
        $this->assertEquals(['missing-input-secret'], $verifier->getErrorCodes());
    }

    public function testVerifySucceedsWithValidResponse()
    {
        Settings::set('secret_key', 'test_secret');

        $verifier = new Verifier($this->makeTransport('{"success": true}'));

        $this->assertTrue($verifier->verify('valid-token'));
        $this->assertEmpty($verifier->getErrorCodes());
    }

    public function testVerifyFailsWithInvalidResponse()
    {
        Settings::set('secret_key', 'test_secret');

        $verifier = new Verifier($this->makeTransport(
            '{"success": false, "error-codes": ["invalid-input-response"]}'
        ));

        $this->assertFalse($verifier->verify('spoofed-token'));
        $this->assertContains('invalid-input-response', $verifier->getErrorCodes());
    }

    public function testVerifyChecksScoreThreshold()
    {
        Settings::set([
            'secret_key' => 'test_secret',
            'min_score' => 0.5
        ]);

        $verifier = new Verifier($this->makeTransport(
            '{"success": true, "score": 0.9, "action": "submitForm"}'
        ));

        $this->assertTrue($verifier->verify('good-token', 'submitForm'));

        $verifier = new Verifier($this->makeTransport(
            '{"success": true, "score": 0.1, "action": "submitForm"}'
        ));

        $this->assertFalse($verifier->verify('suspicious-token', 'submitForm'));
        $this->assertContains('score-threshold-not-met', $verifier->getErrorCodes());
    }

    public function testVerifyChecksScoreWithoutAction()
    {
        Settings::set([
            'secret_key' => 'test_secret',
            'min_score' => 0.5
        ]);

        // The validator rule passes no action, so v3 tokens must still meet the score
        $verifier = new Verifier($this->makeTransport(
            '{"success": true, "score": 0.1, "action": "submitForm"}'
        ));

        $this->assertFalse($verifier->verify('suspicious-token'));
        $this->assertEquals(['score-threshold-not-met'], $verifier->getErrorCodes());

        $verifier = new Verifier($this->makeTransport(
            '{"success": true, "score": 0.9, "action": "submitForm"}'
        ));

        $this->assertTrue($verifier->verify('good-token'));
    }

    public function testVerifyAcceptsV2ResponseWithoutScore()
    {
        Settings::set([
            'secret_key' => 'test_secret',
            'min_score' => 0.5
        ]);

        // v2 responses carry no score, so the minimum score must not reject them
        $verifier = new Verifier($this->makeTransport('{"success": true}'));

        $this->assertTrue($verifier->verify('v2-token'));
        $this->assertEmpty($verifier->getErrorCodes());
    }

    public function testVerifyChecksExpectedAction()
    {
        Settings::set('secret_key', 'test_secret');

        $verifier = new Verifier($this->makeTransport(
            '{"success": true, "score": 0.9, "action": "otherForm"}'
        ));

        $this->assertFalse($verifier->verify('mismatched-token', 'submitForm'));
        $this->assertContains('action-mismatch', $verifier->getErrorCodes());
    }

    public function testVerifyFailsOnTransportError()
    {
        Settings::set('secret_key', 'test_secret');

        $verifier = new Verifier($this->makeThrowingTransport());

        $this->assertFalse($verifier->verify('any-token'));
        $this->assertEquals(['transport-error'], $verifier->getErrorCodes());
    }

    public function testIsConfigured()
    {
        $this->assertFalse((new Verifier)->isConfigured());

        Settings::set([
            'site_key' => 'test_site',
            'secret_key' => 'test_secret'
        ]);

        $this->assertTrue((new Verifier)->isConfigured());
    }

    /**
     * makeTransport builds a fake transport returning a canned response
     */
    protected function makeTransport(string $response): RequestMethod
    {
        return new class($response) implements RequestMethod {
            public function __construct(private string $response) {}

            public function submit(RequestParameters $params)
            {
                return $this->response;
            }
        };
    }

    /**
     * makeThrowingTransport builds a fake transport that throws
     */
    protected function makeThrowingTransport(): RequestMethod
    {
        return new class implements RequestMethod {
            public function submit(RequestParameters $params)
            {
                throw new \RuntimeException('Connection refused');
            }
        };
    }
}

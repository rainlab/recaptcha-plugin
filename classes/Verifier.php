<?php namespace RainLab\Recaptcha\Classes;

use Log;
use Config;
use Request;
use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod;
use RainLab\Recaptcha\Models\Settings;
use Exception;

/**
 * Verifier performs server-side verification of reCAPTCHA response tokens
 * using the Google verification API. Verification is skipped entirely when
 * the service is disabled, either with the backend setting or by setting
 * the RECAPTCHA_ENABLED environment variable to false.
 *
 * @package rainlab\recaptcha
 * @author Alexey Bobkov, Samuel Georges
 */
class Verifier
{
    /**
     * @var array errorCodes from the last verification attempt
     */
    protected array $errorCodes = [];

    /**
     * __construct with an optional transport override, used by unit tests
     */
    public function __construct(protected ?RequestMethod $requestMethod = null)
    {
    }

    /**
     * verify checks a reCAPTCHA response token, returning true when the
     * service is disabled. A score-based (v3) token must meet the minimum
     * score, and the expected action when an action name is supplied.
     */
    public function verify(?string $token, ?string $action = null, ?string $remoteIp = null): bool
    {
        $this->errorCodes = [];

        if (!$this->isEnabled()) {
            return true;
        }

        if (!strlen((string) $token)) {
            $this->errorCodes = ['missing-input-response'];
            return false;
        }

        if (!strlen((string) Settings::get('secret_key'))) {
            $this->errorCodes = ['missing-input-secret'];
            return false;
        }

        try {
            $client = $this->makeClient();

            if ($action !== null) {
                $client->setExpectedAction($action);
            }

            $response = $client->verify($token, $remoteIp ?? Request::ip());

            $this->errorCodes = $response->getErrorCodes();

            if (!$response->isSuccess()) {
                return false;
            }

            // Only v3 responses carry a score. The client's own threshold would
            // reject v2 responses, which have none, so the score is checked here
            $score = $response->getScore();
            if ($score !== null && $score < (float) Settings::get('min_score', 0.5)) {
                $this->errorCodes = [ReCaptcha::E_SCORE_THRESHOLD_NOT_MET];
                return false;
            }

            return true;
        }
        catch (Exception $ex) {
            Log::error($ex);

            $this->errorCodes = ['transport-error'];

            return false;
        }
    }

    /**
     * getErrorCodes returns error codes from the last verification attempt
     */
    public function getErrorCodes(): array
    {
        return $this->errorCodes;
    }

    /**
     * isEnabled determines if verification is active for this environment
     */
    public function isEnabled(): bool
    {
        $configEnabled = Config::get('rainlab.recaptcha::enabled');

        if ($configEnabled !== null) {
            return (bool) $configEnabled;
        }

        return (bool) Settings::get('enabled', true);
    }

    /**
     * isConfigured determines if an API key pair has been supplied
     */
    public function isConfigured(): bool
    {
        return strlen((string) Settings::get('site_key')) &&
            strlen((string) Settings::get('secret_key'));
    }

    /**
     * makeClient builds the underlying reCAPTCHA client
     */
    protected function makeClient(): ReCaptcha
    {
        return new ReCaptcha(Settings::get('secret_key'), $this->requestMethod);
    }
}

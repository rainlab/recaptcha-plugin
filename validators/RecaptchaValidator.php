<?php namespace RainLab\Recaptcha\Validators;

use Illuminate\Contracts\Validation\Rule;
use RainLab\Recaptcha\Classes\Verifier;

/**
 * RecaptchaValidator validates a reCAPTCHA response token as a form rule.
 * The rule passes without verification when the service is disabled.
 *
 *     $rules = ['g-recaptcha-response' => ['required', new RecaptchaValidator]];
 *
 * @package rainlab\recaptcha
 * @author Alexey Bobkov, Samuel Georges
 */
class RecaptchaValidator implements Rule
{
    /**
     * @var array errorCodes from the last verification attempt
     */
    protected array $errorCodes = [];

    /**
     * passes determines if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        $verifier = new Verifier;

        $result = $verifier->verify($value);

        $this->errorCodes = $verifier->getErrorCodes();

        return $result;
    }

    /**
     * validate is a callback method used when extending the validator
     * with a string rule name.
     */
    public function validate($attribute, $value, $params): bool
    {
        return $this->passes($attribute, $value);
    }

    /**
     * message returns the validation error message.
     */
    public function message(): string
    {
        $message = __('The reCAPTCHA verification failed. Please try again.');

        if ($this->errorCodes) {
            $message .= ' ['.implode(', ', $this->errorCodes).']';
        }

        return $message;
    }
}

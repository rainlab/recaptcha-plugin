# reCAPTCHA Plugin

Protects website forms using the [Google reCAPTCHA](https://www.google.com/recaptcha) service, for October CMS.

## Features

- Supports reCAPTCHA v2 checkbox, v2 invisible and v3 score-based keys
- A `recaptcha` component that protects any form, keeping the form's own submit button
- Server-side verification helper and validation rule
- Can be disabled per environment for local development

## Installation

To install with Composer, run from your project root.

```bash
composer require rainlab/recaptcha-plugin
```

## Configuration

To use reCAPTCHA, [sign up for an API key pair](https://www.google.com/recaptcha/admin) for your site. The key pair consists of a site key and a secret key, the secret key should be kept safe. Enter the key pair in the backend area via **Settings → reCAPTCHA**.

> **Important**: The key pair version must match the reCAPTCHA version used by the site. A v3 key pair cannot be used with v2 widgets, or vice versa.

## Component

Attach the `recaptcha` component to a page or layout and render it inside a form, next to the form's own submit button.

```ini
[recaptcha]
type = "v3"
action_name = "contactForm"
==
<form data-request="onSubmitContactForm" data-request-validate>
    <!-- Form fields -->

    {% component 'recaptcha' %}
    <div data-validate-for="g-recaptcha-response"></div>
    <button type="submit" class="btn btn-primary">Send Message</button>
</form>
```

The form submits as usual, by clicking the button or pressing Enter in a field. For the `v3` and `invisible` types, the component holds the submit back while Google checks the visitor, then sends the form with the token in the `g-recaptcha-response` field. The `visible` type renders the checkbox where the component is placed.

The component doesn't render an error message, so place an element with `data-validate-for="g-recaptcha-response"` where the verification error should show.

The following component properties are supported.

| Property | Description | Default |
| --- | --- | --- |
| `type` | widget type: `visible`, `invisible` or `v3` | `v3` |
| `load_script` | include the reCAPTCHA JavaScript from Google | `true` |
| `action_name` | action name reported to reCAPTCHA v3 | `submitForm` |

## Server-Side Verification

The submitted token must be verified server-side. There are several ways to verify, depending on how the form is processed.

### Validation Rule

Use the `RecaptchaValidator` rule to validate the `g-recaptcha-response` field posted with the form.

```php
use Validator;
use RainLab\Recaptcha\Validators\RecaptchaValidator;

$validator = Validator::make(post(), [
    'g-recaptcha-response' => ['required', new RecaptchaValidator]
]);
```

The rule is also registered under the `recaptcha` name.

```php
$validator = Validator::make(post(), [
    'g-recaptcha-response' => 'required|recaptcha'
]);
```

### Verification Helper

Use the `Recaptcha` helper class to verify a token directly, for example, inside an AJAX handler where the token is posted as a variable.

```php
use RainLab\Recaptcha\Classes\Recaptcha;

public function onSubmitForm()
{
    if (!(new Recaptcha)->verify(post('token'))) {
        throw new ApplicationException('The reCAPTCHA verification failed.');
    }
}
```

The helper also provides some static methods for querying the captcha state, useful inside CMS templates.

```php
Recaptcha::isEnabled();      // determines if verification is active
Recaptcha::isConfigured();   // determines if a key pair has been supplied
Recaptcha::siteKey();        // returns the configured site key
```

## Local Development

Set the following in the environment configuration to bypass rendering and verification entirely, this is useful for local development.

```ini
RECAPTCHA_ENABLED=false
```

When disabled, the component renders nothing and loads no external scripts, so the form submits as it would without it, and all server-side verification passes automatically.

Alternatively, Google provides a [test key pair](https://developers.google.com/recaptcha/docs/faq#id-like-to-run-automated-tests-with-recaptcha.-what-should-i-do) that renders a real widget and always passes verification.

```
Site key: 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
Secret key: 6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
```

Test with the real widget before going live, since disabling reCAPTCHA skips the widget and the checks.

## Content Security Policy

The widget loads from `www.recaptcha.net`, the address Google provides for places that can't reach `www.google.com`, such as mainland China. Verification runs on the server and still calls Google directly.

If the site sends a Content Security Policy, it must allow these sources. Without `connect-src`, the widget renders but never produces a token, and every submission fails verification.

```
script-src  https://www.recaptcha.net https://www.gstatic.com
frame-src   https://www.recaptcha.net
connect-src https://www.recaptcha.net
```

## License

This plugin is officially maintained by the October CMS team and is available under the [MIT license](LICENSE).

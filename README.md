# reCAPTCHA Plugin

Protects website forms using the [Google reCAPTCHA](https://www.google.com/recaptcha) service, for October CMS.

## Features

- Supports reCAPTCHA v2 checkbox, v2 invisible and v3 score-based keys
- Drop-in `recaptcha` component that renders the widget and submit button
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

Attach the `recaptcha` component to a page or layout and render it inside a form. The component renders the widget along with the submit button for the form.

```ini
[recaptcha]
type = "v3"
action_name = "contactForm"
button_text = "Send Message"
button_class = "btn btn-primary"
==
<form data-request="onSubmitContactForm">
    <!-- Form fields -->

    {% component 'recaptcha' %}
</form>
```

The following component properties are supported.

| Property | Description | Default |
| --- | --- | --- |
| `type` | widget type: `visible`, `invisible` or `v3` | `v3` |
| `load_script` | include the reCAPTCHA JavaScript from Google | `true` |
| `action_name` | action name reported to reCAPTCHA v3 | `submitForm` |
| `button_text` | text for the form submit button | `Submit` |
| `button_class` | CSS classes applied to the submit button | |

## Server-Side Verification

The submitted token must be verified server-side. There are several ways to verify, depending on how the form is processed.

### Validation Rule

Use the `RecaptchaValidator` rule to validate the `g-recaptcha-response` field posted by v2 checkbox and v3 widgets.

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

When disabled, the component renders a plain submit button without loading any external scripts, and all server-side verification passes automatically.

Alternatively, Google provides a [test key pair](https://developers.google.com/recaptcha/docs/faq#id-like-to-run-automated-tests-with-recaptcha.-what-should-i-do) that renders a real widget and always passes verification.

```
Site key: 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
Secret key: 6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
```

Test with the real widget before going live, since disabling reCAPTCHA renders a different button and skips the checks.

## Content Security Policy

If the site sends a Content Security Policy, it must allow Google's reCAPTCHA sources. Without `connect-src`, the widget renders but never produces a token, and every submission fails verification.

```
script-src  https://www.google.com https://www.gstatic.com
frame-src   https://www.google.com
connect-src https://www.google.com
```

## License

This plugin is officially maintained by the October CMS team and is available under the [MIT license](LICENSE).

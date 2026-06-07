---
name: create-email
description: Create a new system transactional email — model class, EmailId case, and admin template instructions.
argument-hint: <EmailName>
---

# Create Transactional Email

Create all code artifacts for a new system transactional email and output instructions for
completing the setup in the admin panel.

## Step 0 — Read existing files before generating

Read these files in full before doing anything else:

1. `src/Email/EmailId.php` — to see the existing cases, `getVariables()`, and `trans()` arms.
2. `src/Email/Model/LostPassword.php` — example model with a constructor parameter.
3. `src/Email/Model/PremiumEndingReminder.php` — example model with no parameters.

Confirm the exact namespace (`Sledujteto\Email\Model`) and interface
(`Sovic\Cms\Email\Model\EmailModelInterface`) before writing anything.

## Step 1 — Ask for name, ID, and variables

Present all questions together in one round. Do not generate any files until the user answers.

### Questions to ask

**1. Class name** (PascalCase, used as the PHP class and file name):
- Suggest derived from the argument: e.g. argument `"Premium Ending Reminder"` → `PremiumEndingReminder`
- Show the suggestion and ask to confirm or override.

**2. Email ID string** (kebab-case, stored in the database and used to match templates):
- Suggest derived from the class name: `PremiumEndingReminder` → `premium-ending-reminder`
- Show the suggestion and ask to confirm or override.
- Must be unique — scan the existing `EmailId` cases and warn if the suggested value conflicts.

**3. Human-readable label** (Czech, used in `trans()` and in the admin panel):
- Suggest a reasonable label based on the name, following the existing pattern:
  `Premium: Upomínka na končící prémiové členství`
  Categories used: `Uživatel:`, `Premium:`, `Obecné:`
- Ask the user to confirm or provide their own.

**4. Template variables** (zero or more):
- Ask the user to list variables as `variable_name: Czech description` pairs, one per line.
- Example:
  ```
  activation_url: Odkaz pro aktivaci účtu
  premium_to: Datum konce prémiového členství
  ```
- Empty input means no variables (the email has no dynamic data).
- Variable names must be `snake_case`.

### What to do with the answers

- **Class name** → PHP class name + file name: `src/Email/Model/<ClassName>.php`
- **Email ID string** → `EmailId` enum case value (the string after `=`)
- **Class name (PascalCase)** → `EmailId` enum case name (e.g. `case PremiumEndingReminder`)
- **Czech label** → `trans()` arm for the new case
- **Variables** → constructor parameters + `getData()` array + `getVariables()` arm

## Step 2 — Create the Email Model class

**File:** `src/Email/Model/<ClassName>.php`

### No variables (empty model)

```php
<?php

namespace Sledujteto\Email\Model;

use Sledujteto\Email\EmailId;
use Sovic\Cms\Email\Model\EmailModelInterface;

readonly class <ClassName> implements EmailModelInterface
{
    public function __construct()
    {
    }

    public function getId(): EmailId
    {
        return EmailId::<ClassName>;
    }

    public function getData(): array
    {
        return [];
    }
}
```

### With variables

Each variable becomes a `private string $varName` constructor parameter (use the most specific
scalar type: `string`, `int`, `bool`, `DateTimeInterface` when it represents a date/time).
The `getData()` array maps `snake_case` keys to their values.

```php
<?php

namespace Sledujteto\Email\Model;

use Sledujteto\Email\EmailId;
use Sovic\Cms\Email\Model\EmailModelInterface;

readonly class <ClassName> implements EmailModelInterface
{
    public function __construct(
        private string $activationUrl,
        private string $premiumTo,
    ) {
    }

    public function getId(): EmailId
    {
        return EmailId::<ClassName>;
    }

    public function getData(): array
    {
        return [
            'activation_url' => $this->activationUrl,
            'premium_to'     => $this->premiumTo,
        ];
    }
}
```

**Rules:**
- Class is `readonly`, not `final` (matches existing models).
- Constructor parameter names are `camelCase`; array keys in `getData()` are `snake_case`.
- Do not add type-juggling or formatting inside `getData()` — callers are responsible.

## Step 3 — Update EmailId enum

**File:** `src/Email/EmailId.php`

Make three edits to this file:

### 3a — Add the case (alphabetical by case name)

```php
case <ClassName> = '<email-id-string>';
```

Insert it in alphabetical order among the existing cases.

### 3b — Add a `getVariables()` arm

If the email has **no variables**, add the new case to the existing group that returns `[]`:

```php
self::Christmas,
self::<ClassName>,        // ← insert here, alphabetically
self::PremiumEndedReminder => [],
```

If it has **variables**, add a new arm with the variable descriptions in Czech:

```php
self::<ClassName> => [
    'activation_url' => 'Odkaz pro aktivaci účtu',
    'premium_to'     => 'Datum konce prémiového členství',
],
```

### 3c — Add a `trans()` arm

```php
self::<ClassName> => '<Czech label>',
```

Insert it in alphabetical order by case name inside the `match`.

## Step 4 — Output admin instructions

After creating the files, print the following instructions verbatim (fill in the placeholders):

---

**Next: create the email template in admin**

1. Go to **Admin → Emails → New email**
2. Fill in:
   - **Email ID:** `<email-id-string>`
   - **Name:** `<Czech label>`
   - **Language:** `cs` (duplicate for other languages if needed)
   - **Subject:** *(write a subject line)*
   - **Body:** *(write the HTML body using the variables below)*

3. Available template variables (wrap in `{}`):

<if variables>
| Variable | Description |
|---|---|
| `{variable_name}` | Czech description |
| ... | ... |
</if variables>
<if no variables>
   *(this email has no dynamic variables)*
</if no variables>

---

## Step 5 — Output usage example

After the admin instructions, print a PHP usage snippet showing how to send the email.
Use the actual class name, constructor arguments, and the EmailManager pattern from the codebase:

```php
use Sledujteto\Email\Model\<ClassName>;
use Sovic\Cms\Email\EmailManagerInterface;

// inject EmailManagerInterface $emailManager

$model = new <ClassName>(<constructor args>);
$emailManager->send(model: $model, emailTo: $recipientEmail);
```

If the email has no variables, the constructor call is `new <ClassName>()`.

Use named argument `model:` and `emailTo:` to match the existing call style in the codebase.

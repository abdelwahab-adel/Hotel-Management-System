<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Small rule-based validator. The original project trusted every $_POST
 * value directly into SQL and HTML with no length/type/format checks at
 * all (e.g. registration accepted an empty username, any "password").
 */
final class Validator
{
    private array $errors = [];

    public function __construct(private array $data)
    {
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function validated(): array
    {
        return $this->data;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    private function value(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    public function required(string $field, string $label): self
    {
        $value = $this->value($field);
        if ($value === null || (is_string($value) && trim($value) === '')) {
            $this->addError($field, "{$label} is required.");
        }
        return $this;
    }

    public function email(string $field, string $label = 'Email'): self
    {
        $value = $this->value($field);
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "{$label} must be a valid email address.");
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label): self
    {
        $value = (string) $this->value($field);
        if (mb_strlen($value) < $min) {
            $this->addError($field, "{$label} must be at least {$min} characters.");
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        $value = (string) $this->value($field);
        if (mb_strlen($value) > $max) {
            $this->addError($field, "{$label} must not exceed {$max} characters.");
        }
        return $this;
    }

    public function alphaDash(string $field, string $label): self
    {
        $value = (string) $this->value($field);
        if ($value !== '' && !preg_match('/^[A-Za-z0-9_.\-]+$/', $value)) {
            $this->addError($field, "{$label} may only contain letters, numbers, dashes and underscores.");
        }
        return $this;
    }

    public function numeric(string $field, string $label): self
    {
        $value = $this->value($field);
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, "{$label} must be a number.");
        }
        return $this;
    }

    public function date(string $field, string $label): self
    {
        $value = (string) $this->value($field);
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$d || $d->format('Y-m-d') !== $value) {
            $this->addError($field, "{$label} must be a valid date (YYYY-MM-DD).");
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label): self
    {
        $value = $this->value($field);
        if ($value !== null && !in_array($value, $allowed, true)) {
            $this->addError($field, "{$label} is invalid.");
        }
        return $this;
    }

    public function phone(string $field, string $label = 'Phone number'): self
    {
        $value = (string) $this->value($field);
        if ($value !== '' && !preg_match('/^[0-9+\-\s()]{6,25}$/', $value)) {
            $this->addError($field, "{$label} format is invalid.");
        }
        return $this;
    }

    public function confirmed(string $field, string $confirmationField, string $label): self
    {
        if ($this->value($field) !== $this->value($confirmationField)) {
            $this->addError($field, "{$label} confirmation does not match.");
        }
        return $this;
    }
}

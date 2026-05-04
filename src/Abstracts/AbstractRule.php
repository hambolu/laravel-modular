<?php

namespace LaravelModular\Abstracts;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use LaravelModular\Traits\Injectable;

/**
 * Base custom validation rule.
 *
 * Usage:
 *   class UniqueEmailRule extends AbstractRule
 *   {
 *       public function validate(string $attribute, mixed $value, Closure $fail): void
 *       {
 *           if (User::where('email', $value)->exists()) {
 *               $fail("The :attribute is already taken.");
 *           }
 *       }
 *   }
 */
abstract class AbstractRule implements ValidationRule
{
    use Injectable;

    abstract public function validate(string $attribute, mixed $value, Closure $fail): void;
}

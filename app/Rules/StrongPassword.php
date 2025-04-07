<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $passwordRules = Password::defaults();

        $validator = validator([$attribute => $value], [
            $attribute => $passwordRules,
        ]);

        if ($validator->fails()) {
            $fail($validator->errors()->first($attribute));
        }
    }
}

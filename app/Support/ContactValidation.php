<?php

namespace App\Support;

use Closure;

class ContactValidation
{
    public static function allowedEmailDomains(): array
    {
        return ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
    }

    public static function emailRules(bool $required = true, int $max = 255): array
    {
        $requiredRule = $required ? 'required' : 'nullable';

        return [
            $requiredRule,
            'string',
            'email',
            "max:{$max}",
            static function (string $attribute, mixed $value, Closure $fail): void {
                $email = mb_strtolower(trim((string) $value));
                $domain = substr(strrchr($email, '@') ?: '', 1);

                if ($domain === '' || !in_array($domain, self::allowedEmailDomains(), true)) {
                    $fail('يسمح فقط ببريد من الدومينات التالية: gmail.com أو yahoo.com أو outlook.com أو hotmail.com.');
                }
            },
        ];
    }

    public static function egyptianMobileRules(bool $required = true): array
    {
        $requiredRule = $required ? 'required' : 'nullable';

        return [
            $requiredRule,
            'string',
            'regex:/^1[0125][0-9]{8}$/',
        ];
    }

    public static function normalizeEgyptianMobile(string $phone): string
    {
        $trimmed = trim($phone);
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        if ($digits === '') {
            return '';
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            return '+20' . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '01')) {
            return '+2' . $digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '20')) {
            return '+' . $digits;
        }

        return '+' . ltrim($digits, '+');
    }

    public static function messages(): array
    {
        return [
            'email.required' => 'من فضلك اكتب البريد الإلكتروني.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.max' => 'البريد الإلكتروني طويل جدًا.',

            'phone.required' => 'من فضلك اكتب رقم الموبايل.',
            'phone.regex' => 'رقم الموبايل غير صحيح. اكتب 10 أرقام بدون +20 (مثال: 1206628718).',

            'customer_phone.required' => 'من فضلك اكتب رقم الموبايل.',
            'customer_phone.regex' => 'رقم الموبايل غير صحيح. اكتب 10 أرقام بدون +20 (مثال: 1206628718).',
        ];
    }
}

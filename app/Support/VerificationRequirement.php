<?php

namespace App\Support;

use App\Models\ApplicationSetting;

class VerificationRequirement
{
    public const KEY = 'require_customer_email_verification';

    public function required(): bool
    {
        $value = ApplicationSetting::query()->where('key', self::KEY)->value('value');

        return $value === null || filter_var($value, FILTER_VALIDATE_BOOL);
    }

    public function update(bool $required): void
    {
        ApplicationSetting::query()->updateOrCreate(
            ['key' => self::KEY],
            ['value' => $required ? '1' : '0'],
        );
    }
}

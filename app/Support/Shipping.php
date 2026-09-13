<?php

namespace App\Support;

use App\Models\ApplicationSetting;
use App\Models\ShippingRate;

class Shipping
{
    public const THRESHOLD_KEY = 'free_shipping_threshold';

    public function threshold(): ?string
    {
        $value = ApplicationSetting::where('key', self::THRESHOLD_KEY)->value('value');

        return is_numeric($value) ? number_format((float) $value, 2, '.', '') : null;
    }

    public function updateThreshold(?string $value): void
    {
        ApplicationSetting::updateOrCreate(['key' => self::THRESHOLD_KEY], ['value' => filled($value) ? number_format((float) $value, 2, '.', '') : null]);
    }

    public function feeFor(?ShippingRate $rate, string $eligibleAmount): string
    {
        $threshold = $this->threshold();
        if ($threshold !== null && bccomp($eligibleAmount, $threshold, 2) >= 0) {
            return '0.00';
        }

        return $rate ? (string) $rate->fee : '0.00';
    }
}

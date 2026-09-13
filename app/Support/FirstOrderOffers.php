<?php

namespace App\Support;

use App\Models\FirstOrderOffer;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class FirstOrderOffers
{
    private const REWARDS = [
        ['percentage', 5], ['percentage', 10], ['percentage', 15],
        ['fixed', 100], ['fixed', 200], ['free_shipping', 0],
    ];

    public function eligible(User $user): bool
    {
        return ! $user->orders()->where('status', '!=', 'cancelled')->exists()
            && ! FirstOrderOffer::where('user_id', $user->id)->whereNotNull('used_at')->exists();
    }

    public function claim(User $user): FirstOrderOffer
    {
        $existing = FirstOrderOffer::where('user_id', $user->id)->first();
        if ($existing) {
            return $existing;
        }
        if (! $this->eligible($user)) {
            throw ValidationException::withMessages(['offer' => 'This offer is only available before your first order.']);
        }
        [$type, $value] = self::REWARDS[random_int(0, count(self::REWARDS) - 1)];

        return FirstOrderOffer::create(['user_id' => $user->id, 'reward_type' => $type, 'reward_value' => $value, 'claimed_at' => now()]);
    }

    public function discount(FirstOrderOffer $offer, string $merchandise): string
    {
        $discount = match ($offer->reward_type) {
            'percentage' => bcdiv(bcmul($merchandise, $offer->reward_value, 2), '100', 2),
            'fixed' => $offer->reward_value,
            default => '0.00',
        };

        return bccomp($discount, $merchandise, 2) === 1 ? $merchandise : $discount;
    }
}

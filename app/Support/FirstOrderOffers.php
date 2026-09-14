<?php

namespace App\Support;

use App\Models\FirstOrderOffer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FirstOrderOffers
{
    public const GUEST_SESSION_KEY = 'first_order_offer_guest';

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

    public function claimForRequest(Request $request): array
    {
        if ($request->user()) {
            $offer = $this->reconcileGuest($request, $request->user()) ?? $this->claim($request->user());

            return ['label' => $offer->label()];
        }

        $reward = $request->session()->get(self::GUEST_SESSION_KEY);
        if (! is_array($reward)) {
            [$type, $value] = self::REWARDS[random_int(0, count(self::REWARDS) - 1)];
            $reward = ['token' => bin2hex(random_bytes(32)), 'reward_type' => $type, 'reward_value' => $value, 'claimed_at' => now()->toIso8601String()];
            $request->session()->put(self::GUEST_SESSION_KEY, $reward);
        }

        return ['label' => $this->labelFor($reward['reward_type'], (string) $reward['reward_value'])];
    }

    public function reconcileGuest(Request $request, User $user): ?FirstOrderOffer
    {
        $guest = $request->session()->get(self::GUEST_SESSION_KEY);
        if (! is_array($guest)) {
            return $user->firstOrderOffer()->first();
        }

        $offer = $user->firstOrderOffer()->first();
        if (! $offer && $this->eligible($user)) {
            $offer = FirstOrderOffer::create([
                'user_id' => $user->id,
                'reward_type' => $guest['reward_type'],
                'reward_value' => $guest['reward_value'],
                'claimed_at' => $guest['claimed_at'] ?? now(),
            ]);
        }
        $request->session()->forget(self::GUEST_SESSION_KEY);

        return $offer;
    }

    public function guestReward(Request $request): ?array
    {
        $reward = $request->session()->get(self::GUEST_SESSION_KEY);

        return is_array($reward) && isset($reward['reward_type'], $reward['reward_value']) ? $reward : null;
    }

    public function shouldPrompt(Request $request): bool
    {
        if ((bool) $request->session()->get('first_order_offer_prompt_dismissed', false)) {
            return false;
        }

        return $request->user()
            ? ! $request->user()->firstOrderOffer()->exists() && $this->eligible($request->user())
            : ! $this->guestReward($request);
    }

    public function discount(FirstOrderOffer $offer, string $merchandise): string
    {
        return $this->discountFor($offer->reward_type, $offer->reward_value, $merchandise);
    }

    public function discountFor(string $type, string $value, string $merchandise): string
    {
        $discount = match ($type) {
            'percentage' => bcdiv(bcmul($merchandise, $value, 2), '100', 2),
            'fixed' => $value,
            default => '0.00',
        };

        return bccomp($discount, $merchandise, 2) === 1 ? $merchandise : $discount;
    }

    public function labelFor(string $type, string $value): string
    {
        return match ($type) {
            'percentage' => rtrim(rtrim($value, '0'), '.').'% off',
            'fixed' => number_format((float) $value, 0).' EGP off',
            'free_shipping' => 'Free shipping',
            default => 'First-order offer',
        };
    }
}

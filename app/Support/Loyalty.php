<?php

namespace App\Support;

use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class Loyalty
{
    public const EARNING_EGP_PER_POINT = 10;

    public const REDEMPTION_BLOCK = 100;

    public const EGP_PER_POINT = '0.10';

    public function redemptionDiscount(int $points): string
    {
        return bcmul((string) $points, self::EGP_PER_POINT, 2);
    }

    public function redeem(User $user, Order $order, int $points, string $merchandisePayable): void
    {
        if ($points === 0) {
            return;
        }
        if ($points < 0 || $points % self::REDEMPTION_BLOCK !== 0 || $points > $user->loyalty_points) {
            throw ValidationException::withMessages(['points_to_redeem' => 'Choose an available points amount in blocks of 100.']);
        }
        if (bccomp($this->redemptionDiscount($points), $merchandisePayable, 2) === 1) {
            throw ValidationException::withMessages(['points_to_redeem' => 'Points cannot exceed the merchandise amount.']);
        }

        $user->loyalty_points -= $points;
        $user->save();
        $this->record($user, $order, 'redeemed', -$points, "Redeemed on order {$order->order_number}");
    }

    public function awardForDelivered(Order $order): void
    {
        if (! $order->user_id || $order->points_earned_at) {
            return;
        }
        $eligible = max(0, (int) floor((float) bcsub(bcsub((string) $order->subtotal, (string) $order->coupon_discount, 2), (string) $order->points_discount, 2)));
        $points = intdiv($eligible, self::EARNING_EGP_PER_POINT);
        $user = User::withTrashed()->lockForUpdate()->find($order->user_id);
        if (! $user) {
            return;
        }
        if ($points > 0) {
            $user->loyalty_points += $points;
            $user->save();
            $this->record($user, $order, 'earned', $points, "Earned from delivered order {$order->order_number}");
        }
        $order->forceFill(['points_earned' => $points, 'points_earned_at' => now()])->save();
    }

    public function reverseForCancellation(Order $order): void
    {
        $user = $order->user_id ? User::withTrashed()->lockForUpdate()->find($order->user_id) : null;
        if (! $user) {
            return;
        }
        if ($order->points_redeemed > 0 && ! $order->points_redemption_restored_at) {
            $user->loyalty_points += $order->points_redeemed;
            $user->save();
            $this->record($user, $order, 'cancellation_restoration', $order->points_redeemed, "Restored from cancelled order {$order->order_number}");
            $order->points_redemption_restored_at = now();
        }
        if ($order->points_earned > 0 && ! $order->points_reversed_at) {
            $change = -min($order->points_earned, $user->loyalty_points);
            $user->loyalty_points += $change;
            $user->save();
            $this->record($user, $order, 'cancellation_reversal', $change, "Reversed from cancelled order {$order->order_number}");
            $order->points_reversed_at = now();
        }
        $order->save();
    }

    public function adjust(User $user, User $admin, int $change, string $reason): void
    {
        $locked = User::withTrashed()->lockForUpdate()->findOrFail($user->id);
        if ($locked->loyalty_points + $change < 0) {
            throw ValidationException::withMessages(['points_change' => 'The adjustment cannot make the balance negative.']);
        }
        $locked->loyalty_points += $change;
        $locked->save();
        LoyaltyTransaction::create(['user_id' => $locked->id, 'admin_user_id' => $admin->id, 'type' => 'admin_adjustment', 'points_change' => $change, 'balance_after' => $locked->loyalty_points, 'description' => $reason]);
    }

    private function record(User $user, Order $order, string $type, int $change, string $description): void
    {
        LoyaltyTransaction::create(['user_id' => $user->id, 'order_id' => $order->id, 'type' => $type, 'points_change' => $change, 'balance_after' => $user->loyalty_points, 'description' => $description]);
    }
}

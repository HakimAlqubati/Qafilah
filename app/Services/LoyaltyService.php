<?php

namespace App\Services;

use App\Models\CustomerLoyaltyWallet;
use App\Models\LoyaltyTransaction;
use App\Models\MerchantLoyaltySetting;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoyaltyService
{
    /**
     * Get or create the customer loyalty wallet for a specific merchant.
     */
    public function getOrCreateWallet(int $customerId, int $merchantId): CustomerLoyaltyWallet
    {
        return CustomerLoyaltyWallet::firstOrCreate(
            [
                'customer_id' => $customerId,
                'merchant_id' => $merchantId,
            ],
            [
                'balance' => 0,
            ]
        );
    }

    /**
     * Award loyalty points for a completed order.
     */
    public function awardPointsForOrder(Order $order): ?LoyaltyTransaction
    {
        if (!$order->customer_id || !$order->vendor_id) {
            return null;
        }

        // Check if merchant has loyalty program enabled
        $setting = MerchantLoyaltySetting::where('merchant_id', $order->vendor_id)->first();
        if (!$setting || !$setting->is_active) {
            return null;
        }

        if ($setting->earning_spend_amount <= 0 || $setting->earning_reward_points <= 0) {
            return null;
        }

        // Prevent duplicate awarding (Idempotency)
        $alreadyAwarded = LoyaltyTransaction::where('order_id', $order->id)
            ->where('type', LoyaltyTransaction::TYPE_EARNED)
            ->exists();

        if ($alreadyAwarded) {
            return null;
        }

        // Calculate points based on order subtotal
        $subtotal = (float) $order->subtotal;
        $units = floor($subtotal / (float) $setting->earning_spend_amount);
        $pointsToAward = (int) ($units * $setting->earning_reward_points);

        if ($pointsToAward <= 0) {
            return null;
        }

        return DB::transaction(function () use ($order, $pointsToAward) {
            $wallet = $this->getOrCreateWallet($order->customer_id, $order->vendor_id);
            $wallet->increment('balance', $pointsToAward);

            $orderLabel = $order->order_number ?: "#{$order->id}";
            $description = __('lang.points_earned_from_order', ['order' => $orderLabel]);

            return LoyaltyTransaction::create([
                'wallet_id'   => $wallet->id,
                'order_id'    => $order->id,
                'type'        => LoyaltyTransaction::TYPE_EARNED,
                'points'      => $pointsToAward,
                'description' => $description,
            ]);
        });
    }

    /**
     * Redeem customer loyalty points for a discount on an order.
     *
     * @return float The discount amount applied
     */
    public function redeemPointsForOrder(Order $order, int $pointsToRedeem): float
    {
        if ($pointsToRedeem <= 0 || !$order->customer_id || !$order->vendor_id) {
            return 0.0;
        }

        $setting = MerchantLoyaltySetting::where('merchant_id', $order->vendor_id)->first();
        if (!$setting || !$setting->is_active) {
            return 0.0;
        }

        if ($pointsToRedeem < $setting->min_points_to_redeem) {
            return 0.0;
        }

        $wallet = CustomerLoyaltyWallet::where('customer_id', $order->customer_id)
            ->where('merchant_id', $order->vendor_id)
            ->first();

        if (!$wallet || $wallet->balance < $pointsToRedeem) {
            return 0.0;
        }

        $discount = round($pointsToRedeem * (float) $setting->redemption_discount_value, 2);

        // Cap discount to order subtotal if subtotal > 0
        if ($order->subtotal > 0 && $discount > (float) $order->subtotal) {
            $discount = (float) $order->subtotal;
        }

        DB::transaction(function () use ($wallet, $order, $pointsToRedeem) {
            $wallet->decrement('balance', $pointsToRedeem);

            $orderLabel = $order->order_number ?: "#{$order->id}";
            $description = __('lang.points_redeemed_for_order', ['order' => $orderLabel]);

            LoyaltyTransaction::create([
                'wallet_id'   => $wallet->id,
                'order_id'    => $order->id,
                'type'        => LoyaltyTransaction::TYPE_REDEEMED,
                'points'      => -$pointsToRedeem,
                'description' => $description,
            ]);
        });

        return $discount;
    }

    /**
     * Handle order cancellation or return:
     * 1. If points were redeemed for this order, restore them to customer wallet.
     * 2. If points were earned from this order, revoke/reverse them from customer wallet.
     */
    public function handleOrderRefundOrCancellation(Order $order): void
    {
        if (!$order->customer_id || !$order->vendor_id) {
            return;
        }

        DB::transaction(function () use ($order) {
            $orderLabel = $order->order_number ?: "#{$order->id}";

            // 1. Restore redeemed points if any
            $redeemedTx = LoyaltyTransaction::where('order_id', $order->id)
                ->where('type', LoyaltyTransaction::TYPE_REDEEMED)
                ->first();

            if ($redeemedTx) {
                $alreadyRestored = LoyaltyTransaction::where('order_id', $order->id)
                    ->where('type', LoyaltyTransaction::TYPE_REDEEMED_RESTORED)
                    ->exists();

                if (!$alreadyRestored) {
                    $pointsToRestore = abs($redeemedTx->points);
                    $wallet = CustomerLoyaltyWallet::find($redeemedTx->wallet_id);

                    if ($wallet) {
                        $wallet->increment('balance', $pointsToRestore);

                        LoyaltyTransaction::create([
                            'wallet_id'   => $wallet->id,
                            'order_id'    => $order->id,
                            'type'        => LoyaltyTransaction::TYPE_REDEEMED_RESTORED,
                            'points'      => $pointsToRestore,
                            'description' => __('lang.points_redeemed_restored_for_order', ['order' => $orderLabel]),
                        ]);
                    }
                }
            }

            // 2. Reverse earned points if any
            $earnedTx = LoyaltyTransaction::where('order_id', $order->id)
                ->where('type', LoyaltyTransaction::TYPE_EARNED)
                ->first();

            if ($earnedTx) {
                $alreadyReversed = LoyaltyTransaction::where('order_id', $order->id)
                    ->where('type', LoyaltyTransaction::TYPE_EARNED_REVERSED)
                    ->exists();

                if (!$alreadyReversed) {
                    $pointsToDeduct = abs($earnedTx->points);
                    $wallet = CustomerLoyaltyWallet::find($earnedTx->wallet_id);

                    if ($wallet) {
                        // Allow balance to go negative if customer already spent points
                        $wallet->decrement('balance', $pointsToDeduct);

                        LoyaltyTransaction::create([
                            'wallet_id'   => $wallet->id,
                            'order_id'    => $order->id,
                            'type'        => LoyaltyTransaction::TYPE_EARNED_REVERSED,
                            'points'      => -$pointsToDeduct,
                            'description' => __('lang.points_earned_reversed_for_order', ['order' => $orderLabel]),
                        ]);
                    }
                }
            }
        });
    }
}

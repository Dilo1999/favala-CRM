<?php

namespace App\Services;

/**
 * Single source of truth for the quotation/invoice pricing sequence (spec §8).
 * Used by the Quotation builder AND the standalone Price Calculator so both
 * always agree on the numbers.
 */
class PricingEngine
{
    /**
     * Price a single product line.
     *
     * @return array{unit_price: float, gross_amount: float, discount_amount: float, line_amount: float, cost_amount: float, profit: float}
     */
    public static function line(float $cost, float $qty, float $markupPercent, string $discountType = 'flat', float $discountValue = 0): array
    {
        // Round the unit price to cents *before* multiplying by qty — otherwise the
        // gross/line amount can drift a cent away from unit_price × qty, showing a
        // phantom discount on the printout even when discount_value is 0.
        $unitPrice = round($cost * (1 + $markupPercent / 100), 2);
        $grossAmount = round($unitPrice * $qty, 2);

        $discountAmount = $discountType === 'percent'
            ? round($grossAmount * ($discountValue / 100), 2)
            : round($discountValue, 2);
        $discountAmount = min($discountAmount, $grossAmount);

        $lineAmount = round($grossAmount - $discountAmount, 2);
        $costAmount = round($cost * $qty, 2);
        $profit = round($lineAmount - $costAmount, 2);

        return [
            'unit_price' => $unitPrice,
            'gross_amount' => $grossAmount,
            'discount_amount' => $discountAmount,
            'line_amount' => $lineAmount,
            'cost_amount' => $costAmount,
            'profit' => $profit,
        ];
    }

    /**
     * Roll a set of priced lines up to order level: order discount, GST, grand total, profit margin.
     *
     * A line that already carries its own discount (discount_amount > 0) is
     * excluded from the base the order-level discount applies to — it isn't
     * discounted a second time on top of whatever was already taken off it.
     *
     * @param  array<int, array{line_amount: float, profit: float, discount_amount?: float}>  $lines
     * @return array{subtotal: float, order_discount_amount: float, taxable_amount: float, gst_amount: float, grand_total: float, total_profit: float, profit_margin: float}
     */
    public static function order(array $lines, string $discountType = 'flat', float $discountValue = 0, float $gstPercent = 8): array
    {
        $subtotal = round(array_sum(array_column($lines, 'line_amount')), 2);
        $totalProfit = round(array_sum(array_column($lines, 'profit')), 2);

        $undiscountedSubtotal = round(array_sum(array_map(
            fn (array $line) => ($line['discount_amount'] ?? 0) > 0 ? 0 : $line['line_amount'],
            $lines
        )), 2);

        $orderDiscount = $discountType === 'percent'
            ? $undiscountedSubtotal * ($discountValue / 100)
            : $discountValue;
        $orderDiscount = min(max($orderDiscount, 0), $undiscountedSubtotal);

        $taxableAmount = round($subtotal - $orderDiscount, 2);
        $gstAmount = round($taxableAmount * ($gstPercent / 100), 2);
        $grandTotal = round($taxableAmount + $gstAmount, 2);

        $profitMargin = $subtotal > 0 ? round(($totalProfit / $subtotal) * 100, 2) : 0.0;

        return [
            'subtotal' => $subtotal,
            'order_discount_amount' => round($orderDiscount, 2),
            'taxable_amount' => $taxableAmount,
            'gst_amount' => $gstAmount,
            'grand_total' => $grandTotal,
            'total_profit' => $totalProfit,
            'profit_margin' => $profitMargin,
        ];
    }

    /**
     * Standalone Price Calculator (spec §6.17) — a single line priced and summarised in one call.
     */
    public static function calculate(float $cost, float $markupPercent, string $discountType, float $discountValue, float $gstPercent): array
    {
        $line = self::line($cost, 1, $markupPercent, $discountType, $discountValue);
        $gstAmount = round($line['line_amount'] * ($gstPercent / 100), 2);

        return [
            'cost_price' => round($cost, 2),
            'markup_amount' => round($line['unit_price'] - $cost, 2),
            'selling_price' => $line['unit_price'],
            'discount_amount' => $line['discount_amount'],
            'price_after_discount' => $line['line_amount'],
            'gst_amount' => $gstAmount,
            'final_price' => round($line['line_amount'] + $gstAmount, 2),
            'profit' => $line['profit'],
            'profit_margin' => $line['unit_price'] > 0 && $line['line_amount'] > 0
                ? round(($line['profit'] / $line['line_amount']) * 100, 2)
                : 0.0,
        ];
    }
}

<?php

namespace App\Enums;

enum StockBatchType: string
{
    case ITEM_SALE = 'item_sale';
    case KIT_SALE = 'kit_sale';
    case RECEIVING = 'receiving';
    case ADJUSTMENT = 'adjustment';
    case ADJUSTMENT_NEGATIVE = 'adjustment_negative';
    case TRANSFER = 'transfer';
    // Optional: label for UI
    public function label(): string
    {
        return match($this) {
            self::ITEM_SALE => 'Item Sale',
            self::KIT_SALE => 'Kit Sale',
            self::RECEIVING => 'Receiving',
            self::ADJUSTMENT => 'Adjustment',
            self::ADJUSTMENT_NEGATIVE => 'Adjustment Negative',
            self::TRANSFER => 'Transfer',
        };
    }
}

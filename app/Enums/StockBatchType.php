<?php

namespace App\Enums;

enum StockBatchType: string
{
    case ITEM_SALE = 'item_sale';
    case KIT_SALE = 'kit_sale';
    case NEW_ITEM = 'new_item';
    case RECEIVING = 'receiving';
    case ADJUSTMENT = 'adjustment';
    case ADJUSTMENT_NEGATIVE = 'adjustment_negative';
    case TRANSFER = 'transfer';
    case CLOSING_STOCK = 'closing_stock';
    // Optional: label for UI
    public function label(): string
    {
        return match($this) {
            self::ITEM_SALE => 'Item Sale',
            self::KIT_SALE => 'Kit Sale',
            self::RECEIVING => 'Receiving',
            self::NEW_ITEM => 'New Item',
            self::ADJUSTMENT => 'Adjustment',
            self::ADJUSTMENT_NEGATIVE => 'Adjustment Negative',
            self::TRANSFER => 'Transfer',
            self::CLOSING_STOCK => 'Closing Stock'
        };
    }
}

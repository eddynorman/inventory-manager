<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateSystemNotifications extends Command
{
    protected $signature = 'system:notifications';
    protected $description = 'Generate notifications for low stock and expiries';

    public function handle(): int
    {
        $lowStock = Item::whereColumn('current_stock', '<=', 'reorder_level')->get();
        foreach ($lowStock as $item) {
            Notification::firstOrCreate([
                'message' => "Low stock for {$item->name} (Barcode: {$item->barcode})",
                'type' => 'warning',
                'category' => 'stock',
            ], [
                'is_read' => false,
            ]);
        }

        $today = now()->toDateString();
        $expiring = \App\Models\ItemExpiryDate::with('item')
            ->whereDate('expiry_date', '<=', Carbon::now()->addDays(7)->toDateString())
            ->get();
        foreach ($expiring as $exp) {
            $name = $exp->item->name ?? 'Unknown Item';
            Notification::firstOrCreate([
                'message' => "Expiry approaching for {$name} on {$exp->expiry_date}",
                'type' => 'warning',
                'category' => 'expiry',
            ], [
                'is_read' => false,
            ]);
        }

        $this->info('System notifications generated.');
        return self::SUCCESS;
    }
}



<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Location;
use App\Models\Supplier;
use App\Services\ItemService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ItemImport implements ToCollection
{
    protected ItemService $itemService;
    protected int $department_id;
    public array $errors = [];

    public function __construct(ItemService $itemService, int $department_id)
    {
        $this->itemService = $itemService;
        $this->department_id = $department_id;
    }
    private function toBool($value): bool
    {
        return in_array(strtolower(trim($value)), ['true','TRUE', 't', '1']);
    }

    public function collection(Collection $rows)
    {

        foreach ($rows->skip(1) as $index => $row) {
            try {
                DB::beginTransaction();

                $category = Category::firstOrCreate([
                    'department_id' => $this->department_id,
                    'name' => trim($row[2])
                ]);

                $supplier = !empty($row[3])
                    ? Supplier::where('name', 'like', '%' . $row[3] . '%')->first()
                    : null;

                $location = Location::where('name', 'like', '%' . $row[13] . '%')->first();

                if (!$location) {
                    throw new \Exception("Location not found");
                }

                $data = [
                    'name' => $row[0],
                    'barcode' => !empty($row[1]) ? $row[1] : null,
                    'categoryId' => $category->id,
                    'supplierId' => $supplier?->id,
                    'locationId' => null,
                    'newLocationId' => $location->id,
                    'initialStock' => $row[4] ?? 0,
                    'reorderLevel' => $row[5] ?? 0,
                    'smallestUnitId' => null,
                    'smallestUnit' => $row[10],
                    'buyingPrice' => $row[11],
                    'sellingPrice' => $row[12],
                    'buyingPriceIncludesTax' => false,
                    'sellingPriceIncludesTax' => false,
                    'isSaleItem' => $this->toBool($row[6] ?? 'true'),
                    'isStockItem' => $this->toBool($row[7] ?? 'true'),
                    'isAutoTracked' => $this->toBool($row[8] ?? 'true'),
                    'isActive' => $this->toBool($row[9] ?? 'true'),
                ];

                // 🔥 VALIDATE using same rules
                $validator = Validator::make(
                    $data,
                    $this->itemService->rules(null)
                );

                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }

                $this->itemService->save(null, $data);

                DB::commit();

            } catch (ValidationException $e) {
                DB::rollBack();

                $this->errors[] = [
                    'row' => $index + 1,
                    'error' => collect($e->errors())->flatten()->implode(', ')
                ];

            } catch (\Throwable $e) {
                DB::rollBack();

                $this->errors[] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $this->errors;
    }
}

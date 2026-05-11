<?php

namespace App\Imports;

use App\Models\Department;
use App\Services\AssetService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class AssetImport implements ToCollection
{
    protected AssetService $service;
    public array $errors = [];

    public function __construct(AssetService $service)
    {
        $this->service = $service;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows->skip(1) as $index => $row) {

            try {
                DB::beginTransaction();
                $department = Department::where('name', 'like', '%' . $row[1] . '%')->first();

                if (!$department) {
                    throw new \Exception("Department ".$row[1]." not found");
                }
                $data = [
                    'name' => trim($row[0]),
                    'department_id' => $department->id,
                    'initial_quantity' => $row[2],
                    'initial_unit_cost' => $row[3],
                    'initial_purchase_date' => $row[4],
                ];

                // 🔥 wrap into form structure
                $validator = Validator::make(
                    ['form' => $data],
                    $this->service->rules(null),
                    $this->service->messages()
                );

                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }

                $this->service->createItem(['form' => $data]);

                DB::commit();

            } catch (ValidationException $e) {
                DB::rollBack();

                $this->errors[] = [
                    'row' => $index,
                    'error' => collect($e->errors())->flatten()->implode(', ')
                ];

            } catch (\Throwable $e) {
                DB::rollBack();

                $this->errors[] = [
                    'row' => $index,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $this->errors;
    }
}

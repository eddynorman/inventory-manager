<?php

namespace App\Services;

use App\Models\DefaultSaleLocation;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrganisationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function rules(){
        return [
            'organisation' => ['required', 'array'],

            'organisation.name' => ['required', 'string', 'max:255'],

            'organisation.email' => [
                'nullable',
                'email:rfc,dns',
                'max:255'
            ],

            'organisation.phone1' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s]+$/'
            ],

            'organisation.phone2' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s]+$/'
            ],

            'organisation.street' => ['required', 'string', 'max:255'],
            'organisation.city' => ['required', 'string', 'max:100'],
            'organisation.country' => ['required', 'string', 'max:100'],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048' // 2MB
            ],
        ];
    }

    public function getOrganisation(){
        return Organization::first();
    }

    public function save(array $data): Organization
    {
        return DB::transaction(function () use ($data) {

            $organisation = Organization::first();
            // single-record system (settings style)

            // HANDLE LOGO
            if (isset($data['logo']) && $data['logo']!=null) {

                // delete old logo if exists
                if ($organisation && $organisation->logo) {
                    Storage::disk('public')->delete($organisation->logo);
                }

                $path = $data['logo']->store('organisation', 'public');
                //dd($path);
                $data['organisation']['logo'] = $path;
            }

            // UPDATE OR CREATE
            dd(optional($organisation)->id);
            $organisation = Organization::updateOrCreate(
                ['id' => optional($organisation)->id],
                $data['organisation']
            );

            return $organisation;
        });
    }

    public function getDefaultSaleLocations(){
        return DefaultSaleLocation::all();
    }

    public function saveDefaultSaleLocation(array $data){
        $locations = $this->getDefaultSaleLocations();
        foreach ($data as $value) {
            $exists = false;
            foreach($locations as $location){
                if($value == $location->location_id){
                    $exists = true;
                    break;
                }
            }
            if(!$exists){
                DefaultSaleLocation::create(['location_id'=>$value]);
            }
        }

        //delete removed locations
        foreach($locations as $location){
            $is_removed = true;
            foreach($data as $value){
                if($location->location_id == $value){
                    $is_removed = false;
                    break;
                }
            }
            if($is_removed){
                $location->delete();
            }
        }
    }
}

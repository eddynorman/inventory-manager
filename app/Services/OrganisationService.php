<?php

namespace App\Services;

use App\Models\DefaultSaleLocation;
use App\Models\Organization;

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
            'organisation' => ['required','array','min:6'],
            'organisation.name' => ['required','string'],
            'organisation.email' => ['required','email:rfc,dns'],
            'organisation.phone1' => ['required','string'],
            'organisation.name2' => ['nullable','string'],
            'organisation.street' => ['required','string'],
            'organisation.city' => ['required','string'],
            'organisation.country' => ['required','string'],
            'organisation.logo' => ['nullable','string'],
        ];
    }

    public function getById(int $id){
        return Organization::findOrFail($id);
    }

    public function save(array $data,?int $id = null){
        $org = Organization::updateOrCreate(
            ['id' => $id],
            [
                'name' => $data['name'],
                'phone1' => $data['phone1'],
                'phone2' =>$data['phone2'],
                'email' =>$data['email'],
                'street' =>$data['street'],
                'city' =>$data['city'],
                'country' =>$data['country'],
                'logo' =>$data['logo'],
            ]
        );

        return $org;
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

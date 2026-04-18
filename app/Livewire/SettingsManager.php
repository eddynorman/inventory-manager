<?php

namespace App\Livewire;

use App\Models\Location;
use App\Services\OrganisationService;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingsManager extends Component
{
    use WithFileUploads;
    public array $locationIds = [];
    public array $locations = [];
    public array $selectedLocations = [];
    public $selected_id = "";
    public array $organisation = [];
    public array $organisationInfo = [];
    public $logo;

    public bool $showOrganisationForm = false;

    private OrganisationService $organisationService;

    public function mount(){
        $this->organisationService = new OrganisationService();
        $this->locations = Location::all()->toArray();
        $this->loadDefaultLocations();
        $this->organisationInfo = $this->organisationService->getOrganisation()->toArray();
    }

    public function boot(OrganisationService $organisationService){
        $this->organisationService = $organisationService;
    }

    public function loadDefaultLocations(){
        $defaultLocations = $this->organisationService->getDefaultSaleLocations();
        if(count($defaultLocations) > 0){
            foreach($defaultLocations as $loc){
                $this->locationIds[] = $loc->location_id;
            }
            $this->selectedLocations = collect($this->locations)->whereIn('id',$this->locationIds)->values()->toArray();
        }
    }
    public function selectLocation($id){
        if($id != ""){
            if (!in_array($id, $this->locationIds)) {
                $this->locationIds[] = $id;
                $this->organisationService->saveDefaultSaleLocation($this->locationIds);
                session()->flash('success','Default location added!');
                $this->dispatch('flash');
            }
            $this->selectedLocations = collect($this->locations)->whereIn('id',$this->locationIds)->values()->toArray();
            $this->selected_id = "";
        }
    }

    public function removeLocation($id){
        $this->locationIds = array_values(
            array_filter($this->locationIds, fn($i) => $i != $id)
        );
        $this->organisationService->saveDefaultSaleLocation($this->locationIds);
        session()->flash('success','Default location removed!');
        $this->dispatch('flash');
        $this->selectedLocations = collect($this->locations)->whereIn('id',$this->locationIds)->values()->toArray();
    }

    public function updatedShowOrganisationForm(){
        if($this->showOrganisationForm == false){
            $this->reset('organisation');
        }
    }

    public function recordOrganisation(){
        $this->organisation['name'] = "";
        $this->organisation['email'] = "";
        $this->organisation['phone1'] = "";
        $this->organisation['phone2'] = "";
        $this->organisation['street'] = "";
        $this->organisation['city'] = "";
        $this->organisation['country'] = "Tanzania";
        $this->organisation['logo'] = null;

        $this->showOrganisationForm = true;
    }

    public function editOrganisation(){
        if(count($this->organisationInfo) >= 6){
            $this->organisation = $this->organisationInfo;
            $this->showOrganisationForm = true;
        }
    }


    public function saveOrganisation(){
        $validated = $this->validate($this->organisationService->rules());

        $organisation = $this->organisationService->save($validated);
        $this->organisationInfo = $organisation->toArray();
        $this->reset('organisation');
        $this->showOrganisationForm = false;

        session()->flash("success",'Organisation info saved successfully!');
        $this->dispatch('flash');
    }

    public function messages(){
        return [
            'organisation.name.required' => 'Organisation name is required.',
            'organisation.email.email' => 'Enter a valid email address.',
            'organisation.email.required' => 'Email address is required.',
            'organisation.phone1.required' => 'Primary phone is required.',
            'organisation.phone1.regex' => 'Invalid phone format.',
            'organisation.logo.image' => 'Logo must be an image file.',
            'organisation.street.required' => 'Street name is required.',
            'organisation.city.required' => 'City name is required.',
            'organisation.country.required' => 'Country name is required.',
        ];
    }

    public function render()
    {
        return view('livewire.settings-manager');
    }
}

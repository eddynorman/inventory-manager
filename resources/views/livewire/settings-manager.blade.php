<div>
    @include('layouts.flash')

    {{-- HEADER --}}
    <div class="mb-4">
        <h3 class="fw-bold mb-1">
            <i class="fa fa-gear text-primary"></i> System Settings
        </h3>
        <small class="text-muted">Manage organisation details and system defaults</small>
    </div>

    <div class="row g-4">

        {{-- ORGANISATION CARD --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-semibold">
                    <i class="fa fa-building text-primary"></i> Organisation Info
                </div>

                <div class="card-body">

                    @if(empty($organisationInfo))
                        <div class="text-center py-4">
                            <i class="fa fa-building text-muted" style="font-size:40px;"></i>
                            <p class="text-muted mt-2">No organisation information recorded</p>

                            <button class="btn btn-primary btn-sm"
                                wire:click='recordOrganisation'>
                                <i class="fa fa-plus"></i> Add Organisation
                            </button>
                        </div>
                    @else
                        <div class="d-flex gap-3 align-items-start">

                            {{-- LOGO --}}
                            <div>
                                <img src="{{ asset('storage/' . $organisationInfo['logo']) }}"
                                     class="rounded shadow-sm"
                                     style="width:90px; height:90px; object-fit:cover;">
                            </div>

                            {{-- DETAILS --}}
                            <div class="flex-grow-1">
                                <h5 class="mb-1">{{ $organisationInfo['name'] }}</h5>

                                <small class="text-muted d-block">
                                    <i class="fa fa-envelope"></i> {{ $organisationInfo['email'] }}
                                </small>

                                <small class="text-muted d-block">
                                    <i class="fa fa-phone"></i>
                                    {{ $organisationInfo['phone1'] }}
                                    @if($organisationInfo['phone2'])
                                        / {{ $organisationInfo['phone2'] }}
                                    @endif
                                </small>

                                <small class="text-muted d-block">
                                    <i class="fa fa-map-marker"></i>
                                    {{ $organisationInfo['street'] }},
                                    {{ $organisationInfo['city'] }},
                                    {{ $organisationInfo['country'] }}
                                </small>

                                <div class="mt-3">
                                    <button class="btn btn-outline-primary btn-sm"
                                        wire:click='editOrganisation'>
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- DEFAULT LOCATIONS --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-semibold">
                    <i class="fa fa-map-pin text-primary"></i> Default Sale Locations
                </div>

                <div class="card-body">

                    <label class="form-label fw-semibold">Select Locations</label>

                    <select class="form-select mb-3"
                        wire:model='selected_id'
                        wire:change="selectLocation($event.target.value)">
                        <option value="">Select Location...</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                        @endforeach
                    </select>

                    <div class="p-2 border rounded bg-light" style="min-height:60px;">
                        @forelse ($selectedLocations as $location)
                            <span class="badge bg-primary me-2 mb-2 px-3 py-2">
                                {{ $location['name'] }}
                                <button type="button"
                                    class="btn-close btn-close-white ms-2"
                                    style="font-size:10px;"
                                    wire:click.stop="removeLocation({{ $location['id'] }})">
                                </button>
                            </span>
                        @empty
                            <span class="text-muted small">No locations selected</span>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- MODAL --}}
    @if ($showOrganisationForm)
    <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
        style="background: rgba(0,0,0,0.6); z-index:1050;"
        wire:click="$set('showOrganisationForm', false)">

        <div class="card shadow-lg w-100 border-0"
            style="max-width: 850px; max-height: 90vh;"
            wire:click.stop>

            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="fa fa-building"></i> Organisation Setup</span>
                <button class="btn-close" wire:click="$set('showOrganisationForm', false)"></button>
            </div>

            <div class="card-body overflow-auto" style="max-height: calc(90vh - 120px);">
                <form wire:submit.prevent="saveOrganisation">
                    <div class="row g-3">

                        {{-- LOGO PREVIEW --}}
                        <div class="col-12 text-center mb-3">

                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}"
                                     class="rounded shadow-sm"
                                     style="width:100px;height:100px;object-fit:cover;">
                                <p class="small text-muted mt-1">Preview</p>
                            @elseif(!empty($organisation['logo_path']))
                                <img src="{{ asset('storage/' . $organisation['logo_path']) }}"
                                     class="rounded shadow-sm"
                                     style="width:100px;height:100px;object-fit:cover;">
                            @endif

                        </div>

                        {{-- FORM FIELDS --}}
                        <div class="col-md-6">
                            <label class="form-label">Organisation Name</label>
                            <input type="text" class="form-control"
                                   wire:model.defer="organisation.name">
                            @error('organisation.name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" class="form-control"
                                   wire:model.defer="organisation.email">
                            @error('organisation.email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Primary Phone</label>
                            <input type="text" class="form-control"
                                   wire:model.defer="organisation.phone1">
                            @error('organisation.phone1') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Secondary Phone</label>
                            <input type="text" class="form-control"
                                   wire:model.defer="organisation.phone2">
                            @error('organisation.phone2') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Street</label>
                            <input type="text" class="form-control"
                                   wire:model.defer="organisation.street">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control"
                                   wire:model.defer="organisation.city">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control"
                                   wire:model.defer="organisation.country">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Logo</label>
                            <input type="file" class="form-control"
                                   wire:model="logo">
                        </div>

                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light"
                                wire:click="$set('showOrganisationForm', false)">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

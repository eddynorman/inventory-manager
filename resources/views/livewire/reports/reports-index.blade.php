<div>

    <style>

        /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        */

        .erp-report-page{
            display:flex;
            flex-direction:column;
            gap:24px;
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .erp-report-header{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:20px;
            flex-wrap:wrap;
        }

        .erp-report-title{
            font-size:1.75rem;
            font-weight:800;
            color:#111827;
            margin-bottom:6px;
        }

        .erp-report-subtitle{
            color:#6b7280;
            font-size:0.95rem;
            max-width:700px;
        }

        /*
        |--------------------------------------------------------------------------
        | ACTIONS
        |--------------------------------------------------------------------------
        */

        .erp-report-actions{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }

        .erp-action-btn{
            border-radius:16px;
            padding:10px 18px;
            font-weight:600;
            border:none;
            transition:0.2s ease;
        }

        .erp-action-btn:hover{
            transform:translateY(-1px);
        }

        /*
        |--------------------------------------------------------------------------
        | TOP BAR
        |--------------------------------------------------------------------------
        */

        .erp-top-bar{
            background:white;
            border-radius:28px;
            padding:20px;
            box-shadow:0 2px 14px rgba(0,0,0,0.05);
        }

        /*
        |--------------------------------------------------------------------------
        | REPORT NAVIGATION
        |--------------------------------------------------------------------------
        */

        .erp-report-tabs{
            display:flex;
            gap:12px;
            overflow-x:auto;
            padding-bottom:6px;
            scrollbar-width:none;
            margin-bottom:20px;
        }

        .erp-report-tabs::-webkit-scrollbar{
            display:none;
        }

        .erp-report-tab{
            border:none;
            background:#f9fafb;
            border-radius:20px;
            padding:16px 18px;
            min-width:230px;
            flex-shrink:0;
            transition:0.22s ease;
            text-align:left;
            border:1px solid transparent;
        }

        .erp-report-tab:hover{
            background:#f3f4f6;
            transform:translateY(-2px);
        }

        .erp-report-tab.active{
            background:#111827;
            color:white;
            box-shadow:0 10px 25px rgba(17,24,39,0.18);
        }

        .erp-report-tab.active .erp-report-desc{
            color:rgba(255,255,255,0.72);
        }

        .erp-report-tab.active .erp-report-icon{
            background:rgba(255,255,255,0.12);
            color:white;
        }

        .erp-report-tab-content{
            display:flex;
            align-items:flex-start;
            gap:14px;
        }

        .erp-report-icon{
            width:52px;
            height:52px;
            border-radius:16px;
            background:white;
            color:#111827;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:20px;
            flex-shrink:0;
            transition:0.2s ease;
        }

        .erp-report-name{
            font-weight:700;
            margin-bottom:4px;
            font-size:0.95rem;
        }

        .erp-report-desc{
            font-size:0.82rem;
            color:#6b7280;
            line-height:1.45;
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER SECTION
        |--------------------------------------------------------------------------
        */

        .erp-filter-section{
            border-top:1px solid #f3f4f6;
            padding-top:20px;
        }

        .erp-filter-label{
            font-size:0.82rem;
            font-weight:700;
            color:#374151;
            margin-bottom:8px;
        }

        .erp-filter-input{
            min-height:52px;
            border-radius:16px !important;
            border:1px solid #e5e7eb;
            box-shadow:none !important;
            font-size:0.94rem;
        }

        .erp-filter-input:focus{
            border-color:#111827;
        }
        /*
        |--------------------------------------------------------------------------
        | MODERN FILTER PANEL
        |--------------------------------------------------------------------------
        */

        .erp-filter-panel{
            background:#f9fafb;
            border:1px solid #eef2f7;
            border-radius:24px;
            padding:22px;
        }

        .erp-filter-top{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:16px;
            margin-bottom:20px;
            flex-wrap:wrap;
        }

        .erp-filter-title{
            font-size:1rem;
            font-weight:800;
            color:#111827;
            display:flex;
            align-items:center;
            gap:10px;
        }

        .erp-filter-subtitle{
            color:#6b7280;
            font-size:0.85rem;
            margin-top:4px;
        }

        .erp-filter-grid{
            display:grid;
            grid-template-columns:
                repeat(auto-fit,minmax(240px,1fr));
            gap:18px;
        }

        .erp-filter-card{
            background:white;
            border:1px solid #edf1f5;
            border-radius:20px;
            padding:16px;
            transition:0.2s ease;
        }

        .erp-filter-card:hover{
            border-color:#dbe3ec;
            transform:translateY(-1px);
        }

        .erp-filter-label{
            display:flex;
            align-items:center;
            gap:8px;
            font-size:0.82rem;
            font-weight:700;
            color:#374151;
            margin-bottom:10px;
        }

        .erp-filter-input{
            min-height:52px;
            border-radius:16px !important;
            border:1px solid #e5e7eb !important;
            box-shadow:none !important;
            font-size:0.94rem;
            padding-left:14px;
        }

        .erp-filter-input:focus{
            border-color:#111827 !important;
        }

        .erp-filter-select{
            min-height:52px;
            border-radius:16px !important;
            border:1px solid #e5e7eb !important;
            box-shadow:none !important;
            font-size:0.94rem;
            padding-left:14px;
        }

        .erp-filter-select:focus{
            border-color:#111827 !important;
        }

        .erp-filter-actions{
            display:flex;
            justify-content:flex-end;
            align-items:center;
            gap:12px;
            margin-top:22px;
            flex-wrap:wrap;
        }

        .erp-generate-btn{
            min-height:54px;
            border-radius:18px;
            font-weight:700;
            padding-inline:24px;
            border:none;
            box-shadow:0 8px 22px rgba(17,24,39,0.12);
        }

        .erp-filter-badge{
            background:white;
            border:1px solid #e5e7eb;
            border-radius:999px;
            padding:8px 14px;
            font-size:0.78rem;
            color:#6b7280;
            font-weight:600;
        }

        @media(max-width:768px){

            .erp-filter-panel{
                padding:16px;
            }

            .erp-filter-grid{
                grid-template-columns:1fr;
            }

            .erp-filter-actions{
                flex-direction:column;
                align-items:stretch;
            }

        }

        /*
        |--------------------------------------------------------------------------
        | REPORT INFO STRIP
        |--------------------------------------------------------------------------
        */

        .erp-report-strip{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:16px;
        }

        .erp-strip-card{
            background:white;
            border-radius:22px;
            padding:18px 20px;
            box-shadow:0 2px 10px rgba(0,0,0,0.04);
        }

        .erp-strip-label{
            color:#6b7280;
            font-size:0.78rem;
            margin-bottom:6px;
        }

        .erp-strip-value{
            font-weight:700;
            color:#111827;
        }

        /*
        |--------------------------------------------------------------------------
        | LOADING
        |--------------------------------------------------------------------------
        */

        .erp-loading-card{
            background:white;
            border-radius:28px;
            padding:70px 30px;
            text-align:center;
            box-shadow:0 2px 12px rgba(0,0,0,0.05);
        }

        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media(max-width:768px){

            .erp-report-title{
                font-size:1.45rem;
            }

            .erp-report-tab{
                min-width:210px;
            }

            .erp-top-bar{
                padding:16px;
            }

        }

    </style>

    <div class="erp-report-page">

        {{-- HEADER --}}
        <div class="erp-report-header">

            <div>

                <div class="erp-report-title">
                    Reports & Analytics
                </div>

                <div class="erp-report-subtitle">
                    Operational, inventory and financial intelligence reports
                    for ERP analysis and decision making.
                </div>

            </div>

            {{-- ACTIONS --}}
            <div class="erp-report-actions">

                <button class="btn btn-success erp-action-btn">

                    <i class="bi bi-file-earmark-excel me-2"></i>

                    Excel

                </button>

                <button class="btn btn-danger erp-action-btn">

                    <i class="bi bi-file-earmark-pdf me-2"></i>

                    PDF

                </button>

                <button class="btn btn-dark erp-action-btn">

                    <i class="bi bi-printer me-2"></i>

                    Print

                </button>

            </div>

        </div>

        {{-- TOP BAR --}}
        @if($showReportSelector)

            <div class="erp-top-bar">

                <div class="d-flex align-items-center justify-content-between mb-4">

                    <div>

                        <div class="erp-report-title mb-1">
                            Available Reports
                        </div>

                        <div class="erp-report-subtitle">
                            Select a report category and generate analytics
                        </div>

                    </div>

                </div>

                <div class="d-flex flex-column gap-4">

                    @foreach($reportGroups as $key => $group)
                        @if(auth()->user()->canAccess($group_permissions[$key]))
                            <div>

                                <div class="d-flex align-items-center gap-3 mb-3">

                                    <div class="erp-report-icon">

                                        <i class="{{ $group['icon'] }}"></i>

                                    </div>

                                    <div>

                                        <div class="fw-bold">

                                            {{ $group['title'] }}

                                        </div>

                                    </div>

                                </div>

                                <div class="row g-3">

                                    @foreach($group['reports'] as $key => $report)
                                        <div class="col-xl-3 col-lg-4 col-md-6">

                                            <button
                                                wire:click="changeReport('{{ $key }}','{{ $report['title'] }}','{{ $report['description'] }}')"
                                                class="erp-report-tab w-100 h-100">

                                                <div class="erp-report-tab-content">

                                                    <div class="erp-report-icon">

                                                        <i class="{{ $report['icon'] }}"></i>

                                                    </div>

                                                    <div>

                                                        <div class="erp-report-name">

                                                            {{ $report['title'] }}

                                                        </div>

                                                        <div class="erp-report-desc">

                                                            {{ $report['description'] }}

                                                        </div>

                                                    </div>

                                                </div>

                                            </button>

                                        </div>

                                    @endforeach

                                </div>

                            </div>
                        @endif

                    @endforeach

                </div>

            </div>

        @else

            <div class="erp-top-bar">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">

                    <div>

                        <button
                            wire:click="backToReports"
                            class="btn btn-light mb-3">

                            <i class="bi bi-arrow-left me-2"></i>

                            Back To Reports

                        </button>

                        <div class="erp-report-title">

                            {{ $activeReportName }}

                        </div>

                        <div class="erp-report-subtitle">

                            {{ $activeReportDescription}}

                        </div>

                    </div>

                </div>

                {{-- FILTERS --}}
                <div class="erp-filter-section">

                    @php

                        $usesDepartment =
                            in_array($activeReport, [
                                'sold_items',
                                'sold_kits',
                                'used_items',
                                'stock_movement',
                                'stock_valuation',
                                'general_sales',
                            ]);

                        $usesLocation =
                            in_array($activeReport, [
                                'stock_movement',
                                'stock_valuation',
                            ]);

                    @endphp

                    <div class="erp-filter-panel">

                        {{-- TOP --}}
                        <div class="erp-filter-top">

                            <div>

                                <div class="erp-filter-title">

                                    <i class="bi bi-funnel"></i>

                                    Report Filters

                                </div>

                                <div class="erp-filter-subtitle">

                                    Customize report scope, date range and operational filters

                                </div>

                            </div>

                            <div class="erp-filter-badge">

                                <i class="bi bi-clock-history me-1"></i>

                                {{ now()->format('d M Y H:i') }}

                            </div>

                        </div>

                        {{-- FILTER GRID --}}
                        <div class="erp-filter-grid">

                            {{-- FROM DATE --}}
                            <div class="erp-filter-card">

                                <label class="erp-filter-label">

                                    <i class="bi bi-calendar-event"></i>

                                    From Date & Time

                                </label>

                                <input
                                    type="datetime-local"
                                    max="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="form-control erp-filter-input @error('fromDate') is-invalid @enderror"
                                    wire:model="fromDate">

                                @error('fromDate')

                                    <small class="text-danger mt-2 d-block">
                                        {{ $message }}
                                    </small>

                                @enderror

                            </div>

                            {{-- TO DATE --}}
                            <div class="erp-filter-card">

                                <label class="erp-filter-label">

                                    <i class="bi bi-calendar2-check"></i>

                                    To Date & Time

                                </label>

                                <input
                                    type="datetime-local"
                                    max="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="form-control erp-filter-input @error('toDate') is-invalid @enderror"
                                    wire:model="toDate">

                                @error('toDate')

                                    <small class="text-danger mt-2 d-block">
                                        {{ $message }}
                                    </small>

                                @enderror

                            </div>

                            {{-- DEPARTMENT --}}
                            @if($usesDepartment)

                                <div class="erp-filter-card">

                                    <label class="erp-filter-label">

                                        <i class="bi bi-diagram-3"></i>

                                        Department

                                    </label>

                                    <select
                                        wire:model="selectedDepartment"
                                        class="form-select erp-filter-select">

                                        <option value="">
                                            All Departments
                                        </option>

                                        @foreach($departments as $department)

                                            <option value="{{ $department->id }}">

                                                {{ $department->name }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>

                            @endif

                            {{-- LOCATION --}}
                            @if($usesLocation)

                                <div class="erp-filter-card">

                                    <label class="erp-filter-label">

                                        <i class="bi bi-geo-alt"></i>

                                        Location

                                    </label>

                                    <select
                                        wire:model="selectedLocation"
                                        class="form-select erp-filter-select">

                                        <option value="">
                                            All Locations
                                        </option>

                                        @foreach($locations as $location)

                                            <option value="{{ $location->id }}">

                                                {{ $location->name }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>

                            @endif

                        </div>

                        {{-- ACTIONS --}}
                        <div class="erp-filter-actions">

                            <button
                                wire:click="loadReport"
                                class="btn btn-dark erp-generate-btn">

                                <i class="bi bi-bar-chart-line me-2"></i>

                                Generate Report

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        @endif

        {{-- REPORT INFO STRIP --}}
        @if(!$showReportSelector)
            <div class="erp-report-strip">

                <div class="erp-strip-card">

                    <div class="erp-strip-label">
                        Active Report
                    </div>

                    <div class="erp-strip-value">

                        {{ $activeReportName }}

                    </div>

                </div>

                <div class="erp-strip-card">

                    <div class="erp-strip-label">
                        Report Period
                    </div>

                    <div class="erp-strip-value">

                        {{ \Carbon\Carbon::parse($fromDate)->format('d M Y H:i') }}

                    </div>

                </div>

                <div class="erp-strip-card">

                    <div class="erp-strip-label">
                        To
                    </div>

                    <div class="erp-strip-value">

                        {{ \Carbon\Carbon::parse($toDate)->format('d M Y H:i') }}

                    </div>

                </div>

                <div class="erp-strip-card">

                    <div class="erp-strip-label">
                        Generated
                    </div>

                    <div class="erp-strip-value">

                        {{ now()->format('d M Y H:i') }}

                    </div>

                </div>

            </div>
        @endif
        {{-- REPORT CONTENT --}}
        <div wire:loading.remove>

            @if($activeReport == 'general_sales')

                @include('livewire.reports.partials.general-sales')

            @elseif($activeReport == 'sold_items')

                @include('livewire.reports.partials.sold-items')

            @elseif($activeReport == 'sold_kits')

                @include('livewire.reports.partials.sold-kits')

            @elseif($activeReport == 'used_items')

                @include('livewire.reports.partials.used-items')

            @elseif($activeReport == 'individual_sales')

                @include('livewire.reports.partials.individual-sales')

            @elseif($activeReport == 'stock_movement')

                @include('livewire.reports.partials.stock-movement')

            @elseif($activeReport == 'stock_valuation')

                @include('livewire.reports.partials.stock-valuation')

            @elseif($activeReport == 'low_stock')

                @include('livewire.reports.partials.low-stock')

            @elseif($activeReport == 'negative_stock')

                @include('livewire.reports.partials.negative-stock')

            @else

                @include('livewire.reports.partials.empty-report')

            @endif

        </div>

        {{-- LOADING --}}
        <div wire:loading>

            <div class="erp-loading-card">

                <div class="spinner-border text-dark mb-4"></div>

                <div class="fw-bold mb-2">
                    Generating Report
                </div>

                <div class="text-muted">
                    Processing ERP operational and financial data...
                </div>

            </div>

        </div>

    </div>

</div>

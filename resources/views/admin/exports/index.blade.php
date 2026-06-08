@extends('layouts.admin')
@section('admin-content')
<div class="container-fluid px-4 py-4">
    <x-page-header title="Export Data" subtitle="Select data types and columns to export as CSV">
        <x-slot:actions>
            <button type="submit" form="exportForm" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i> Export Selected</button>
        </x-slot:actions>
    </x-page-header>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <form id="exportForm" method="POST" action="{{ route('admin.exports.export') }}">
        @csrf
        <div class="row g-4">
            @php
                $sections = [
                    'students' => ['label' => 'Students', 'cols' => ['ID','First Name','Last Name','Full Name','Email','Phone','Gender','DOB','Nationality','Passport Number','Passport Expiry','Marital Status','Qualification','Passed Year','Last Grades','Education Board','Gap (years)','Preferred Country','Preferred City','Preferred Course','Preferred University','Agent','Agent Email','Expected Revenue','Received Revenue','Created Date']],
                    'applications' => ['label' => 'Applications', 'cols' => ['ID','Application #','Student ID','Student Name','Student Email','Student Phone','University','University Country','University City','Course','Course Duration','Course Fee','Status','Agent','Agent Email','Created Date','Updated Date']],
                    'universities' => ['label' => 'Universities', 'cols' => ['ID','Name','Short Name','Country','City','Website','Email','Phone','Description','Course Count','Created Date']],
                    'courses' => ['label' => 'Courses', 'cols' => ['ID','Title','University','Level','Duration','Fee','Currency','Intake','Category','Type','Description','Created Date']],
                    'users' => ['label' => 'Users', 'cols' => ['ID','Name','Email','Role','Business Name','Phone','Status','Agreement Status','Student Count','Application Count','Created Date']],
                    'agents' => ['label' => 'Agents', 'cols' => ['ID','Name','Email','Business Name','Phone','Status','Agreement Status','Student Count','Application Count','Created Date']],
                ];
            @endphp
            @foreach($sections as $key => $sec)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check me-3">
                                <input class="form-check-input section-checkbox" type="checkbox" id="select{{ ucfirst($key) }}" data-section="{{ $key }}">
                                <label class="form-check-label fw-bold" for="select{{ ucfirst($key) }}">{{ $sec['label'] }}</label>
                            </div>
                        </div>
                        <div class="ms-2" id="{{ $key }}Columns" style="max-height:200px;overflow-y:auto;padding:4px;">
                            @foreach($sec['cols'] as $col)
                            <div class="form-check form-check-inline mb-1" style="width:48%;">
                                <input class="form-check-input column-checkbox" type="checkbox" name="columns[{{ $key }}][]" value="{{ $col }}" id="{{ $key }}_{{ Str::slug($col) }}" disabled>
                                <label class="form-check-label small" for="{{ $key }}_{{ Str::slug($col) }}">{{ $col }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 text-center">
            <button type="submit" class="btn btn-primary px-5 py-2"><i class="fas fa-download me-2"></i> Export Selected</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.querySelectorAll('.section-checkbox').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var section = this.dataset.section;
        var cols = document.querySelectorAll('#' + section + 'Columns .column-checkbox');
        cols.forEach(function(col) {
            col.disabled = !cb.checked;
            if (!cb.checked) col.checked = false;
        });
    });
});
</script>
@endpush
@endsection

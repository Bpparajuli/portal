{{-- Validation Errors --}}
@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Success Message --}}
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Error Message --}}

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

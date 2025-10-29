{{-- Validation Errors --}}
@if($errors->any())
<div class="alert alert-danger mb-0">
    <ul class="p-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Success Message --}}
@if(session('success'))
<div class="alert alert-success mb-0 ">{{ session('success') }}</div>
@endif

{{-- Error Message --}}

@if(session('error'))
<div class="alert alert-danger mb-0">{{ session('error') }}</div>
@endif

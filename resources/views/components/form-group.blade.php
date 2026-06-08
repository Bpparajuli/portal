@props(['name', 'label' => null, 'help' => null, 'required' => false, 'error' => null, 'class' => ''])
<div class="mb-3 {{ $class }}">
    @if($label)
    <label for="{{ $name }}" class="form-label fw-medium">
        {{ $label }}
        @if($required)<span class="text-danger">*</span>@endif
    </label>
    @endif
    {{ $slot }}
    @if($help)
    <div class="form-text">{{ $help }}</div>
    @endif
    @if($error)
    <div class="invalid-feedback d-block">{{ $error }}</div>
    @endif
</div>

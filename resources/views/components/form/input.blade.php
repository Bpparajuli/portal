@props(['name','label','type'=>'text','value'=>''])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}" {{ $attributes->merge(['class' => 'form-control']) }}>
    @error($name)
    <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

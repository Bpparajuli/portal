@props(['name','label','value'=>''])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <textarea name="{{ $name }}" id="{{ $name }}" {{ $attributes->merge(['class' => 'form-control']) }}>{{ old($name, $value) }}</textarea>
    @error($name)
    <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

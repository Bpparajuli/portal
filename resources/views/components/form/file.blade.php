@props(['name','label','required'=>false])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="file" name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => 'form-control']) }}>
    @error($name)
    <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

@props(['name','label','options'=>[],'required'=>false,'id'=>null])

<div class="mb-3">
    <label for="{{ $id ?? $name }}" class="form-label">{{ $label }}</label>
    <select name="{{ $name }}" id="{{ $id ?? $name }}" {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => 'form-select']) }}>
        {{ $slot }}
        @if(!empty($options))
        @foreach($options as $value => $display)
        <option value="{{ $value }}" {{ old($name)==$value ? 'selected' : '' }}>{{ $display }}</option>
        @endforeach
        @endif
    </select>
    @error($name)
    <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

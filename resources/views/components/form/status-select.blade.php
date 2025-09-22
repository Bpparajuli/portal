@props(['name','options'=>[],'selected'=>null])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">Application Status</label>
    <select name="{{ $name }}" id="{{ $name }}" {{ $attributes->merge(['class' => 'form-select']) }}>
        @foreach($options as $status)
        <option value="{{ $status }}" {{ old($name,$selected)==$status?'selected':'' }}>
            {{ $status }}
        </option>
        @endforeach
    </select>
    @error($name)
    <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

@props(['name','label','required'=>false,'value'=>null])

<div class="mb-3 file-upload-group">
    <label for="{{ $name }}" class="form-label fw-semibold">{{ $label }}</label>
    {{-- Existing file preview (if any) --}}
    @if($value)
    @php
    $extension = pathinfo($value, PATHINFO_EXTENSION);
    @endphp
    <div class="file-preview border rounded p-2 mb-2 d-flex align-items-center gap-3 bg-light">
        <a href="#" class="preview-link" data-preview="{{ asset('storage/' . $value) }}">
            @if(in_array(strtolower($extension), ['jpg','jpeg','png','gif']))
            <img src="{{ asset('storage/' . $value) }}" style="width:70px; height:70px; object-fit:cover; border-radius:6px;">
            @elseif(strtolower($extension) === 'pdf')
            <i class="bi bi-file-earmark-pdf text-danger fs-2"></i>
            @else
            <i class="bi bi-file-earmark fs-2 text-secondary"></i>
            @endif
        </a>
        <div>
            <p class="mb-1"><strong>{{ basename($value) }}</strong></p>
            <small class="text-muted">{{ strtoupper($extension) }} file</small>
        </div>
    </div>
    @endif

    {{-- Input --}}
    <input type="file" name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => 'form-control file-input']) }} accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">

    {{-- Live preview for new file --}}
    <div class="live-preview mt-2"></div>
    @error($name)
    <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

@props(['action', 'id' => null, 'label' => 'Delete', 'message' => 'Are you sure? This action cannot be undone.'])
<button type="button" class="btn btn-danger btn-sm btn-delete-confirm"
    data-url="{{ $id ? route($action, $id) : route($action) }}"
    data-message="{{ $message }}">
    <i class="fas fa-trash-alt me-1"></i> {{ $label }}
</button>

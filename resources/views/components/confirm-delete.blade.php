@props([
    'action' => null,
    'id' => null,
    'url' => null,
    'label' => 'Delete',
    'icon' => true,
    'title' => 'Are you sure?',
    'message' => 'This action cannot be undone.',
    'method' => 'DELETE',
    'class' => 'btn btn-danger btn-sm',
    'mode' => 'swal',
    'formId' => null,
    'reload' => true,
    'confirmText' => 'Yes, delete it!',
    'cancelText' => 'Cancel',
])

@php
    $deleteUrl = $url ?? ($id ? route($action, $id) : route($action));
@endphp

@if ($mode === 'form')
    <form action="{{ $deleteUrl }}" method="POST" class="d-inline">
        @csrf
        @method($method)
        <button type="submit" class="{{ $class }}" title="{{ $label }}">
            @if($icon)<i class="fas fa-trash-alt me-1"></i>@endif{{ $label }}
        </button>
    </form>
@elseif ($mode === 'native')
    <form action="{{ $deleteUrl }}" method="POST" class="d-inline"
        onsubmit="return confirm('{{ $message }}')">
        @csrf
        @method($method)
        <button type="submit" class="{{ $class }}" title="{{ $label }}">
            @if($icon)<i class="fas fa-trash-alt me-1"></i>@endif{{ $label }}
        </button>
    </form>
@else
    <button type="button"
        class="btn-delete-confirm {{ $class }}"
        data-url="{{ $deleteUrl }}"
        data-method="{{ $method }}"
        data-title="{{ $title }}"
        data-message="{{ $message }}"
        data-confirm-text="{{ $confirmText }}"
        data-cancel-text="{{ $cancelText }}"
        data-reload="{{ $reload ? '1' : '0' }}"
        data-form-id="{{ $formId ?? '' }}"
        data-csrf="{{ csrf_token() }}"
        title="{{ $label }}">
        @if($icon)<i class="fas fa-trash-alt me-1"></i>@endif{{ $label }}
    </button>
@endif

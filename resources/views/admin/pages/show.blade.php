@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header :title="$page->title" subtitle="View page details">
        <x-slot:actions>
            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Page
            </a>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Pages
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if($page->featured_image)
                    <img src="{{ asset('storage/' . $page->featured_image) }}" class="img-fluid rounded mb-3" alt="{{ $page->title }}">
                    @endif
                    <div class="page-content">
                        {!! $page->content !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th class="ps-0">Status</th>
                            <td class="pe-0">
                                @if($page->is_published)
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-0">Slug</th>
                            <td class="pe-0"><code>/{{ $page->slug }}</code></td>
                        </tr>
                        <tr>
                            <th class="ps-0">Template</th>
                            <td class="pe-0">{{ $page->template ?? 'Default' }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Menu Item</th>
                            <td class="pe-0">{{ $page->is_menu_item ? 'Yes' : 'No' }}</td>
                        </tr>
                        @if($page->meta_title)
                        <tr>
                            <th class="ps-0">Meta Title</th>
                            <td class="pe-0 small">{{ $page->meta_title }}</td>
                        </tr>
                        @endif
                        @if($page->meta_description)
                        <tr>
                            <th class="ps-0">Meta Description</th>
                            <td class="pe-0 small text-muted">{{ $page->meta_description }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th class="ps-0">Created</th>
                            <td class="pe-0 small">{{ $page->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Updated</th>
                            <td class="pe-0 small">{{ $page->updated_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .page-content h1 { font-size: 1.75rem; margin-top: 1.5rem; }
    .page-content h2 { font-size: 1.5rem; margin-top: 1.25rem; }
    .page-content h3 { font-size: 1.25rem; margin-top: 1rem; }
    .page-content p { margin-bottom: 1rem; line-height: 1.7; }
    .page-content img { max-width: 100%; border-radius: 8px; margin: 1rem 0; }
</style>
@endpush
@endsection

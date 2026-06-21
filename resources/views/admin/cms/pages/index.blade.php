@extends('layouts.admin')
@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header title="Manage Pages">
        <x-slot:actions>
            <a href="{{ route('admin.pages.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Page</a>
        </x-slot:actions>
    </x-page-header>

    @if($pages->count())
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th style="width:130px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pages as $page)
                    <tr>
                        <td class="fw-semibold">{{ $page->title }}</td>
                        <td>
                            <code class="small">{{ $page->slug }}</code>
                            @if($page->is_published)
                            <a href="{{ url($page->slug) }}" target="_blank" class="text-decoration-none small ms-1" title="View page"><i class="fas fa-external-link-alt"></i></a>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColors = ['published' => 'success', 'draft' => 'warning', 'archived' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$page->status ?? 'draft'] }} bg-opacity-10 text-{{ $statusColors[$page->status ?? 'draft'] }} rounded-pill">
                                {{ $page->status ?? 'draft' }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $page->updated_at->format('M d, Y') }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewPage({{ $page->id }})" title="Preview"><i class="fas fa-eye"></i></button>
                                <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <x-confirm-delete
                                    action="admin.pages.destroy"
                                    :id="$page->id"
                                    label=""
                                    title="Delete Page?"
                                    message="This will permanently delete this page."
                                    mode="form"
                                    class="btn btn-sm btn-outline-danger"
                                />
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($pages->hasPages())
        <div class="card-footer bg-white">{{ $pages->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
    @else
    <x-empty-state icon="fa-file" title="No pages yet" description="Create your first page to get started." actionLabel="Create Page" actionUrl="{{ route('admin.pages.create') }}" />
    @endif
</div>

{{-- View Modal --}}
<div class="modal fade" id="pageViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Page Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const pages = @json($pages->items());
function viewPage(id) {
    const page = pages.find(p => p.id === id);
    if (!page) return;
    document.getElementById('modalTitle').textContent = page.title;
    document.getElementById('modalBody').innerHTML = `
        <div class="mb-3"><strong>Slug:</strong> <code>${page.slug}</code></div>
        <div class="mb-3"><strong>Status:</strong> <span class="badge bg-${page.status === 'published' ? 'success' : (page.status === 'draft' ? 'warning' : 'secondary')}">${page.status || 'draft'}</span></div>
        <hr>
        <div>${page.content || '<em>No content</em>'}</div>
        ${page.meta_title ? `<hr><div class="small text-muted"><strong>Meta:</strong> ${page.meta_title}${page.meta_description ? ' — ' + page.meta_description : ''}</div>` : ''}
    `;
    new bootstrap.Modal(document.getElementById('pageViewModal')).show();
}
</script>
@endpush
@endsection
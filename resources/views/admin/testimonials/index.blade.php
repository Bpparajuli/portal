@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header title="Manage Testimonials" subtitle="Create and manage customer testimonials">
        <x-slot:actions>
            <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create New
            </a>
        </x-slot:actions>
    </x-page-header>

    @if($testimonials->count())
        <x-table-responsive id="testimonialsTable" searchable="true">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Content</th>
                    <th>Rating</th>
                    <th>Active</th>
                    <th>Order</th>
                    <th style="width:140px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($testimonials as $testimonial)
                <tr>
                    <td class="fw-semibold">{{ $testimonial->name }}</td>
                    <td>{{ $testimonial->location }}</td>
                    <td class="text-muted small">{{ Str::limit($testimonial->content, 80) }}</td>
                    <td>
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $testimonial->rating)
                                <i class="fas fa-star text-warning"></i>
                            @else
                                <i class="far fa-star text-muted"></i>
                            @endif
                        @endfor
                    </td>
                    <td>
                        <x-badge type="{{ $testimonial->is_active ? 'success' : 'secondary' }}" text="{{ $testimonial->is_active ? 'Active' : 'Inactive' }}" />
                    </td>
                    <td>{{ $testimonial->sort_order }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <x-confirm-delete action="admin.testimonials.destroy" :id="$testimonial->id" />
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </x-table-responsive>
    @else
        <x-empty-state
            icon="fa-quote-right"
            title="No testimonials yet"
            description="Create your first testimonial to get started."
            actionLabel="Create Testimonial"
            actionUrl="{{ route('admin.testimonials.create') }}"
        />
    @endif
</div>
@endsection

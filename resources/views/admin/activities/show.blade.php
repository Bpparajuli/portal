@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header title="Activity Detail" subtitle="View activity information">
        <x-slot:actions>
            <a href="{{ route('admin.activities.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Log
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th class="bg-light" style="width:180px">Type</th>
                            <td><span class="badge bg-info bg-opacity-10 text-info text-capitalize">{{ str_replace('_', ' ', $activity->type) }}</span></td>
                        </tr>
                        <tr>
                            <th class="bg-light">User</th>
                            <td>{{ $activity->user?->name ?? 'System' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Description</th>
                            <td>{{ $activity->description }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Date/Time</th>
                            <td>{{ $activity->created_at->format('F d, Y h:i A') }}</td>
                        </tr>
                        @if($activity->student)
                        <tr>
                            <th class="bg-light">Related Student</th>
                            <td>
                                <a href="{{ route('admin.students.show', $activity->student) }}">{{ $activity->student->full_name }}</a>
                            </td>
                        </tr>
                        @endif
                        @if($activity->application)
                        <tr>
                            <th class="bg-light">Related Application</th>
                            <td>
                                <a href="{{ route('admin.applications.show', $activity->application) }}">{{ $activity->application->application_number ?? '#' . $activity->application->id }}</a>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

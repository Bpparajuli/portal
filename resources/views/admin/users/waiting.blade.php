@extends('layouts.app')

@section('content')
<div class="p-3">

    @if(auth()->check() && auth()->user()->is_admin)

    {{-- ============================
         USERS WAITING APPROVAL
    ============================= --}}
    <h2 class="text-center mb-4">Users Waiting for Approval</h2>

    @if($waitingUsers->isEmpty())
    <div class="alert alert-info text-center">No users waiting for approval.</div>
    @else
    <div class="table-responsive mb-5">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Business Name</th>
                    <th>Owner</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Approve</th>
                    <th>Applied at</th>
                </tr>
            </thead>
            <tbody>
                @foreach($waitingUsers as $user)
                <tr>
                    <td>{{ $user->id }}</td>

                    <td>
                        <a href="{{ route('admin.users.show', $user->business_name) }}">
                            {{ $user->business_name }}
                        </a>
                    </td>

                    <td>{{ $user->owner_name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->contact }}</td>
                    <td>{{ $user->address }}</td>

                    <td>
                        <form action="{{ route('admin.users.approve', $user->business_name) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button class="btn btn-success btn-sm">Approve</button>
                        </form>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif



    {{-- ============================================
         USERS WAITING AGREEMENT VERIFICATION
    ============================================= --}}
    <h2 class="text-center mb-4">Users Waiting for Agreement Verification</h2>

    @if($agreementUsers->isEmpty())
    <div class="alert alert-info text-center">No agreements waiting for verification.</div>
    @else
    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Business Name</th>
                    <th>Agreement File</th>
                    <th>Status</th>
                    <th>Verify</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agreementUsers as $user)
                <tr>
                    <td>{{ $user->id }}</td>

                    <td>
                        <a href="{{ route('admin.users.show', $user->business_name) }}">
                            {{ $user->business_name }}
                        </a>
                    </td>

                    <td>
                        @if($user->agreement_file)
                        <a href="{{ asset('storage/'.$user->agreement_file) }}" target="_blank">View File</a>
                        @else
                        <span class="text-muted">No File</span>
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('admin.users.edit', $user->business_name_slug) }}" class="text-decoration-none">
                            @if ($user->agreement_status === 'not_uploaded')
                            <span class="badge bg-secondary">Not Uploaded</span>
                            @elseif ($user->agreement_status === 'uploaded')
                            <span class="badge bg-warning text-dark">Uploaded</span>
                            @elseif ($user->agreement_status === 'verified')
                            <span class="badge bg-success">Verified</span>
                            @else
                            <span class="badge bg-danger">Unknown</span>
                            @endif
                        </a>
                    </td>

                    <td>
                        <form action="{{ route('admin.users.verifyAgreement', $user->business_name) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button class="btn btn-primary btn-sm">Verify</button>
                        </form>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif


    @else
    <div class="alert alert-danger text-center mt-5">
        You are not authorized to view this page.
    </div>
    @endif

</div>
@endsection

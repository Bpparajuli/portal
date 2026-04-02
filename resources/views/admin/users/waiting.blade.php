@extends('layouts.app')

@section('content')
<div class="p-3">

    @if(auth()->check() && auth()->user()->is_admin)

    {{-- ==========================
        USERS WAITING APPROVAL
    =========================== --}}
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
                </tr>
            </thead>
            <tbody>
                @foreach($waitingUsers as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td><a href="{{ route('admin.users.show', $user->slug) }}">{{ $user->business_name }}</a></td>
                    <td><a href="{{ route('admin.users.show', $user->slug) }}">{{ $user->owner_name }}</a></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->contact }}</td>
                    <td>{{ $user->address }}</td>
                    <td>
                        {{-- APPROVE FORM: Still a standard, separate form --}}
                        <form action="{{ route('admin.users.approve', $user->slug) }}" method="POST">
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

    <hr>

    {{-- ==========================
        USERS WAITING AGREEMENT REMINDER
        
        CRITICAL CHANGE: Wrap the whole section in the reminder form.
        We will use JavaScript to submit the 'Verify' actions separately.
    =========================== --}}
    <h2 class="text-center mb-4">Users Waiting for Agreement Verification</h2>

    @if($agreementUsers->isEmpty())
    <div class="alert alert-info text-center">No agreements waiting for verification.</div>
    @else

    {{-- THE REMINDER FORM: Wraps the table and submission buttons --}}
    <form id="reminderForm" action="{{ route('admin.reminder.send') }}" method="POST" onsubmit="return confirmReminder(this, event)">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>ID</th>
                        <th>Business Name</th>
                        <th class="text-center">Agreement File</th>
                        <th class="text-end">Status</th>
                        <th>Verify</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agreementUsers as $user)
                    <tr>
                        <td>
                            {{-- Checkbox uses standard structure --}}
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" data-name="{{ $user->business_name }}">
                        </td>
                        <td>{{ $user->id }}</td>
                        <td> <a href="{{ route('admin.users.show', $user->slug) }}">{{ $user->business_name }}</a></td>
                        <td class="text-center">
                            @if($user->agreement_file)
                            <a href="{{ asset('storage/'.$user->agreement_file) }}" target="_blank">View File</a>
                            @else
                            <span class="text-muted">No File</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($user->agreement_status === 'not_uploaded')
                            <span class="badge bg-secondary">Not Uploaded</span>
                            @elseif ($user->agreement_status === 'uploaded')
                            <span class="badge bg-warning text-dark">Uploaded</span>
                            @elseif ($user->agreement_status === 'verified')
                            <span class="badge bg-success">Verified</span>
                            @endif
                        </td>
                        <td>
                            {{-- VERIFY BUTTON: Changed to a button with data attributes, NOT a form --}}
                            <button type="button" class="btn btn-primary btn-sm verify-btn" data-slug="{{ $user->slug }}" data-url="{{ route('admin.users.verifyAgreement', $user->slug) }}">
                                Verify
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary mt-2" name="send_all" value="0">Send Reminder to Selected Users</button>
        <button type="submit" class="btn btn-secondary mt-2" name="send_all" value="1">Send Reminder to All Users</button>
    </form>
    @endif

    @else
    <div class="alert alert-danger text-center mt-5">
        You are not authorized to view this page.
    </div>
    @endif

</div>

{{-- Hidden Form for PUT/Verify Actions (Used by JavaScript) --}}
<form id="verifyForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
</form>

{{-- =============================
    JS for select all & confirm
============================= --}}
<script>
    // Select all checkboxes
    document.getElementById('selectAll').addEventListener('click', function() {
        // Targets all checkboxes named user_ids[] within the reminderForm
        document.querySelectorAll('#reminderForm input[name="user_ids[]"]').forEach(el => el.checked = this.checked);
    });

    // Confirmation before sending reminder emails
    function confirmReminder(form, event) {
        // Find which button was clicked
        const sendAll = event.submitter && event.submitter.name === 'send_all' && event.submitter.value === '1';

        if (sendAll) {
            return confirm('⚠️ Are you sure you want to send reminders to ALL users?');
        }

        // Get selected users from the form itself
        const selected = Array.from(form.querySelectorAll('input[name="user_ids[]"]:checked'))
            .map(cb => cb.dataset.name);

        if (selected.length === 0) {
            alert('Please select at least one user to send a reminder.');
            return false; // **This returns false if no users are selected**
        }

        return confirm('Send reminder emails to the following users:\n\n' + selected.join('\n'));
    }

    // --- NEW JAVASCRIPT FOR VERIFY BUTTONS ---
    const verifyForm = document.getElementById('verifyForm');

    // Attach click listener to all 'Verify' buttons
    document.querySelectorAll('.verify-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to verify this user\'s agreement?')) {
                // 1. Get the target URL from the button's data attribute
                const url = this.dataset.url;

                // 2. Set the hidden form's action URL
                verifyForm.action = url;

                // 3. Submit the hidden form (which performs the PUT request)
                verifyForm.submit();
            }
        });
    });

</script>
@endsection

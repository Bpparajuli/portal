@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><strong>Agreement Status</strong></h5>
                </div>

                <div class="card-body">

                    {{-- Status Messages --}}
                    @if($user->agreement_status === 'not_uploaded')
                    <div class="alert alert-info">
                        <strong>Your registration is almost complete.</strong><br>
                        Your agreement has expired or You had not yet submitted the agremment <br>
                        Please upload your signed agreement to activate your agent account.
                    </div>

                    @elseif($user->agreement_status === 'uploaded')
                    <div class="alert alert-primary">
                        <strong>Your agreement has been uploaded.</strong><br>
                        It is currently <strong>under review</strong>.<br>
                        You will receive a notification once it is verified.
                    </div>

                    @elseif($user->agreement_status === 'verified')
                    <div class="alert alert-success">
                        <strong>Your agreement has been verified!</strong><br>
                        Your account is now fully active.<br>
                        <span class="text-muted">If needed, you can upload a newer version of the agreement below.</span>
                    </div>
                    @endif


                    <p class="mb-3"><strong>Steps to complete your registration:</strong></p>

                    <ul class="list-group mb-4">
                        <li class="list-group-item">
                            1️⃣ Download the agreement provided by the admin:<br>
                            <p class="text-center"><a href="{{ asset('storage/agreement_file.docx') }}" target="_blank" class="m-2">📄 Download Agreement</a></p>
                        </li>
                        <li class="list-group-item" style="background-color: #fff3cd;">
                            2️⃣ Update your name and other details highlighted in yellow.
                        </li>
                        <li class="list-group-item">3️⃣ Sign the agreement (digitally or print → sign → scan).</li>
                        <li class="list-group-item">4️⃣ Upload the signed agreement using the form below.</li>
                    </ul>

                    {{-- If agreement exists --}}
                    @if($user->agreement_file)
                    <div class="mb-3">
                        <p><strong>Current file:</strong>
                            <a href="{{ asset('storage/' . $user->agreement_file) }}" target="_blank" class="btn-secondary mb-2 p-2 rounded">
                                👁️ View uploaded agreement
                            </a>
                        </p>

                        <p class="m-2"><strong>Status:</strong>
                            {{ ucfirst($user->agreement_status) }}
                        </p>

                        {{-- REUPLOAD (always visible if a file exists) --}}
                        <form method="POST" action="{{ route('auth.agreement.upload') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Upload Updated Agreement (PDF)</label>
                                <input type="file" name="agreement_file" class="form-control @error('agreement_file') is-invalid @enderror" required>

                                @error('agreement_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <small class="text-muted d-block mt-1">Max 5MB. PDF only.</small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Upload Again</button>
                        </form>

                    </div>

                    {{-- No agreement uploaded yet --}}
                    @else
                    <form method="POST" action="{{ route('auth.agreement.upload') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Upload Signed Agreement (PDF)</label>
                            <input type="file" name="agreement_file" class="form-control @error('agreement_file') is-invalid @enderror" required>

                            @error('agreement_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted d-block mt-1">Max 10Mb</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Upload Agreement</button>
                    </form>
                    @endif

                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted mb-0">If you haven't received an agreement, Please contact the admin.</p>
                <p class="text-muted mb-0">=977 01-4547547 / 01-5318333</p>
            </div>

        </div>
    </div>
</div>
@endsection

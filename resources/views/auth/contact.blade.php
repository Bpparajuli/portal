@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class=" p-5">
    {{-- Hero / Title --}}
    <div class="row mb-4">
        <div class="col-md-8 mx-auto text-center">
            <h2 class="fw-bold">Get In Touch</h2>
            <h4 class="mb-2">Have any Queries?</h4>
            <div class="divider mx-auto"></div>
        </div>
    </div>

    {{-- Contact Info + Map + Form --}}
    <div class="row gy-4">
        <div class="col-lg-4">
            {{-- Info boxes --}}
            <div class="mb-4">
                <div class="icon-box">
                    <div class="me-2">
                        <i class="far fa-building fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Meet Us</h5>
                        <p class="mb-0">
                            Location: Dillibazar-30 Kathmandu Nepal<br />
                            3rd Floor of Sano Ganesh Mandir (<strong>One-way road</strong>)
                        </p>
                    </div>
                </div>

                <div class="icon-box">
                    <div class="me-2">
                        <i class="fas fa-phone-volume fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Call Us</h5>
                        <p class="mb-0">
                            01-4547547<br />
                            01-4547548<br />
                            +977 9761799575
                        </p>
                    </div>
                </div>

                <div class="icon-box">
                    <div class="me-2">
                        <i class="far fa-envelope-open fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Email Us</h5>
                        <p class="mb-0">
                            info@ideaconsultancyservices.com<br />
                            ideaconsultingservice@gmail.com<br />
                            enquiry-ics.hotmail.com
                        </p>
                    </div>
                </div>
            </div>

            {{-- Map --}}
            <div class="ratio ratio-4x3">
                <iframe loading="lazy" src="https://maps.google.com/maps?q=idea%20consultancy%20services%20pvt%20ltd&amp;t=m&amp;z=14&amp;output=embed&amp;iwloc=near" title="Idea Consultancy Services Pvt Ltd" aria-label="Idea Consultancy Services Pvt Ltd" style="border:0;" allowfullscreen="" referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="p-4 form-section">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-uppercase">Don't be a stranger!</small>
                        <h2 class="fw-bold">You tell us. We listen.</h2>
                        <p>
                            We will contact you as soon as we get your message. In most cases, we will respond within 24 hours. However, if your message is particularly complex or requires further research, it may take us longer to get back to you. We appreciate your patience and understanding.
                        </p>
                    </div>
                </div>

                {{-- Flash success --}}
                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                {{-- Validation errors --}}
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Contact form --}}
                <form action="{{ route('auth.contact.submit') }}" method="POST" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="NAME" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror" placeholder="SUBJECT" value="{{ old('subject') }}">
                            @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="EMAIL" value="{{ old('email') }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea id="message" name="message" class="form-control @error('message') is-invalid @enderror" placeholder="MESSAGE" rows="5" required>{{ old('message') }}</textarea>
                            @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Honeypot (optional) --}}
                        <div style="display:none;">
                            <label for="hp">Name</label>
                            <input type="text" id="hp" name="hp" value="">
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

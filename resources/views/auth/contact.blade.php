@extends('layouts.app')
@section('title', 'Contact Us')

@section('content')
    <style>
        .divider {
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #1a0262, #820b5c);
            border-radius: 2px;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        /* Submit Button */
        .contact-btn {
            border-radius: 10px;
            border: none;
            background: var(--success);
            padding: 12px 28px;
            color: #fff;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .contact-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.5);
            background: linear-gradient(90deg, #1a0262, #820b5c);

        }

        .fade-in-up,
        .fade-in-left,
        .fade-in-right {
            opacity: 0;
            animation-duration: 0.8s;
            animation-fill-mode: forwards;
        }

        .fade-in-up {
            transform: translateY(30px);
            animation-name: fadeInUp;
        }

        .fade-in-left {
            transform: translateX(-30px);
            animation-name: fadeInLeft;
        }

        .fade-in-right {
            transform: translateX(30px);
            animation-name: fadeInRight;
        }

        .delay-1 {
            animation-delay: 0.3s;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* =========================
                                                                                                                                       Responsive Design
                                                                                                                                       ========================= */
        @media (max-width: 768px) {
            .contact-info .info-box {
                gap: 10px;
            }

            .contact-info .info-box i {
                font-size: 1.2rem;
                min-width: 34px;
            }

            .form-section {
                padding: 18px;
            }

            .contact-btn {
                width: 100%;
                padding: 14px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .contact-page .divider {
                width: 60px;
                height: 3px;
            }

            .contact-info h5 {
                font-size: 0.95rem;
            }

            .form-control {
                padding: 10px;
                font-size: 0.9rem;
            }
        }
    </style>

    <div class="bg-light p-2 py-5">

        {{-- Hero Section --}}
        <div class="text-center mb-5 fade-in-up">
            <h1 class="fw-bold">Get In Touch</h1>
            <p class="lead text-muted">We’d love to hear from you! Reach out through any of the options below.</p>
            <div class="divider mx-auto"></div>
        </div>

        <div class="row g-4">
            {{-- Contact Info --}}
            <div class="col-lg-5 fade-in-left">
                <div class="contact-info bg-white p-4 shadow-sm rounded-3 mb-4">
                    <div class="info-box d-flex align-items-start mb-4">
                        <i class="far fa-building fa-2x text-primary me-3"></i>
                        <div>
                            <h5 class="fw-bold">Visit Us</h5>
                            <ul>
                                <li class="mb-0 small text-muted">
                                    Dillibazar-30 Kathmandu, Nepal<br>
                                    3rd Floor of Sano Ganesh Mandir (<strong>One-way road</strong>)
                                </li>
                                <li class="mb-0 small text-muted mt-2">
                                    Banehswor-10 Kathmandu, Nepal<br>
                                    2nd Floor (<strong>opposite to Everest Bank</strong>)
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="info-box d-flex align-items-start mb-4">
                        <i class="fas fa-phone-volume fa-2x text-success me-3"></i>
                        <div>
                            <h5 class="fw-bold">Call Us</h5>
                            <p class="mb-0 small text-muted">
                                01-4547547 / 01-4547548<br>
                                015318333<br>
                                +977 9761799575 / 9705547547
                            </p>
                        </div>
                    </div>

                    <div class="info-box d-flex align-items-start">
                        <i class="far fa-envelope-open fa-2x text-danger me-3"></i>
                        <div>
                            <h5 class="fw-bold">Email Us</h5>
                            <p class="mb-0 small text-muted">
                                info@ideaconsultancyservices.com<br>
                                ideaconsultingservice@gmail.com<br>
                                enquiry-ics@hotmail.com
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Map --}}
                <div class="ratio ratio-4x3 rounded-3 overflow-hidden shadow-sm fade-in-left delay-1">
                    <iframe loading="lazy"
                        src="https://maps.google.com/maps?q=idea%20consultancy%20services%20pvt%20ltd&amp;t=m&amp;z=14&amp;output=embed&amp;iwloc=near"
                        title="Idea Consultancy Services Pvt Ltd" style="border:0;" allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

            {{-- Contact Form --}}
            <div class="col-lg-7 fade-in-right">
                <div class="form-section bg-white p-5 shadow-sm rounded-3">
                    <h3 class="fw-bold mb-3">Send Us a Message</h3>
                    <p class="text-muted mb-4">
                        We’ll get back to you within 24 hours. Your thoughts and feedback are important to us.
                    </p>
                    <form action="{{ route('auth.contact.submit') }}" method="POST" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror" placeholder="Your Name"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" id="address" name="address"
                                    class="form-control @error('address') is-invalid @enderror" placeholder="Your Address"
                                    value="{{ old('address') }}" required>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="contact" class="form-label">Contact <span class="text-danger">*</span></label>
                                <input type="text" id="contact" name="contact"
                                    class="form-control @error('contact') is-invalid @enderror" placeholder="Your Contact"
                                    value="{{ old('contact') }}" required>
                                @error('contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror" placeholder="Your Email"
                                    value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" id="subject" name="subject"
                                    class="form-control @error('subject') is-invalid @enderror" placeholder="Subject"
                                    value="{{ old('subject') }}">
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea id="message" name="message" class="form-control @error('message') is-invalid @enderror"
                                    placeholder="Write your message..." rows="7" required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Honeypot --}}
                            <div style="display:none;">
                                <input type="text" name="hp">
                            </div>

                            <div class="col-12 justify-content-end d-flex">
                                <button type="submit" class="contact-btn px-4 py-2">
                                    <i class="fas fa-paper-plane me-2"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

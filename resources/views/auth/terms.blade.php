@extends('layouts.app')
@section('title', 'Terms & Conditions')

@section('content')
<div class="terms-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                {{-- Card --}}
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-idea2 text-white text-center py-4">
                        <h2 class="fw-bold mb-0">Terms & Conditions</h2>
                        <p class="mb-0 small">Please read carefully before using our services.</p>
                    </div>

                    <div class="card-body p-5">

                        <h4 class="fw-bold mb-3">1. Introduction</h4>
                        <p class="text-muted">
                            By accessing and registering with <strong>Idea Consultancy Services</strong>, you agree to abide by these Terms and Conditions.
                            If you do not agree, you should refrain from using our services.
                        </p>

                        <h4 class="fw-bold mt-4 mb-3">2. Eligibility</h4>
                        <p class="text-muted">
                            To register as an agent, you must:
                        </p>
                        <ul class="text-muted">
                            <li>Must be the registered company in the respective country by following the registration act.</li>
                            <li>Provide accurate and truthful information during registration.</li>
                            <li>Maintain updated information with us at all times.</li>
                        </ul>

                        <h4 class="fw-bold mt-4 mb-3">3. Account Responsibilities</h4>
                        <p class="text-muted">
                            You are responsible for maintaining the confidentiality of your login credentials and
                            for all activities under your account. Notify us immediately if you suspect unauthorized access.
                        </p>

                        <h4 class="fw-bold mt-4 mb-3">4. Use of Services</h4>
                        <p class="text-muted">
                            Our platform must only be used for legitimate purposes such as student consultation, application submissions, and document management.
                            Any misuse including fraud, misrepresentation, or spamming will result in termination of your account.
                        </p>

                        <h4 class="fw-bold mt-4 mb-3">5. Intellectual Property</h4>
                        <p class="text-muted">
                            All content, logos, and trademarks displayed on our website are the property of Idea Consultancy Services.
                            Unauthorized use, reproduction, or distribution is strictly prohibited.
                        </p>

                        <h4 class="fw-bold mt-4 mb-3">6. Limitation of Liability</h4>
                        <p class="text-muted">
                            We shall not be held liable for indirect, incidental, or consequential damages arising from
                            the use or inability to use our services, including but not limited to delays, errors, or interruptions.
                        </p>

                        <h4 class="fw-bold mt-4 mb-3">7. Termination</h4>
                        <p class="text-muted">
                            We reserve the right to suspend or terminate your account if you violate these Terms or engage in
                            unlawful activities. Upon termination, your access to the platform will be revoked immediately.
                        </p>

                        <h4 class="fw-bold mt-4 mb-3">8. Changes to Terms</h4>
                        <p class="text-muted">
                            We may update these Terms & Conditions at any time. Continued use of our platform
                            after changes constitutes acceptance of the revised terms.
                        </p>

                        <h4 class="fw-bold mt-4 mb-3">9. Contact Us</h4>
                        <p class="text-muted">
                            If you have any questions about these Terms, please contact us at:
                        </p>
                        <ul class="text-muted">
                            <li><i class="fas fa-envelope me-2 text-primary"></i> info@ideaconsultancyservices.com</li>
                            <li><i class="fas fa-phone me-2 text-success"></i> +977 9761799575</li>
                            <li><i class="fas fa-map-marker-alt me-2 text-danger"></i> Dillibazar-30, Kathmandu, Nepal</li>
                        </ul>

                        <div class="text-center mt-5">
                            <a href="{{ route('register') }}" class="btn btn-primary px-4">
                                <i class="fas fa-arrow-left me-2"></i> Back to Registration
                            </a>
                        </div>

                    </div>
                </div>
                {{-- /Card --}}

            </div>
        </div>
    </div>
</div>
@endsection

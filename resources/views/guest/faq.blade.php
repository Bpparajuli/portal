@extends('layouts.guest')

@section('title', 'Frequently Asked Questions - Idea Consultancy')
@section('page-title', 'FAQ')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="text-center mb-5" data-aos="fade-down">
                <span class="badge bg-light text-primary px-3 py-2 mb-2" style="font-size:0.8rem;border-radius:20px;">
                    <i class="fas fa-circle-question me-1"></i> Got Questions?
                </span>
                <h1 class="fw-bold" style="color:var(--primary);">Frequently Asked Questions</h1>
                <p class="text-muted">Find answers to the most common questions below.</p>
            </div>

            @if(count($faqs))
                <div class="accordion" id="faqAccordion">
                    @foreach($faqs as $i => $faq)
                        <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden" data-aos="fade-up" data-aos-delay="{{ min($i * 50, 300) }}">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faq{{ $faq->id }}"
                                    style="font-weight:600;font-size:1rem;color:#1f2937;background:#fff;">
                                    <i class="fas fa-question-circle me-2" style="color:var(--primary);font-size:0.9rem;"></i>
                                    {{ $faq->title }}
                                </button>
                            </h2>
                            <div id="faq{{ $faq->id }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body bg-white" style="color:#4b5563;line-height:1.8;">
                                    {!! nl2br(e($faq->content)) !!}
                                    @if($faq->excerpt)
                                        <hr class="my-3">
                                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>{{ $faq->excerpt }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5" data-aos="fade-up">
                    <i class="fas fa-circle-question fa-4x text-muted mb-3 opacity-50"></i>
                    <h4 class="text-muted">No FAQs Available</h4>
                    <p class="text-muted small">Check back later for frequently asked questions.</p>
                </div>
            @endif

            <div class="text-center mt-5 p-4 rounded-3" style="background:#f8fafc;" data-aos="fade-up">
                <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                <h5 class="fw-semibold">Still have questions?</h5>
                <p class="text-muted small mb-2">Can't find the answer you're looking for? Please reach out to us.</p>
                <a href="{{ route('guest.enquiries.create') }}" class="btn btn-primary px-4 btn-sm">
                    <i class="fas fa-paper-plane me-1"></i>Contact Us
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

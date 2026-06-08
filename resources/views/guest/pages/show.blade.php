@extends('layouts.guest')

@section('title', $page->title . ' - Idea Consultancy')
@section('page-title', $page->title)

@section('content')
    <div class="container py-4">
            <article class="card border-0 shadow-sm rounded-3 overflow-hidden" data-aos="fade-up">
            <div class="card-body p-4 p-lg-5">
                <h1 class="fw-bold mb-3" style="color: var(--primary);">{{ $page->title }}</h1>

                @if($page->meta_description)
                    <p class="text-muted lead mb-4">{{ $page->meta_description }}</p>
                @endif

                <hr class="my-4">

                <div class="page-content" style="font-size: 1rem; line-height: 1.8; color: #374151;">
                    {!! nl2br(e($page->content)) !!}
                </div>

                <hr class="my-4">

                <div class="d-flex align-items-center text-muted small">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <span>Last updated: {{ $page->updated_at ? $page->updated_at->format('F j, Y') : $page->created_at->format('F j, Y') }}</span>
                </div>
            </div>
        </article>
    </div>
@endsection

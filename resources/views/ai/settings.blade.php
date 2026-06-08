@extends('layouts.admin')
@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header title="AI Configuration">
        <x-slot:actions>
            <a href="{{ route('ai.assistant') }}" class="btn btn-outline-primary"><i class="fas fa-robot me-1"></i>Open Assistant</a>
        </x-slot:actions>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-sliders-h me-2 text-primary"></i>AI Settings</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('ai.settings.update') }}">
                        @csrf

                        @php
                            $keyVal = fn($k) => optional($aiSettings->firstWhere('key', $k))->value;
                            $keyType = fn($k) => optional($aiSettings->firstWhere('key', $k))->type ?? 'string';
                        @endphp

                        <div class="mb-4">
                            <label class="form-label fw-semibold">OpenAI API Key</label>
                            <input type="password" name="openai_key" class="form-control" value="{{ $keyVal('openai_key') }}" placeholder="sk-...">
                            <div class="form-text">Required for GPT-powered responses. Get yours at <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com</a></div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">AI Model</label>
                                <select name="ai_model" class="form-select">
                                    @foreach(['gpt-3.5-turbo','gpt-4','gpt-4-turbo','gpt-4o'] as $m)
                                        <option value="{{ $m }}" {{ $keyVal('ai_model') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Temperature</label>
                                <input type="number" name="ai_temperature" class="form-control" value="{{ $keyVal('ai_temperature') ?? '0.7' }}" step="0.1" min="0" max="2">
                                <div class="form-text">0 = precise, 2 = creative</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Max Tokens</label>
                                <input type="number" name="ai_max_tokens" class="form-control" value="{{ $keyVal('ai_max_tokens') ?? '500' }}" min="100" max="4000">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="ai_enabled" value="true" role="switch"
                                    id="aiEnabled" {{ filter_var($keyVal('ai_enabled'), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="aiEnabled">Enable AI Assistant</label>
                                <div class="form-text">When disabled, the assistant will use pattern-matched replies only.</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Settings</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-info"></i>Current Status</h6>
                </div>
                <div class="card-body p-4">
                    @php
                        $hasKey = !empty($keyVal('openai_key'));
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="rounded-circle d-inline-block" style="width:12px;height:12px;background:{{ $hasKey ? '#22c55e' : '#ef4444' }};"></span>
                        <span class="fw-semibold small">{{ $hasKey ? 'OpenAI Connected' : 'No API Key Set' }}</span>
                    </div>
                    @if(!$hasKey)
                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Without an API key, the assistant uses pattern-matched replies which may not be as accurate.
                    </div>
                    @endif
                    <hr>
                    <p class="small text-muted mb-0">Model: <strong>{{ $keyVal('ai_model') ?: 'gpt-3.5-turbo (default)' }}</strong></p>
                    <p class="small text-muted mb-0">Temperature: <strong>{{ $keyVal('ai_temperature') ?: '0.7' }}</strong></p>
                    <p class="small text-muted">Max Tokens: <strong>{{ $keyVal('ai_max_tokens') ?: '500' }}</strong></p>
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-lightbulb me-2 text-warning"></i>Tips</h6>
                </div>
                <div class="card-body p-3 small">
                    <ul class="mb-0 ps-3">
                        <li class="mb-1">Ask about student counts, application stats</li>
                        <li class="mb-1">Request recent activity summaries</li>
                        <li class="mb-1">Use for document analysis and review</li>
                        <li>Set temperature lower (0.3-0.5) for factual answers</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
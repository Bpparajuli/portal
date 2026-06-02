@extends('layouts.crm')

@section('title', 'Notification Settings')

@push('styles')
    <style>
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .settings-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .settings-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .settings-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: #1f2937;
        }

        .settings-body {
            padding: 1.5rem;
        }

        .setting-group {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .setting-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .setting-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .setting-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 1rem;
        }

        .setting-description {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        /* Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #3b82f6;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        .btn-save {
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-save:hover {
            background: #2563eb;
        }

        .btn-back {
            background: #9ca3af;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 1rem;
        }

        .btn-back:hover {
            background: #6b7280;
            color: white;
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="settings-container">
        <div class="settings-card">
            <div class="settings-header">
                <h1>
                    <i class="fas fa-bell me-2"></i>
                    Notification Settings
                </h1>
                <p class="text-muted mt-2 mb-0">Choose which notifications you want to receive</p>
            </div>

            @if (session('success'))
                <div class="alert-success m-3">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert-error m-3">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('crm.notifications.update-settings') }}" id="settingsForm">
                @csrf

                <div class="settings-body">
                    <div class="setting-group">
                        <div class="setting-label">
                            <div>
                                <div class="setting-title">
                                    <i class="fas fa-tasks me-2"></i>
                                    Task Assigned
                                </div>
                                <div class="setting-description">
                                    Get notified when a task is assigned to you
                                </div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="task_assigned" value="1"
                                    {{ $preferences['task_assigned'] ?? true ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-group">
                        <div class="setting-label">
                            <div>
                                <div class="setting-title">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    Task Due Today
                                </div>
                                <div class="setting-description">
                                    Get notified when a task is due today
                                </div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="task_due_today" value="1"
                                    {{ $preferences['task_due_today'] ?? true ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-group">
                        <div class="setting-label">
                            <div>
                                <div class="setting-title">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Upcoming Tasks
                                </div>
                                <div class="setting-description">
                                    Get notified about tasks due tomorrow
                                </div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="task_upcoming" value="1"
                                    {{ $preferences['task_upcoming'] ?? true ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-group">
                        <div class="setting-label">
                            <div>
                                <div class="setting-title">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Overdue Tasks
                                </div>
                                <div class="setting-description">
                                    Get reminded about overdue tasks (once per day)
                                </div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="task_overdue" value="1"
                                    {{ $preferences['task_overdue'] ?? true ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-group">
                        <div class="setting-label">
                            <div>
                                <div class="setting-title">
                                    <i class="fas fa-envelope me-2"></i>
                                    Email Notifications
                                </div>
                                <div class="setting-description">
                                    Receive email notifications for important updates
                                </div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="email_notifications" value="1"
                                    {{ $preferences['email_notifications'] ?? false ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="settings-footer p-4 border-top" style="background: #f9fafb;">
                    <a href="{{ route('crm.dashboard') }}" class="btn-back">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save me-2"></i>Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('settingsForm')?.addEventListener('submit', function(e) {
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            submitBtn.disabled = true;

            // Re-enable after a timeout in case of issues
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    </script>
@endpush

{{-- resources/views/crm/show.blade.php --}}
@extends('layouts.crm')

@section('title', $student->full_name ?? $student->first_name . ' ' . $student->last_name)

@push('styles')
    <style>
        /* CRM-specific components */
        .crm-back-bar {
            background: var(--light);
            border-bottom: 1px solid var(--glass-gradient);
            padding: .5rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .crm-back-bar a {
            text-decoration: none;
            color: var(--muted);
        }

        .crm-back-bar a:hover {
            color: var(--primary);
        }

        .crm-student-header {
            background: var(--light-blue);
            border-bottom: 1px solid var(--glass-gradient);
            padding: 1.25rem 1.5rem;
        }

        .staff-avatar {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--glass-gradient);
        }

        .stu-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
        }

        .stu-meta {
            font-size: .85rem;
            color: var(--muted);
            margin-top: .25rem;
        }

        .stu-tag {
            display: inline-block;
            font-size: .7rem;
            background: #f0f2ff;
            color: var(--primary);
            border-radius: 20px;
            padding: .15rem .55rem;
            margin: .1rem;
        }

        .revenue-summary {
            background: linear-gradient(135deg, #059669, #047857);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            color: white;
        }

        .revenue-total {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .revenue-item {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e9f2;
        }

        .revenue-item:last-child {
            border-bottom: none;
        }

        .revenue-method-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .revenue-method-cash {
            background: #e0e7ff;
            color: #3730a3;
        }

        .revenue-method-bank_transfer {
            background: #dcfce7;
            color: #166534;
        }

        .revenue-method-credit_card {
            background: #fed7aa;
            color: #9a3412;
        }

        .revenue-method-cheque {
            background: #fef9c3;
            color: #854d0e;
        }

        .revenue-method-online_payment {
            background: #e0f2fe;
            color: #075985;
        }

        /* Stage Pipeline */
        .stage-pipeline {
            width: 100%;
            overflow: hidden;
            background: #f9fafb;
            padding: 0.5rem 0;
        }

        .stage-track {
            display: flex;
            align-items: stretch;
            gap: 2px;
            width: 100%;
        }

        .stage-wrapper {
            flex: 1;
            min-width: 0;
        }

        .stage-wrapper form {
            margin: 0;
            width: 100%;
            height: 100%;
        }

        .stage-card {
            position: relative;
            width: 100%;
            min-height: 48px;
            border: none;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 6px 14px;
            font-size: 10px;
            font-weight: 600;
            line-height: 1.2;
            text-align: center;
            color: #374151;
            white-space: normal;
            word-break: break-word;
            cursor: pointer;
            clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 50%, calc(100% - 8px) 100%, 0 100%, 8px 50%);
            transition: 0.2s ease;
        }

        .stage-wrapper:first-child .stage-card {
            clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 50%, calc(100% - 8px) 100%, 0 100%);
        }

        .stage-card.current {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
        }

        .stage-card.passed {
            background: #a8ebc4;
            color: #0f6e00;
            font-weight: 800;
        }

        .stage-card.passed::before {
            content: "✓✓";
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            font-weight: bold;
            color: #059669;
        }

        .stage-card.pending {
            background: #e5e7eb;
            color: #4b5563;
        }

        .stage-title {
            display: block;
            width: 100%;
        }

        .stage-days {
            font-size: 9px;
            opacity: 0.7;
            display: block;
            margin-top: 2px;
        }

        .crm-body {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 1.25rem;
            padding: 1.25rem 1.5rem;
        }

        @media (max-width:992px) {
            .crm-body {
                grid-template-columns: 1fr;
            }
        }

        .crm-section {
            background: var(--light);
            border: 1px solid var(--dark-gradient);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        .crm-section-header {
            padding: .75rem 1.1rem;
            border-bottom: 1px solid var(--glass-gradient);
            font-size: .8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            background: #fafbff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .crm-section-body {
            padding: 1rem 1.1rem;
        }

        .note-display {
            white-space: pre-wrap;
            font-size: .875rem;
            color: var(--text);
            line-height: 1.6;
        }

        .note-item {
            padding: .65rem .85rem;
            border: 1px solid var(--glass-gradient);
            border-radius: 8px;
            margin-bottom: .6rem;
            font-size: .85rem;
        }

        .note-item.pinned {
            border-left: 3px solid #f59e0b;
            background: #fffbeb;
        }

        .note-item.log-entry {
            border-left: 3px solid #3b82f6;
            background: #f0f2ff;
        }

        .note-pin-btn,
        .edit-note-btn {
            background: none;
            border: none;
            font-size: .85rem;
            cursor: pointer;
            color: var(--muted);
            padding: 0 4px;
        }

        .note-pin-btn:hover {
            color: #f59e0b;
        }

        .edit-note-btn:hover {
            color: #3b82f6;
        }

        .note-actions {
            display: flex;
            gap: 0.5rem;
        }

        .crm-tabs {
            display: flex;
            border-bottom: 2px solid var(--glass-gradient);
            padding: 0 1.1rem;
            background: var(--light-blue);
        }

        .crm-tab {
            padding: .65rem 1.1rem;
            font-size: .85rem;
            font-weight: 500;
            border: none;
            background: none;
            cursor: pointer;
            color: var(--muted);
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all .15s;
        }

        .crm-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tasks-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .task-box {
            border: 1px solid var(--light-gradient);
            border-radius: 10px;
            padding: .85rem 1rem;
            background: var(--crm-bg);
        }

        .task-box-header {
            font-size: .8rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: .75rem;
            padding-bottom: .5rem;
            border-bottom: 2px solid var(--glass-gradient);
        }

        .task-item {
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            padding: .6rem .75rem;
            border: 1px solid var(--glass-gradient);
            border-radius: 8px;
            margin-bottom: .5rem;
            font-size: .83rem;
            transition: all 0.2s;
        }

        .task-item:hover {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .task-item.overdue {
            border-left: 4px solid #ef4444;
            background: #fef2f2;
        }

        .task-item.today {
            border-left: 4px solid #10b981;
            background: #ecfdf5;
        }

        .task-item.upcoming {
            border-left: 4px solid #3b82f6;
            background: #f0f2ff;
        }

        .task-title {
            font-weight: 600;
            color: var(--text);
            font-size: .9rem;
        }

        .task-title p {
            margin: 0;
        }

        .task-meta {
            font-size: .72rem;
            color: var(--muted);
            margin-top: .15rem;
        }

        .task-description {
            font-size: .78rem;
            color: var(--text);
            margin-top: .35rem;
            padding: .4rem .6rem;
            background: #f8fafc;
            border-radius: 6px;
        }

        .task-actions {
            margin-left: auto;
            display: flex;
            gap: .3rem;
            flex-shrink: 0;
        }

        .priority-high {
            color: #ef4444;
            font-weight: 600;
        }

        .priority-medium {
            color: #f59e0b;
            font-weight: 600;
        }

        .priority-low {
            color: #10b981;
            font-weight: 600;
        }

        .staff-avatar-sm {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-content.large {
            max-width: 700px;
        }

        .modal-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e5e9f2;
            font-weight: 700;
            font-size: 1.1rem;
            background: #fafbff;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 1.25rem;
        }

        .modal-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid #e5e9f2;
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e5e9f2;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .activity-item {
            padding: .85rem 1rem;
            border-bottom: 1px solid #e5e9f2;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item .act-desc {
            font-size: .82rem;
            color: var(--text);
            margin-top: .35rem;
            background: #f8fafc;
            border-radius: 6px;
            padding: .5rem .75rem;
            border-left: 3px solid #e5e9f2;
        }

        .crm-sidebar {
            position: sticky;
            top: 56px;
            align-self: start;
        }

        .sidebar-field {
            margin-bottom: .85rem;
        }

        .sidebar-field label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            display: block;
            margin-bottom: .2rem;
        }

        .sidebar-field .val {
            font-size: .875rem;
            color: var(--text);
            font-weight: 500;
        }

        .read-only-badge {
            background: #fef3c7;
            color: #92400e;
            font-size: .72rem;
            border-radius: 20px;
            padding: .2rem .65rem;
            font-weight: 600;
        }

        .admin-badge {
            background: #e0e7ff;
            color: #3730a3;
            font-size: .65rem;
            border-radius: 20px;
            padding: .15rem .5rem;
            font-weight: 600;
            margin-left: .5rem;
        }

        .btn {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            border: 1px solid transparent;
            display: inline-block;
            text-align: center;
            text-decoration: none;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
            border-color: #ef4444;
        }

        .btn-success {
            background-color: #10b981;
            color: white;
            border-color: #10b981;
        }

        .btn-warning {
            background-color: #f59e0b;
            color: white;
            border-color: #f59e0b;
        }

        .btn-outline-secondary {
            background: transparent;
            border-color: #94a3b8;
            color: #64748b;
        }

        .btn-outline-primary {
            background: transparent;
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .btn-outline-danger {
            background: transparent;
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-outline-warning {
            background: transparent;
            border-color: #f59e0b;
            color: #f59e0b;
        }

        .w-100 {
            width: 100%;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: 1rem;
        }

        .text-muted {
            color: #64748b;
        }

        .text-center {
            text-align: center;
        }

        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -0.5rem;
        }

        .col-md-6 {
            flex: 0 0 50%;
            padding: 0.5rem;
        }

        .col-sm-6 {
            flex: 0 0 50%;
            padding: 0.5rem;
        }

        .col-12 {
            flex: 0 0 100%;
            padding: 0.5rem;
        }

        .d-flex {
            display: flex;
        }

        .flex-grow-1 {
            flex-grow: 1;
        }

        .flex-column {
            flex-direction: column;
        }

        .align-items-center {
            align-items: center;
        }

        .align-items-start {
            align-items: flex-start;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .justify-content-end {
            justify-content: flex-end;
        }

        .ms-auto {
            margin-left: auto;
        }

        .ms-2 {
            margin-left: 0.5rem;
        }

        .reschedule-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .tag-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10001;
            align-items: center;
            justify-content: center;
        }

        .tag-modal.active {
            display: flex;
        }

        .tag-modal-content {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            width: 90%;
            max-width: 400px;
        }

        .tag-modal-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .tag-modal-close {
            cursor: pointer;
            font-size: 1.5rem;
            line-height: 1;
        }

        .tag-input-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e5e9f2;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .suggested-tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .suggested-tag {
            font-size: 0.75rem;
            background: #f0f2ff;
            color: #3b82f6;
            border-radius: 12px;
            padding: 0.2rem 0.6rem;
            cursor: pointer;
        }

        .suggested-tag:hover {
            background: #3b82f6;
            color: white;
        }

        .modal-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .modal-buttons button {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .sc-tags-section {
            margin-top: 0.5rem;
        }

        .sc-tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
            margin-bottom: 0.3rem;
        }

        .sc-tag {
            font-size: 0.7rem;
            background: #f0f2ff;
            color: #3b82f6;
            border-radius: 12px;
            padding: 0.2rem 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .remove-tag-btn {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            padding: 0;
            margin-left: 0.2rem;
        }

        .remove-tag-btn:hover {
            color: #ef4444;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
        }

        .bg-danger {
            background: #ef4444;
            color: white;
        }

        .bg-success {
            background: #10b981;
            color: white;
        }

        /* Activity Log Styles */
        .log-icon {
            font-size: 1.1rem;
            margin-right: 0.75rem;
        }

        .log-content {
            flex: 1;
        }

        .log-title {
            font-weight: 600;
            font-size: 0.85rem;
        }

        .log-meta {
            font-size: 0.7rem;
            color: #64748b;
        }

        .log-description {
            font-size: 0.8rem;
            margin-top: 0.25rem;
            color: #000000;
        }

        .log-pre {
            white-space: pre-wrap;
            font-family: inherit;
            margin: 0;
        }

        .revenue-card {
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 16px;
        }

        .revenue-title {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .revenue-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 0.85rem;
        }

        .revenue-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .crm-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1100;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .crm-modal {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .crm-modal-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e5e9f2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .crm-modal-header h5 {
            margin: 0;
        }

        .crm-modal-body {
            padding: 1.25rem;
        }

        .crm-modal-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid #e5e9f2;
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }

        .d-none {
            display: none !important;
        }
    </style>

    <style>
        .crm-student-header-modern {
            padding: 1rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
        }

        .student-header-card {
            position: relative;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .student-header-card:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
        }

        .card-gradient-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .student-header-content {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            padding: 1.5rem;
        }

        /* Left Column */
        .header-left-col {
            display: flex;
            gap: 1.25rem;
            flex: 2;
            min-width: 280px;
        }

        .student-avatar-wrapper {
            flex-shrink: 0;
        }

        .student-avatar-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 20px;
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .student-avatar-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .placeholder {
            color: snow;
        }

        .student-avatar-placeholder i {
            font-size: 3rem;
            color: white;
        }

        .student-identity {
            flex: 1;
        }

        .student-name {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 0.75rem 0;
            color: #1a202c;
            letter-spacing: -0.02em;
        }

        .student-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .badge-applying,
        .badge-country {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.75rem;
            background: #f1f5f9;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            color: #334155;
        }

        .badge-applying i,
        .badge-country i {
            font-size: 0.7rem;
            color: #64748b;
        }

        .student-contact-info {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .student-contact-info span {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #475569;
        }

        .student-contact-info i {
            width: 1rem;
            color: #94a3b8;
        }

        .student-agent-info {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #f8fafc;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #475569;
        }

        .agent-logo-sm {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #e2e8f0;
        }

        /* Middle Column */
        .header-middle-col {
            flex: 1;
            min-width: 220px;
            background: #f8fafc;
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .tags-section {
            flex: 1;
        }

        .tags-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .tags-header span {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }

        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .tag-item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            border-radius: 20px;
            padding: 0.25rem 0.5rem 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #1e293b;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .remove-tag-btn {
            background: #f1f5f9;
            border: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }

        .remove-tag-btn:hover {
            background: #fee2e2;
            color: #ef4444;
        }

        .no-tags {
            font-size: 0.75rem;
            color: #94a3b8;
            font-style: italic;
        }

        .btn-quick-edit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: white;
            border: 1px solid #e2e8f0;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .btn-quick-edit:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        /* Right Column */
        .header-right-col {
            flex: 1.5;
            min-width: 200px;
            align-items: baseline;
        }

        .revenue-card-modern {
            background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            padding: 1rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .revenue-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #f7e094;
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .revenue-header i {
            font-size: 0.9rem;
        }

        .revenue-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-item {
            display: flex;
            text-align: center;
        }

        .stat-label {
            display: block;
            font-size: 1rem;
            color: #d6d6d6;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1rem;
            font-weight: 700;
            color: white;
        }

        .stat-value.text-success {
            color: #4ade80;
        }

        .stat-value.text-warning {
            color: #fab115;
        }

        .stat-divider {
            width: 1px;
            height: 30px;
            background: #334155;
        }

        .btn-add-revenue {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 500;
            color: #cbd5e1;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }

        .btn-add-revenue:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .pagination-wrapper {
            margin-top: 1rem;
            display: flex;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .student-header-content {
                flex-direction: column;
            }

            .header-left-col {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .student-contact-info {
                justify-content: center;
            }

            .student-agent-info {
                justify-content: center;
            }

            .student-badges {
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')

    {{-- Back bar --}}
    <div class="crm-back-bar">
        <a href="{{ route('crm.dashboard') }}">← Back to Pipeline</a>
        <div class="d-flex align-items-center gap-2">
            @if ($canEdit)
                <a href="{{ route('crm.student.edit', $student) }}" class="btn btn-sm btn-outline-primary">✏️ Edit Student</a>
            @endif
            @if (!$canEdit)
                <span class="read-only-badge">👁 Read-only</span>
            @endif
            @if (auth()->user()->is_admin)
                <span class="admin-badge">👑 Admin</span>
            @endif
        </div>
    </div>

    {{-- Student header --}}
    <div class="crm-student-header-modern">
        {{-- Main Card --}}
        <div class="student-header-card">
            <div class="student-header-content">
                {{-- Left Column: Avatar & Identity --}}
                <div class="header-left-col">
                    <div class="student-avatar-wrapper">
                        @if ($student->students_photo && Storage::disk('public')->exists($student->students_photo))
                            <img src="{{ Storage::url($student->students_photo) }}" class="student-avatar-img"
                                alt="Photo">
                        @else
                            <div class="student-avatar-placeholder">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                        @endif
                    </div>

                    <div class="student-identity">
                        <h1 class="student-name">
                            {{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}
                        </h1>
                        <div class="student-badges">
                            <span class="badge-applying">
                                <i class="fa-regular fa-file-lines"></i> {{ $student->applying_for ?? 'Not specified' }}
                            </span>
                            <span class="badge-country">
                                <i class="fa-solid fa-globe"></i> {{ $student->preferred_country ?? 'Not specified' }}
                            </span>
                        </div>
                        <div class="student-contact-info">
                            <span><i class="fa-solid fa-phone"></i> {{ $student->phone_number ?? '—' }}</span>
                            <span><i class="fa-regular fa-envelope"></i> {{ $student->email ?? '—' }}</span>
                        </div>
                        <div class="student-agent-info">
                            @if ($student->agent && $student->agent->business_logo)
                                <img src="{{ Storage::url($student->agent->business_logo) }}" alt="Logo"
                                    class="agent-logo-sm">
                            @else
                                <i class="fa-regular fa-building"></i>
                            @endif
                            <span>Student of:
                                <strong>{{ $student->agent?->business_name ?? ($student->agent?->name ?? '—') }}</strong></span>
                        </div>
                        <div>{{ $student->rating }}</div>
                        @if ($canEdit)
                            <button class="btn-quick-edit" onclick="openMiniEditModal()">
                                <i class="fa-regular fa-pen-to-square"></i> Quick Edit
                            </button>
                        @endif
                    </div>

                </div>

                {{-- Middle Column: Tags & Actions --}}
                <div class="header-middle-col">
                    <div class="tags-section">
                        <div class="tags-header">
                            <span><i class="fa-solid fa-tags"></i> Student Tags</span>

                        </div>
                        <div class="tags-container" id="studentTagsList">
                            @if ($student->tags && is_array($student->tags))
                                @foreach ($student->tags as $tag)
                                    <div class="tag-item">
                                        <span>🏷️ {{ $tag }}</span>
                                        <button type="button" class="remove-tag-btn"
                                            data-tag="{{ $tag }}">×</button>
                                    </div>
                                @endforeach
                            @else
                                <div class="no-tags">No tags added yet</div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-2"
                            onclick="openTagModal({{ $student->id }})">
                            <i class="fa-solid fa-plus"></i> Add Tags
                        </button>
                    </div>


                </div>

                {{-- Right Column: Revenue Card --}}
                <div class="header-right-col">
                    <div class="revenue-card-modern">
                        <div class="revenue-header">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Revenue Overview</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="stat-label">Expected</span>
                            <strong class="stat-value">${{ number_format($expectedRevenue, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="stat-label">Received</span>
                            <strong class="stat-value text-success">${{ number_format($collectedRevenue, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="stat-label">Pending</span>
                            <strong class="stat-value text-warning">${{ number_format($remainingDue, 2) }}</strong>
                        </div>
                        <button class="btn-add-revenue" onclick="openRevenueModal({{ $student->id }})">
                            <i class="fa-solid fa-plus-circle"></i> Add Revenue
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if ($revenues instanceof \Illuminate\Pagination\LengthAwarePaginator && $revenues->hasPages())
            <div class="pagination-wrapper">
                {{ $revenues->links() }}
            </div>
        @endif
    </div>


    {{-- Stage Pipeline --}}
    <div class="stage-pipeline">
        <div class="stage-track">
            @foreach ($stages as $index => $stg)
                @php
                    $isCurrent = $currentStage?->id === $stg->id;
                    $isPassed = $currentStage && $stg->stage_order < $currentStage->stage_order;
                    $statusClass = $isCurrent ? 'current' : ($isPassed ? 'passed' : 'pending');
                @endphp
                <div class="stage-wrapper">
                    @if ($canEdit)
                        <form action="{{ route('crm.student.stage', $student) }}" method="POST">
                            @csrf
                            <input type="hidden" name="new_stage_id" value="{{ $stg->id }}">
                            <button type="submit" class="stage-card {{ $statusClass }}"
                                onclick="return confirm('Move to \'{{ $stg->name }}\'?')">
                                <span class="stage-title">{{ $stg->name }}</span>
                            </button>
                        </form>
                    @else
                        <div class="stage-card {{ $statusClass }}">
                            <span class="stage-title">{{ $stg->name }}</span>
                            @if ($isCurrent)
                                <span class="stage-days">{{ $student->days_in_current_stage ?? 0 }}d</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Main body --}}
    <div class="crm-body">
        {{-- LEFT --}}
        <div>
            {{-- Notes Section --}}
            <div class="crm-section">
                <div class="crm-section-header">
                    <span>📝 Internal Notes</span>
                    @if ($canEdit)
                        <button class="btn btn-sm btn-outline-primary" onclick="openLogNoteModal()">📋 Log
                            Activity</button>
                    @endif
                </div>
                <div class="crm-section-body">
                    <div id="notesContainer">
                        {{-- Pinned Notes --}}
                        @foreach ($notes->where('is_pinned', true) as $note)
                            <div class="note-item pinned" data-note-id="{{ $note->id }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="note-display">{{ $note->content }}</div>
                                    @if ($canEdit)
                                        <div class="note-actions">
                                            <button class="edit-note-btn"
                                                onclick="openEditNoteModal({{ $note->id }}, '{{ addslashes($note->content) }}', {{ $note->is_pinned ? 'true' : 'false' }})">
                                                ✏️
                                            </button>
                                            <button class="note-pin-btn"
                                                onclick="togglePin({{ $note->id }})">📌</button>
                                            <form action="{{ route('crm.notes.destroy', $note) }}" method="POST"
                                                onsubmit="return confirm('Delete note?')" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="note-pin-btn">🗑️</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-muted mt-1" style="font-size:.72rem">
                                    By {{ $note->creator?->name ?? 'Unknown' }} &bull;
                                    {{ $note->created_at->format('d M Y, g:i A') }}
                                </div>
                            </div>
                        @endforeach

                        {{-- Regular Notes (NOT logs) --}}
                        @foreach ($notes->where('is_pinned', false) as $note)
                            <div class="note-item" data-note-id="{{ $note->id }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="note-display">{{ $note->content }}</div>
                                    @if ($canEdit)
                                        <div class="note-actions">
                                            <button class="edit-note-btn"
                                                onclick="openEditNoteModal({{ $note->id }}, '{{ addslashes($note->content) }}', {{ $note->is_pinned ? 'true' : 'false' }})">
                                                ✏️
                                            </button>
                                            <button class="note-pin-btn"
                                                onclick="togglePin({{ $note->id }})">📍</button>
                                            <form action="{{ route('crm.notes.destroy', $note) }}" method="POST"
                                                onsubmit="return confirm('Delete note?')" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="note-pin-btn">🗑️</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-muted mt-1" style="font-size:.72rem">
                                    By {{ $note->creator?->name ?? 'Unknown' }} &bull;
                                    {{ $note->created_at->format('d M Y, g:i A') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($notes->isEmpty())
                        <div class="text-muted text-center py-3" id="noNotesMessage">No notes yet.</div>
                    @endif

                    @if ($canEdit)
                        <div id="noteForm" class="mt-3">
                            <form action="{{ route('crm.notes.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <input type="hidden" name="type" value="internal">
                                <textarea name="content" rows="5" class="form-control mb-2" placeholder="Write a quick note…" required></textarea>
                                <div class="d-flex gap-2 justify-content-between align-items-center">
                                    <div>
                                        <input type="checkbox" name="is_pinned" value="1" id="pin_note">
                                        <label for="pin_note">Pin this note</label>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary">Save Note</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tabs --}}
            <div class="crm-section">
                <div class="crm-tabs">
                    <button class="crm-tab active" onclick="switchTab('tasks', this)">Tasks</button>
                    <button class="crm-tab" onclick="switchTab('documents', this)">Documents</button>
                    <button class="crm-tab" onclick="switchTab('history', this)">History</button>
                </div>

                {{-- TASKS TAB --}}
                <div id="tab-tasks">
                    <div class="crm-section-body">
                        <div class="tasks-container">
                            @if ($canEdit)
                                <div class="mt-3 d-flex justify-content-end">
                                    <button class="btn btn-sm btn-outline-primary w-auto" onclick="openNewTaskModal()">
                                        + Create New Task
                                    </button>
                                </div>
                            @endif
                            @if ($dueTasks->count() > 0)
                                <div class="task-box">
                                    <div class="task-box-header" style="color: #ef4444;">⚠️ Overdue Tasks
                                        ({{ $dueTasks->count() }})</div>
                                    @foreach ($dueTasks as $task)
                                        @include('crm.partials.task-item', [
                                            'task' => $task,
                                            'type' => 'overdue',
                                            'canEdit' => $canEdit,
                                        ])
                                    @endforeach
                                </div>
                            @endif

                            @if ($todayTasks->count() > 0)
                                <div class="task-box">
                                    <div class="task-box-header" style="color: #10b981;">📅 Today's Tasks
                                        ({{ $todayTasks->count() }})</div>
                                    @foreach ($todayTasks as $task)
                                        @include('crm.partials.task-item', [
                                            'task' => $task,
                                            'type' => 'today',
                                            'canEdit' => $canEdit,
                                        ])
                                    @endforeach
                                </div>
                            @endif

                            @if ($plannedTasks->count() > 0)
                                <div class="task-box">
                                    <div class="task-box-header" style="color: #3b82f6;">🗓 Upcoming Tasks
                                        ({{ $plannedTasks->count() }})</div>
                                    @foreach ($plannedTasks as $task)
                                        @include('crm.partials.task-item', [
                                            'task' => $task,
                                            'type' => 'upcoming',
                                            'canEdit' => $canEdit,
                                        ])
                                    @endforeach
                                </div>
                            @endif

                            @if ($dueTasks->count() == 0 && $todayTasks->count() == 0 && $plannedTasks->count() == 0)
                                <div class="task-box">
                                    <div class="text-muted text-center py-4">No pending tasks 🎉</div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4">
                            <div class="task-box-header">✅ Completed & Cancelled Tasks ({{ $completedTasks->total() }})
                            </div>
                            <div id="completedTasksContainer">
                                @forelse($completedTasks as $task)
                                    <div class="activity-item d-flex gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold" style="font-size:.85rem">
                                                {{ $task->subject }}
                                                @if ($task->status === 'cancelled')
                                                    <span class="badge bg-danger">CANCELLED</span>
                                                @else
                                                    <span class="badge bg-success">COMPLETED</span>
                                                @endif
                                            </div>
                                            @if ($task->completion_note)
                                                <div class="act-desc"><strong>Completion notes:</strong>
                                                    {{ $task->completion_note }}</div>
                                            @endif
                                            @if ($task->cancellation_note)
                                                <div class="act-desc"><strong>Cancellation reason:</strong>
                                                    {{ $task->cancellation_note }}</div>
                                            @endif
                                            <div class="text-muted mt-1" style="font-size:.72rem">
                                                @if ($task->status === 'completed')
                                                    Completed on: {{ $task->completed_at?->format('d M Y, g:i A') }} by
                                                    {{ $task->completedBy?->name ?? 'Unknown' }}
                                                @elseif($task->status === 'cancelled')
                                                    Cancelled on: {{ $task->cancelled_at?->format('d M Y, g:i A') }} by
                                                    {{ $task->cancelledBy?->name ?? 'Unknown' }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            @if ($canEdit)
                                                @if ($task->status === 'completed')
                                                    <button onclick="undoComplete({{ $task->id }})"
                                                        class="btn btn-sm btn-outline-secondary">↩️ Undo</button>
                                                @elseif($task->status === 'cancelled')
                                                    <button onclick="undoCancel({{ $task->id }})"
                                                        class="btn btn-sm btn-outline-secondary">↩️ Restore</button>
                                                @endif
                                            @endif
                                            @if (auth()->user()->is_admin)
                                                <button onclick="deleteTask({{ $task->id }})"
                                                    class="btn btn-sm btn-outline-danger">🗑️ Delete</button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted text-center py-3">No completed or cancelled tasks yet.</div>
                                @endforelse
                                @if ($completedTasks->hasPages())
                                    <div class="mt-3">{{ $completedTasks->links() }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="task-box-header">📋 Activity Log</div>
                            <div id="activityLogContainer">
                                @forelse($activityLogs ?? [] as $log)
                                    <div class="note-item log-entry" data-note-id="{{ $log->id }}">
                                        <div class="d-flex justify-content-between">
                                            <div class="log-icon">📝 {{ $log->title ?? 'Activity Log' }}
                                            </div>
                                            @if ($canEdit)
                                                <div class="note-actions">
                                                    <button class="edit-note-btn"
                                                        onclick="openEditNoteModal({{ $log->id }}, '{{ addslashes($log->content) }}', false)">
                                                        ✏️
                                                    </button>
                                                    <form action="{{ route('crm.notes.destroy', $log) }}" method="POST"
                                                        onsubmit="return confirm('Delete this log entry?')"
                                                        class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="note-pin-btn"
                                                            title="Delete log">🗑️</button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="log-content">
                                            <div class="log-title">
                                                <div class="log-description">
                                                    <pre class="log-pre">{{ $log->content }}</pre>
                                                </div>
                                                <div class="log-meta">
                                                    By {{ $log->creator?->name ?? 'Unknown' }} •
                                                    {{ $log->created_at->format('d M Y, g:i A') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted text-center py-3">No activity logs yet. Click "Log Activity"
                                        to add one.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- DOCUMENTS TAB --}}
                    <div id="tab-documents" style="display:none">
                        <div class="crm-section-body">
                            @forelse($student->documents as $doc)
                                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                                    <span class="fs-5">📄</span>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium small">{{ $doc->document_type }}</div>
                                        <div class="text-muted" style="font-size:.72rem">Uploaded
                                            {{ $doc->created_at->format('d M Y') }}</div>
                                    </div>
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            @empty
                                <div class="text-muted text-center py-4">No documents uploaded.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- HISTORY TAB --}}
                    <div id="tab-history" style="display:none">
                        <div class="crm-section-body">
                            <div id="stageHistoryContent">
                                <div class="text-center text-muted py-3 small">Loading history…</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT sidebar --}}
        <div class="crm-sidebar">
            <div class="crm-section">
                <div class="crm-section-header">Student Details</div>
                <div class="crm-section-body">
                    <div class="sidebar-field">
                        <label>Full Name</label>
                        <div class="val">
                            {{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Stage</label>
                        <div class="val">
                            @if ($currentStage)
                                <span
                                    style="background:{{ $currentStage->color }}20;color:{{ $currentStage->color }};border-radius:20px;padding:.2rem .65rem;font-size:.8rem;font-weight:600;display:inline-block">
                                    {{ $currentStage->name }}
                                </span>
                                <div class="text-muted mt-1" style="font-size:.72rem">
                                    {{ $student->days_in_current_stage ?? 0 }} days in this stage</div>
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </div>
                    </div>
                    <div class="sidebar-field">
                        <label>Date of Birth</label>
                        <div class="val">{{ $student->dob?->format('d M Y') ?? '—' }} ({{ $student->age ?? '?' }} yrs)
                        </div>
                    </div>
                    <div class="sidebar-field">
                        <label>Gender</label>
                        <div class="val">{{ $student->gender ?? '—' }}</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Nationality</label>
                        <div class="val">{{ $student->nationality ?? '—' }}</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Passport</label>
                        <div class="val">{{ $student->passport_number ?? '—' }}</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Qualification</label>
                        <div class="val">{{ $student->qualification ?? '—' }} ({{ $student->passed_year ?? '—' }})
                        </div>
                    </div>
                    <div class="sidebar-field">
                        <label>Preferred Country</label>
                        <div class="val">{{ $student->preferred_country ?? '—' }}</div>
                    </div>
                    <div class="sidebar-field">
                        <label>Remarks</label>
                        <div class="val small">{{ $student->remarks ?? '—' }}</div>
                    </div>
                </div>

                <div class="revenue-stats"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 1rem; margin-bottom: 1rem; color: white;">
                    <div class="row">
                        <div class="col-sm-4 text-center" style="padding: 0.5rem;">
                            <div style="font-size: 0.7rem; opacity: 0.9; margin-bottom: 0.25rem;">Expected</div>
                            <div style="font-size: 0.55rem; font-weight: 700;">${{ number_format($expectedRevenue, 2) }}
                            </div>
                        </div>
                        <div class="col-sm-4 text-center" style="padding: 0.5rem;">
                            <div style="font-size: 0.7rem; opacity: 0.9; margin-bottom: 0.25rem;">Collected</div>
                            <div style="font-size: 0.55rem; font-weight: 700;">${{ number_format($collectedRevenue, 2) }}
                            </div>
                        </div>
                        <div class="col-sm-4 text-center" style="padding: 0.5rem;">
                            <div style="font-size: 0.7rem; opacity: 0.9; margin-bottom: 0.25rem;">Due</div>
                            <div style="font-size: 0.55rem; font-weight: 700;">${{ number_format($remainingDue, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="revenue-list">
                    <div class="revenue-title mt-2">📋 Transaction History</div>
                    @forelse($revenuesCollection ?? [] as $revenue)
                        <div class="revenue-item d-flex flex-column justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                    <strong>${{ number_format($revenue->amount, 2) }}</strong>
                                    <span class="revenue-method-badge revenue-method-{{ $revenue->method }}">
                                        {{ ucfirst(str_replace('_', ' ', $revenue->method)) }}
                                    </span>
                                    <small> 📅 {{ $revenue->transaction_date->format('d M Y') }}</small>
                                </div>
                                @if ($revenue->description)
                                    <div class="small text-muted mt-1">{{ $revenue->description }}</div>
                                @endif

                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="small text-muted mt-1">
                                    Verified by {{ $revenue->creator?->name ?? 'Unknown' }}
                                </div>
                                @if (auth()->user()->is_admin)
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="openRevenueModal({{ $student->id }}, {{ $revenue->id }})">
                                            ✏️
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="deleteRevenue({{ $student->id }}, {{ $revenue->id }}, '${{ number_format($revenue->amount, 2) }}')">
                                            🗑️
                                        </button>
                                    </div>
                                @endif
                            </div>

                        </div>
                    @empty
                        <div class="text-muted text-center py-3">No revenue records yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODALS --}}
    {{-- ============================================ --}}

    {{-- MODAL 1: CREATE NEW TASK --}}
    <div id="newTaskModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span>➕ Create New Task</span>
                <button onclick="closeModal('newTaskModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form action="{{ route('crm.tasks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task Title *</label>
                        <input type="text" name="title" class="form-control" required
                            placeholder="e.g., Call student for follow-up">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Task details..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Task Type *</label>
                                <select name="task_type" class="form-select" required>
                                    <option value="call">📞 Call</option>
                                    <option value="email">✉️ Email</option>
                                    <option value="whatsapp">💬 WhatsApp</option>
                                    <option value="meeting">👥 Meeting</option>
                                    <option value="follow_up">⏰ Follow Up</option>
                                    <option value="counseling">🎓 Counseling</option>
                                    <option value="todo">✅ To Do</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Due Date *</label>
                                    <input type="datetime-local" name="due_date" id="due_date" class="form-control"
                                        value="{{ date('Y-m-d\TH:i', strtotime('+7 days')) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Time Slot</label>
                                <select name="time_slot" class="form-select">
                                    <option value="">Any time</option>
                                    <option value="morning">🌅 Morning</option>
                                    <option value="afternoon">☀️ Afternoon</option>
                                    <option value="evening">🌙 Evening</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="low">🟢 Low</option>
                                    <option value="medium" selected>🟡 Medium</option>
                                    <option value="high">🔴 High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Myself ({{ auth()->user()->name }})</option>
                            @foreach ($assignableUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeModal('newTaskModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: COMPLETE TASK --}}
    <div id="completeModal" class="modal-overlay">
        <div class="modal-content large">
            <div class="modal-header">
                <span>✅ Mark as Done</span>
                <button onclick="closeModal('completeModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form id="completeTaskForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task: <strong><span id="completeTaskTitle"></span></strong></label>
                    </div>

                    <div class="form-group">
                        <label>Write Feedback / Completion Notes</label>
                        <textarea name="completion_note" id="completion_note" rows="3" class="form-control"
                            placeholder="What was accomplished? Any important notes? (Optional)"></textarea>
                    </div>

                    <div class="form-group">
                        <label>After completion:</label>
                        <div class="d-flex gap-3 flex-wrap">
                            <label class="d-flex align-items-center gap-2">
                                <input type="radio" name="completion_action" value="just_complete" checked>
                                <span class="btn btn-sm btn-success">✓ Done</span>
                            </label>
                            <label class="d-flex align-items-center gap-2">
                                <input type="radio" name="completion_action" value="create_next">
                                <span class="btn btn-sm btn-primary">✓ Done & Schedule Next</span>
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="closeModal('completeModal')">Discard</button>
                        </div>
                    </div>

                    <div id="newTaskSection"
                        style="display:none; margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div class="form-group">
                            <label style="font-weight: 600;">📋 Schedule Next Activity</label>
                        </div>
                        <div class="form-group">
                            <label>Activity Type *</label>
                            <select name="next_task_type" id="next_task_type" class="form-select">
                                <option value="todo">✅ To-Do</option>
                                <option value="call">📞 Call</option>
                                <option value="email">✉️ Email</option>
                                <option value="whatsapp">💬 WhatsApp</option>
                                <option value="meeting">👥 Meeting</option>
                                <option value="follow_up">⏰ Follow Up</option>
                                <option value="counseling">🎓 Counseling</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Summary *</label>
                            <input type="text" name="next_task_title" id="next_task_title" class="form-control"
                                placeholder="e.g., Follow up on application">
                        </div>
                        <div class="form-group">
                            <label>Log a note...</label>
                            <textarea name="next_task_description" id="next_task_description" rows="2" class="form-control"
                                placeholder="Additional details..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Due Date *</label>
                                    <input type="date" name="due_date" id="due_date" class="form-control"
                                        value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Time Slot</label>
                                    <select name="next_time_slot" id="next_time_slot" class="form-select">
                                        <option value="">Any time</option>
                                        <option value="morning">🌅 Morning</option>
                                        <option value="afternoon">☀️ Afternoon</option>
                                        <option value="evening">🌙 Evening</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Priority</label>
                                    <select name="next_priority" id="next_priority" class="form-select">
                                        <option value="low">🟢 Low</option>
                                        <option value="medium" selected>🟡 Medium</option>
                                        <option value="high">🔴 High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Assign To</label>
                                    <select name="next_assigned_to" id="next_assigned_to" class="form-select">
                                        <option value="">Same assignee</option>
                                        @foreach ($assignableUsers as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeModal('completeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="completeSubmitBtn">✓ Done</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3: RESCHEDULE TASK --}}
    <div id="rescheduleModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span>📅 Reschedule Task</span>
                <button onclick="closeModal('rescheduleModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form id="rescheduleTaskForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task: <strong><span id="rescheduleTaskTitle"></span></strong></label>
                    </div>

                    <div class="form-group">
                        <label>Quick Options</label>
                        <div class="reschedule-buttons">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="setRescheduleDate(1)">Tomorrow</button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="setRescheduleDate(3)">In 3 days</button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="setRescheduleDate(7)">Next week</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>New Due Date</label>
                        <input type="date" name="due_date" id="reschedule_due_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Time Slot</label>
                        <select name="time_slot" id="reschedule_time_slot" class="form-select">
                            <option value="">Any time</option>
                            <option value="morning">🌅 Morning</option>
                            <option value="afternoon">☀️ Afternoon</option>
                            <option value="evening">🌙 Evening</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Reassign to (Optional)</label>
                        <select name="assigned_to" id="reschedule_assigned_to" class="form-select">
                            <option value="">Keep current assignee</option>
                            @foreach ($assignableUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Reschedule Reason (Optional)</label>
                        <textarea name="reschedule_reason" rows="2" class="form-control"
                            placeholder="Why is this task being rescheduled?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeModal('rescheduleModal')">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reschedule Task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 4: CANCEL TASK --}}
    <div id="cancelModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span>❌ Cancel Task</span>
                <button onclick="closeModal('cancelModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form id="cancelTaskForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Task: <strong><span id="cancelTaskTitle"></span></strong></label>
                    </div>
                    <div class="form-group">
                        <label>Cancellation Reason *</label>
                        <textarea name="cancellation_reason" id="cancellation_reason" rows="3" class="form-control" required
                            placeholder="Why is this task being cancelled?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeModal('cancelModal')">Go
                        Back</button>
                    <button type="submit" class="btn btn-danger">Cancel Task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 5: EDIT TASK (Admin Only) --}}
    <div id="editTaskModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span>✏️ Edit Task (Admin Only)</span>
                <button onclick="closeModal('editTaskModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form id="editTaskForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="task_id" id="edit_task_id">
                    <div class="form-group">
                        <label>Task Title *</label>
                        <input type="text" name="title" id="edit_task_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_task_description" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Task Type</label>
                                <select name="task_type" id="edit_task_type" class="form-select">
                                    <option value="call">📞 Call</option>
                                    <option value="email">✉️ Email</option>
                                    <option value="whatsapp">💬 WhatsApp</option>
                                    <option value="meeting">👥 Meeting</option>
                                    <option value="follow_up">⏰ Follow Up</option>
                                    <option value="counseling">🎓 Counseling</option>
                                    <option value="todo">✅ To Do</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" name="due_date" id="edit_due_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Time Slot</label>
                                <select name="time_slot" id="edit_time_slot" class="form-select">
                                    <option value="">Any time</option>
                                    <option value="morning">🌅 Morning</option>
                                    <option value="afternoon">☀️ Afternoon</option>
                                    <option value="evening">🌙 Evening</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" id="edit_priority" class="form-select">
                                    <option value="low">🟢 Low</option>
                                    <option value="medium">🟡 Medium</option>
                                    <option value="high">🔴 High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Assign To</label>
                        <select name="assigned_to" id="edit_assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach ($assignableUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeModal('editTaskModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 6: LOG NOTE --}}
    <div id="logNoteModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span>📋 Log Activity</span>
                <button onclick="closeModal('logNoteModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form action="{{ route('crm.notes.store') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <input type="hidden" name="type" value="log">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Activity Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Title of the log note">
                    </div>
                    <div class="form-group">
                        <label>Activity Description *</label>
                        <textarea name="content" rows="4" class="form-control" required
                            placeholder="Describe the activity, call outcome, meeting notes, or important updates..."></textarea>
                    </div>
                    <div class="text-muted small">This will appear in the Activity Log section only.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeModal('logNoteModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Activity Log</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 7: EDIT NOTE --}}
    <div id="editNoteModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span>✏️ Edit Note</span>
                <button onclick="closeModal('editNoteModal')"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form id="editNoteForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="note_id" id="edit_note_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" id="edit_note_content" rows="5" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_pinned" value="1" id="edit_note_pinned">
                            Pin this note
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeModal('editNoteModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Note</button>
                </div>
            </form>
        </div>
    </div>

    {{-- TAG MODAL --}}
    <div id="tagModal" class="tag-modal">
        <div class="tag-modal-content">
            <div class="tag-modal-header">
                <span>Add Tag</span>
                <span class="tag-modal-close" onclick="closeTagModal()">&times;</span>
            </div>
            <div class="tag-input-group">
                <input type="text" id="tagInput" placeholder="Enter tag name..." maxlength="50">
            </div>
            <div class="suggested-tags-list" id="suggestedTagsList"></div>
            <div class="modal-buttons">
                <button class="btn btn-outline-secondary" onclick="closeTagModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveTag()">Add Tag</button>
            </div>
        </div>
    </div>

    {{-- REVENUE MODAL --}}
    <div id="revenueModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span id="revenueModalTitle">Add Revenue</span>
                <button onclick="closeRevenueModal()"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form id="revenueForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Amount *</label>
                        <input type="number" name="amount" id="revenue_amount" class="form-control" step="0.01"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method *</label>
                        <select name="method" id="revenue_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="cheque">Cheque</option>
                            <option value="online_payment">Online Payment</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Transaction Date *</label>
                        <input type="date" name="transaction_date" id="revenue_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Reference Number</label>
                        <input type="text" name="reference_number" id="revenue_reference" class="form-control"
                            placeholder="Transaction ID, Cheque No, etc.">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="revenue_description" rows="3" class="form-control"
                            placeholder="Additional details..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Receipt (Optional)</label>
                        <input type="file" name="receipt_file" class="form-control" accept="image/*,.pdf">
                        <small class="text-muted">Max 5MB. JPG, PNG, PDF</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeRevenueModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Revenue</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MINI EDIT MODAL --}}
    <div id="miniEditModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h5>✏️ Quick Edit Student</h5>
                <button type="button" onclick="closeMiniEditModal()"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>

            <form method="POST" id="miniEditForm">
                @csrf
                @method('PUT')

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="first_name" id="qe_first_name" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" id="qe_last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone_number" id="qe_phone" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="qe_email" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Preferred Country</label>
                                <input type="text" name="preferred_country" id="qe_country" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Applying For</label>
                                <select name="applying_for" class="form-select">
                                    <option value="">Select Program Type</option>
                                    <option value="Bachelor">Bachelor</option>
                                    <option value="Master">Master</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="Language Course">Language Course</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Expected Revenue</label>
                                <input type="number" step="0.01" name="expected_revenue" id="qe_expected_revenue"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Tags (comma separated)</label>
                                <input type="text" name="tags" id="qe_tags" class="form-control"
                                    placeholder="tag1, tag2, tag3">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="closeMiniEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ========== GLOBAL VARIABLES ==========
        let currentTaskId = null;
        let currentStudentId = {{ $student->id }};
        let historyLoaded = false;
        let currentRevenueId = null;

        // ========== CSRF TOKEN HELPER ==========
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value;
        }

        // ========== TAB SWITCHING ==========
        function switchTab(name, btn) {
            document.querySelectorAll('[id^="tab-"]').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.crm-tab').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + name).style.display = 'block';
            btn.classList.add('active');
            if (name === 'history') loadHistory();
        }

        function loadHistory() {
            if (historyLoaded) return;
            historyLoaded = true;

            fetch('{{ route('crm.student.history', $student) }}')
                .then(response => response.json())
                .then(data => {
                    const el = document.getElementById('stageHistoryContent');
                    if (!data.length) {
                        el.innerHTML =
                            '<div class="text-muted text-center py-4 small">No stage changes recorded.</div>';
                        return;
                    }
                    el.innerHTML = data.map(h => `
                    <div style="display:flex;gap:.75rem;padding:.75rem 0;border-bottom:1px solid #e5e9f2">
                        <span style="font-size:1.1rem">🔄</span>
                        <div>
                            <div style="font-size:.85rem;font-weight:500">${escapeHtml(h.from)} → ${escapeHtml(h.to)}</div>
                            <div style="font-size:.72rem;color:#6b7280">By ${escapeHtml(h.changed_by)} &bull; ${escapeHtml(h.date)}</div>
                        </div>
                    </div>
                `).join('');
                })
                .catch(error => {
                    console.error('Error loading history:', error);
                    document.getElementById('stageHistoryContent').innerHTML =
                        '<div class="text-danger text-center py-3">Failed to load history.</div>';
                });
        }

        // ========== NOTE FUNCTIONS ==========
        function togglePin(noteId) {
            fetch(`/crm/notes/${noteId}/pin`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        showToast(data.message || 'Failed to pin note', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error toggling pin:', error);
                    showToast('An error occurred', 'error');
                });
        }

        function openEditNoteModal(noteId, content, isPinned) {
            document.getElementById('edit_note_id').value = noteId;
            document.getElementById('edit_note_content').value = content;
            document.getElementById('edit_note_pinned').checked = isPinned;
            document.getElementById('editNoteForm').action = `/crm/notes/${noteId}`;
            document.getElementById('editNoteModal').style.display = 'flex';
        }

        function openLogNoteModal() {
            document.getElementById('logNoteModal').style.display = 'flex';
        }

        // ========== TASK MODAL FUNCTIONS ==========
        function openNewTaskModal() {
            document.getElementById('newTaskModal').style.display = 'flex';
        }

        function openCompleteModal(taskId, taskTitle) {
            currentTaskId = taskId;
            document.getElementById('completeTaskTitle').innerHTML = escapeHtml(taskTitle);
            document.getElementById('completeTaskForm').action = `/crm/tasks/${taskId}/complete`;

            document.getElementById('completion_note').value = '';
            document.querySelector('input[name="completion_action"][value="just_complete"]').checked = true;
            document.getElementById('newTaskSection').style.display = 'none';
            document.getElementById('completeSubmitBtn').innerHTML = '✓ Done';
            document.getElementById('completeSubmitBtn').disabled = false;

            document.getElementById('next_task_title').value = '';
            document.getElementById('next_task_description').value = '';
            document.getElementById('next_task_type').value = 'todo';
            document.getElementById('due_date').value = getDefaultDueDate();
            document.getElementById('next_time_slot').value = '';
            document.getElementById('next_priority').value = 'medium';
            document.getElementById('next_assigned_to').value = '';

            document.getElementById('completeModal').style.display = 'flex';
        }

        function openRescheduleModal(taskId, taskTitle) {
            currentTaskId = taskId;
            document.getElementById('rescheduleTaskTitle').innerHTML = escapeHtml(taskTitle);
            document.getElementById('rescheduleTaskForm').action = `/crm/tasks/${taskId}/reschedule`;

            document.getElementById('reschedule_due_date').value = '';
            document.getElementById('reschedule_time_slot').value = '';
            document.getElementById('reschedule_assigned_to').value = '';

            document.getElementById('rescheduleModal').style.display = 'flex';
        }

        function openCancelModal(taskId, taskTitle) {
            currentTaskId = taskId;
            document.getElementById('cancelTaskTitle').innerHTML = escapeHtml(taskTitle);
            document.getElementById('cancelTaskForm').action = `/crm/tasks/${taskId}/cancel`;
            document.getElementById('cancellation_reason').value = '';
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function openEditModal(taskId) {
            @if (!auth()->user()->is_admin)
                alert('Only administrators can edit tasks.');
                return;
            @endif

            fetch(`/crm/tasks/${taskId}/data`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch task data');
                    return response.json();
                })
                .then(task => {
                    document.getElementById('edit_task_id').value = task.id;
                    document.getElementById('edit_task_title').value = task.subject || '';
                    document.getElementById('edit_task_description').value = task.description || '';
                    document.getElementById('edit_task_type').value = task.activity_type || 'todo';
                    document.getElementById('edit_due_date').value = task.scheduled_at || '';
                    document.getElementById('edit_time_slot').value = task.priority_time_slot || '';
                    document.getElementById('edit_priority').value = task.priority || 'medium';
                    document.getElementById('edit_assigned_to').value = task.assigned_to || '';
                    document.getElementById('editTaskForm').action = `/crm/tasks/${taskId}`;
                    document.getElementById('editTaskModal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error loading task:', error);
                    alert('Failed to load task details. Please try again.');
                });
        }

        function deleteTask(taskId) {
            if (!confirm('Are you sure you want to permanently delete this task? This action cannot be undone.')) return;

            fetch(`/crm/tasks/${taskId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Task deleted successfully');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Failed to delete task', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error deleting task:', error);
                    showToast('An error occurred', 'error');
                });
        }

        function setRescheduleDate(days) {
            const date = new Date();
            date.setDate(date.getDate() + days);
            const formattedDate = date.toISOString().split('T')[0];
            document.getElementById('reschedule_due_date').value = formattedDate;
        }

        function getDefaultDueDate() {
            const date = new Date();
            date.setDate(date.getDate() + 7);
            return date.toISOString().split('T')[0];
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // ========== TASK COMPLETION RADIO BUTTON HANDLER ==========
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll('input[name="completion_action"]');
            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const newTaskSection = document.getElementById('newTaskSection');
                    const submitBtn = document.getElementById('completeSubmitBtn');
                    if (this.value === 'create_next') {
                        newTaskSection.style.display = 'block';
                        submitBtn.innerHTML = '✓ Done & Schedule Next';
                        document.getElementById('next_task_title').setAttribute('required',
                            'required');
                        document.getElementById('due_date').setAttribute('required',
                            'required');
                    } else {
                        newTaskSection.style.display = 'none';
                        submitBtn.innerHTML = '✓ Done';
                        document.getElementById('next_task_title').removeAttribute('required');
                        document.getElementById('due_date').removeAttribute('required');
                    }
                });
            });
        });

        // ========== UNDO FUNCTIONS ==========
        function undoComplete(taskId) {
            if (!confirm('Undo mark as completed? This will reopen the task.')) return;

            fetch(`/crm/tasks/${taskId}/undo`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Task reopened successfully', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Failed to undo', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error undoing task:', error);
                    showToast('An error occurred', 'error');
                });
        }

        function undoCancel(taskId) {
            if (!confirm('Restore this cancelled task?')) return;

            fetch(`/crm/tasks/${taskId}/undo-cancel`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Task restored successfully', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Failed to restore', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error restoring task:', error);
                    showToast('An error occurred', 'error');
                });
        }

        // ========== TAG MANAGEMENT ==========
        function openTagModal(studentId) {
            currentStudentId = studentId;
            const modal = document.getElementById('tagModal');
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.add('active');
                document.getElementById('tagInput').value = '';
                document.getElementById('tagInput').focus();
                loadPopularTags();
            }
        }

        function closeTagModal() {
            const modal = document.getElementById('tagModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('active');
            }
        }

        async function loadPopularTags() {
            try {
                const response = await fetch('{{ route('crm.student.popularTags') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Failed to load tags');
                const data = await response.json();
                const container = document.getElementById('suggestedTagsList');
                if (container && data.tags?.length) {
                    container.innerHTML = data.tags.map(tag =>
                        `<span class="suggested-tag" data-tag="${escapeHtml(tag)}">🏷️ ${escapeHtml(tag)}</span>`
                    ).join('');
                    document.querySelectorAll('.suggested-tag').forEach(tag => {
                        tag.onclick = () => document.getElementById('tagInput').value = tag.dataset.tag;
                    });
                } else if (container) {
                    container.innerHTML = '<div class="text-muted small">No popular tags found</div>';
                }
            } catch (error) {
                console.error('Error loading popular tags:', error);
                const container = document.getElementById('suggestedTagsList');
                if (container) {
                    container.innerHTML = '<div class="text-muted small">Failed to load suggestions</div>';
                }
            }
        }

        async function saveTag() {
            const tag = document.getElementById('tagInput').value.trim();
            if (!tag) {
                showToast('Please enter a tag', 'error');
                return;
            }

            try {
                const response = await fetch(`/crm/students/${currentStudentId}/add-tag`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        tag: tag
                    })
                });

                const data = await response.json();
                if (data.success) {
                    updateTagsInCard(currentStudentId, data.tags);
                    showToast(`Tag "${tag}" added successfully`);
                    closeTagModal();
                } else {
                    throw new Error(data.error || 'Failed to add tag');
                }
            } catch (error) {
                console.error('Error saving tag:', error);
                showToast(error.message, 'error');
            }
        }

        async function removeTagFromCard(studentId, tag) {
            if (!confirm(`Remove tag "${tag}"?`)) return;

            try {
                const response = await fetch(`/crm/students/${studentId}/remove-tag`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        tag: tag
                    })
                });

                const data = await response.json();
                if (data.success) {
                    updateTagsInCard(studentId, data.tags);
                    showToast(`Tag "${tag}" removed`);
                } else {
                    throw new Error(data.error || 'Failed to remove tag');
                }
            } catch (error) {
                console.error('Error removing tag:', error);
                showToast(error.message, 'error');
            }
        }

        function updateTagsInCard(studentId, tags) {
            const container = document.getElementById('studentTagsList');
            if (!container) return;

            if (tags?.length) {
                container.innerHTML = tags.map(tag =>
                    `<span class="sc-tag">🏷️ ${escapeHtml(tag)}<button type="button" class="remove-tag-btn" data-tag="${escapeHtml(tag)}">×</button></span>`
                ).join('');

                container.querySelectorAll('.remove-tag-btn').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        removeTagFromCard(studentId, btn.dataset.tag);
                    };
                });
            } else {
                container.innerHTML = '';
            }
        }

        // ========== REVENUE FUNCTIONS ==========
        // ========== REVENUE FUNCTIONS - UPDATED ==========
        function openRevenueModal(studentId, revenueId = null) {
            currentRevenueId = revenueId;
            const modal = document.getElementById('revenueModal');
            const title = document.getElementById('revenueModalTitle');
            const form = document.getElementById('revenueForm');

            if (revenueId) {
                title.textContent = 'Edit Revenue';
                fetch(`/crm/students/${studentId}/revenues/${revenueId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('revenue_amount').value = data.data.amount;
                            document.getElementById('revenue_method').value = data.data.method;
                            document.getElementById('revenue_date').value = data.data.transaction_date;
                            document.getElementById('revenue_description').value = data.data.description || '';
                            document.getElementById('revenue_reference').value = data.data.reference_number || '';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching revenue:', error);
                        showToast('Failed to load revenue data', 'error');
                    });
                form.action = `/crm/students/${studentId}/revenues/${revenueId}`;
                document.querySelector('input[name="_method"]').value = 'PUT';
            } else {
                title.textContent = 'Add Revenue';
                form.reset();
                form.action = `/crm/students/${studentId}/revenues`;
                document.querySelector('input[name="_method"]').value = 'POST';
                document.getElementById('revenue_date').value = new Date().toISOString().split('T')[0];
            }

            modal.style.display = 'flex';
        }

        function closeRevenueModal() {
            const modal = document.getElementById('revenueModal');
            if (modal) modal.style.display = 'none';
        }

        function deleteRevenue(studentId, revenueId, amount) {
            if (!confirm(`Delete revenue of ${amount}? This action cannot be undone.`)) {
                return;
            }

            fetch(`/crm/students/${studentId}/revenues/${revenueId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Revenue deleted successfully');
                        // Update the page without reload
                        updateRevenueDisplay(data.student);
                        // Remove the revenue item from DOM
                        const revenueItem = document.querySelector(
                            `button[onclick*="deleteRevenue(${studentId}, ${revenueId}"]`)?.closest('.revenue-item');
                        if (revenueItem) revenueItem.remove();
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Failed to delete revenue', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error deleting revenue:', error);
                    showToast('An error occurred', 'error');
                });
        }

        function updateRevenueDisplay(studentData) {
            // Update the revenue stats
            const expectedEl = document.querySelector('.revenue-stats-value:first-child');
            const collectedEl = document.querySelector('.revenue-stats-value:nth-child(2)');
            const dueEl = document.querySelector('.revenue-stats-value:last-child');

            if (expectedEl) expectedEl.textContent = `$${parseFloat(studentData.expected_revenue).toFixed(2)}`;
            if (collectedEl) collectedEl.textContent = `$${parseFloat(studentData.received_revenue).toFixed(2)}`;
            if (dueEl) dueEl.textContent = `$${parseFloat(studentData.remaining_due).toFixed(2)}`;

            // Update the revenue card
            const expectedRow = document.querySelector('.revenue-card .revenue-row:first-child strong');
            const receivedRow = document.querySelector('.revenue-card .revenue-row:nth-child(2) strong');
            const pendingRow = document.querySelector('.revenue-card .revenue-row:nth-child(3) strong');

            if (expectedRow) expectedRow.textContent = `$${parseFloat(studentData.expected_revenue).toFixed(2)}`;
            if (receivedRow) receivedRow.textContent = `$${parseFloat(studentData.received_revenue).toFixed(2)}`;
            if (pendingRow) pendingRow.textContent = `$${parseFloat(studentData.remaining_due).toFixed(2)}`;
        }

        // Add event listener for revenue form submission
        document.addEventListener('DOMContentLoaded', function() {
            const revenueForm = document.getElementById('revenueForm');
            if (revenueForm) {
                revenueForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const url = this.action;
                    const method = document.querySelector('input[name="_method"]').value;

                    fetch(url, {
                            method: method === 'PUT' ? 'POST' : 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.message);
                                closeRevenueModal();
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                showToast(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error saving revenue:', error);
                            showToast('An error occurred', 'error');
                        });
                });
            }
        });
        // ========== MINI EDIT MODAL - UPDATED ==========
        function openMiniEditModal() {
            const student = @json($student);

            const modal = document.getElementById('miniEditModal');
            modal.classList.remove('d-none');
            modal.style.display = 'flex';

            document.getElementById('miniEditForm').action = `/crm/students/${student.id}/mini-update`;

            document.getElementById('qe_first_name').value = student.first_name ?? '';
            document.getElementById('qe_last_name').value = student.last_name ?? '';
            document.getElementById('qe_phone').value = student.phone_number ?? '';
            document.getElementById('qe_email').value = student.email ?? '';
            document.getElementById('qe_country').value = student.preferred_country ?? '';
            document.getElementById('qe_applying').value = student.applying_for ?? '';
            document.getElementById('qe_expected_revenue').value = student.expected_revenue ?? '';
            document.getElementById('qe_tags').value = Array.isArray(student.tags) ? student.tags.join(', ') : (student
                .tags ?? '');
        }

        function closeMiniEditModal() {
            const modal = document.getElementById('miniEditModal');
            modal.classList.add('d-none');
            modal.style.display = 'none';
        }

        // Add event listener for mini edit form
        document.addEventListener('DOMContentLoaded', function() {
            const miniEditForm = document.getElementById('miniEditForm');
            if (miniEditForm) {
                miniEditForm.addEventListener('submit', function(e) {
                    // Form submits normally, no need to prevent default
                    showToast('Updating student...', 'success');
                });
            }
        });

        // ========== HELPER FUNCTIONS ==========
        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function showToast(message, type = 'success') {
            let toast = document.getElementById('crmToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'crmToast';
                toast.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    padding: 12px 20px;
                    border-radius: 8px;
                    color: white;
                    font-size: 14px;
                    z-index: 10000;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                    pointer-events: none;
                `;
                document.body.appendChild(toast);
            }

            toast.style.backgroundColor = type === 'error' ? '#ef4444' : '#10b981';
            toast.textContent = message;
            toast.style.opacity = '1';

            setTimeout(() => {
                toast.style.opacity = '0';
            }, 3000);
        }

        // ========== CLOSE MODALS ON OUTSIDE CLICK ==========
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
            }
            if (event.target.classList.contains('tag-modal')) {
                closeTagModal();
            }
        }

        // ========== INITIALIZE REMOVE TAG BUTTONS ==========
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.remove-tag-btn').forEach(btn => {
                btn.onclick = (e) => {
                    e.stopPropagation();
                    const tag = btn.dataset.tag;
                    if (tag) {
                        removeTagFromCard(currentStudentId, tag);
                    }
                };
            });
        });
    </script>
@endpush

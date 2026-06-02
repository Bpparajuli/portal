{{-- resources/views/crm/components/show/styles.blade.php --}}
<style>
    /* CRM-specific components */
    .crm-back-bar {
        background: var(--light);
        border-bottom: 1px solid var(--glass-gradient);
        padding: .5rem .5rem;
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

    .btn-outline-success {
        background: transparent;
        border-color: #066e00;
        color: #066e00;
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

    /* Modern Student Header Styles */
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

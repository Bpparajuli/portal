{{-- resources/views/crm/components/show/styles.blade.php --}}
<style>
    /* CRM-specific components */

    .star-rating {
        display: inline-flex;
        flex-direction: row-reverse;
        gap: 2px;
    }
    .star-rating input { display: none; }
    .star-rating label {
        font-size: 1.1rem;
        color: #d1d5db;
        cursor: pointer;
        line-height: 1;
        transition: color .1s;
    }
    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #f59e0b;
    }

    .btn.btn-outline-purple {
        background: #ede5f8;
        color: #1a0262;
        border: 1px solid #d4c4ec;
        border-radius: 6px;
        font-size: .68rem;
        padding: 4px 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all .12s;
    }
    .btn.btn-outline-purple:hover {
        background: #d4c0f0;
        border-color: #820b5c;
        color: #1a0262;
    }

    .btn.btn-solid-dark {
        background: #1a0262;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: .72rem;
        padding: 5px 16px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all .12s;
    }
    .btn.btn-solid-dark:hover {
        background: #2a0272;
        color: #fff;
    }

    .crm-back-bar {
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: .5rem .75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }

    .crm-back-bar a {
        text-decoration: none;
        color: #6b7280;
        font-size: .85rem;
        font-weight: 500;
    }

    .crm-back-bar a:hover {
        color: var(--accent);
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
        background: linear-gradient(135deg, #1a0262, #820b5c);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        color: white;
    }

    .revenue-total {
        font-size: 1.75rem;
        font-weight: 700;
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
        border-bottom: 1px solid #e5e7eb;
        padding: 0;
    }

    .stage-track {
        display: flex;
        align-items: stretch;
        width: 100%;
        gap: 1px;
        padding: 5px 0;
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
        min-height: 26px;
        border: 1.5px solid #d4c4ec;
        background: #f8f6fc;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        padding: 3px 10px;
        font-size: 9px;
        font-weight: 700;
        line-height: 1.1;
        text-align: center;
        color: #475569;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: pointer;
        clip-path: polygon(0 0, calc(100% - 6px) 0, 100% 50%, calc(100% - 6px) 100%, 0 100%, 6px 50%);
        transition: 0.1s ease;
    }

    .stage-wrapper:first-child .stage-card {
        clip-path: polygon(0 0, calc(100% - 6px) 0, 100% 50%, calc(100% - 6px) 100%, 0 100%);
    }

    .stage-card:hover {
        filter: brightness(1.05);
        background: #ede5f8;
        border-color: #820b5c;
    }

    .stage-card.current {
        background: linear-gradient(135deg, #820b5c, #1a0262);
        color: #fff;
        font-weight: 700;
        border-color: transparent;
    }
    .stage-card.current:hover {
        background: linear-gradient(135deg, #9a0d6c, #2a0272);
        border-color: transparent;
    }

    .stage-card.passed {
        background: #ecfdf5;
        color: #065f46;
        border: 1.5px solid #6ee7b7;
    }
    .stage-card.passed:hover {
        background: #d1fae5;
        border-color: #10b981;
    }

    .stage-card.passed::after {
        content: "✓";
        margin-left: 3px;
        font-size: 8px;
        font-weight: 700;
        color: #059669;
    }

    .stage-card.pending {
        background: #f8f6fc;
        color: #6b5b8a;
        border: 1.5px solid #e0d8ec;
    }
    .stage-card.pending:hover {
        background: #ede5f8;
        border-color: #820b5c;
    }

    .stage-title {
        display: block;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stage-days {
        font-size: 7px;
        opacity: .7;
        background: rgba(255,255,255,.2);
        padding: 0 3px;
        border-radius: 2px;
        line-height: 1.4;
    }

    .crm-body {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 16px;
        padding: 14px;
    }

    @media (max-width:992px) {
        .crm-body {
            grid-template-columns: 1fr;
        }
    }

    .crm-section {
        background: white;
        border: 1px solid #e8e5ee;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 12px;
        box-shadow: 0 1px 3px rgba(26,2,98,.04);
    }

    .crm-section-header {
        padding: 10px 14px;
        font-size: .72rem;
        font-weight: 700;
        color: #374151;
        background: #faf9fc;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e8e5ee;
    }

    .crm-section-body {
        padding: 12px 14px;
    }

    .note-display {
        white-space: pre-wrap;
        font-size: .7rem;
        color: #1e293b;
        line-height: 1.5;
    }

    .note-item {
        padding: 10px 12px;
        border: 1px solid #e8e5ee;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #fff;
        transition: box-shadow .12s;
    }
    .note-item:hover { box-shadow: 0 2px 6px rgba(26,2,98,.05); }

    .note-item.pinned {
        border-left: 3px solid #f59e0b;
        background: #fffbeb;
    }

    .note-item.log-entry {
        border-left: 3px solid var(--accent);
        background: #f4edfb;
    }

    .note-pin-btn,
    .edit-note-btn {
        background: none;
        border: none;
        font-size: .75rem;
        cursor: pointer;
        color: #94a3b8;
        padding: 2px 4px;
        border-radius: 4px;
        transition: all .12s;
    }

    .note-pin-btn:hover {
        color: #820b5c;
        background: #ede5f8;
    }

    .edit-note-btn:hover {
        color: #1a0262;
        background: #ede5f8;
    }

    .note-actions {
        display: flex;
        gap: 0.3rem;
    }

    .crm-tabs {
        display: flex;
        border-bottom: 1px solid #e8e5ee;
        padding: 0 14px;
        background: #faf9fc;
        gap: 2px;
    }

    .crm-tab {
        padding: 8px 14px;
        font-size: .72rem;
        font-weight: 600;
        border: none;
        background: none;
        cursor: pointer;
        color: #94a3b8;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        transition: all .12s;
    }

    .crm-tab:hover {
        color: #1a0262;
        background: #ede5f8;
    }

    .crm-tab.active {
        color: #1a0262;
        border-bottom-color: #820b5c;
    }

    .tasks-container {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .task-box {
        border: 1px solid #e8e5ee;
        border-radius: 8px;
        padding: 6px 8px;
        background: #faf9fc;
        margin-bottom: 8px;
    }

    .task-box-header {
        font-size: .72rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
        padding-bottom: 4px;
        border-bottom: 1px solid #e8e5ee;
    }

    .task-item {
        display: flex;
        align-items: flex-start;
        gap: 0.35rem;
        padding: 0.3rem 0.4rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        margin-bottom: 0.25rem;
        font-size: 0.76rem;
        transition: all 0.1s;
        background: white;
    }

    .task-item:hover {
        box-shadow: var(--shadow-sm);
    }

    .task-item.overdue {
        border-left: 3px solid var(--danger);
        background: var(--danger-soft);
    }

    .task-item.today {
        border-left: 3px solid var(--warning);
        background: var(--warning-soft);
    }

    .task-item.upcoming {
        border-left: 3px solid var(--success);
        background: var(--success-soft);
    }

    .task-title {
        font-weight: 700;
        color: var(--text-color);
        font-size: 0.78rem;
    }

    .task-title p {
        margin: 0;
    }

    .task-meta {
        font-size: 0.65rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .task-description {
        font-size: 0.72rem;
        color: var(--text-color);
        margin-top: 4px;
        padding: 4px 6px;
        background: #f8fafc;
        border-radius: 4px;
    }

    .task-actions {
        margin-left: auto;
        display: flex;
        gap: 0.2rem;
        flex-shrink: 0;
    }

    .priority-high {
        color: var(--danger);
        font-weight: 600;
    }

    .priority-medium {
        color: var(--warning-dark);
        font-weight: 600;
    }

    .priority-low {
        color: var(--success-dark);
        font-weight: 600;
    }

    .staff-avatar-sm {
        width: 18px;
        height: 18px;
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
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(2px);
        z-index: 1000;
        justify-content: center;
        align-items: center;
        padding: 1rem;
    }

    .modal-content {
        background: #fff;
        border-radius: 14px;
        max-width: 500px;
        width: 100%;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 25px 60px rgba(15, 23, 42, 0.2), 0 0 0 1px rgba(0,0,0,0.04);
        animation: modalIn .2s ease-out;
    }

    @keyframes modalIn {
        from { opacity: 0; transform: scale(.96) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-content.large {
        max-width: 640px;
    }

    .modal-header {
        padding: 0.85rem 1.2rem;
        border-bottom: 1px solid #e9edf2;
        font-weight: 700;
        font-size: 0.9rem;
        background: #fff;
        background-image: linear-gradient(135deg, #fafbfc 0%, #fff 100%);
        border-radius: 14px 14px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .modal-header button:hover {
        background: #f1f3f7;
        border-radius: 6px;
    }

    .modal-body {
        padding: 1rem 1.2rem;
    }

    .modal-footer {
        padding: 0.75rem 1.2rem;
        border-top: 1px solid #e9edf2;
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
        background: #fafbfc;
        border-radius: 0 0 14px 14px;
        position: sticky;
        bottom: 0;
    }

    .form-group {
        margin-bottom: 0.75rem !important;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.25rem;
        font-weight: 600;
        font-size: 0.78rem;
        color: #1e293b;
    }

    .form-control,
    .form-select {
        width: 100%;
        padding: 0.35rem 0.55rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 0.8rem;
        height: auto;
        min-height: 32px;
        line-height: 1.4;
    }

    .form-control:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary);
    }

    .activity-item {
        padding: 0.4rem 0.5rem;
        border-bottom: 1px solid var(--border);
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item .act-desc {
        font-size: 0.74rem;
        color: var(--text-color);
        margin-top: 4px;
        background: #f8fafc;
        border-radius: 4px;
        padding: 4px 6px;
        border-left: 3px solid var(--border);
    }

    .crm-sidebar {
        position: sticky;
        top: 60px;
        align-self: start;
        max-height: calc(100vh - 60px);
        overflow-y: auto;
    }

    .sidebar-field {
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid #f0eef5;
    }
    .sidebar-field:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .sidebar-field label {
        font-size: .6rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #94a3b8;
        display: block;
        margin-bottom: 1px;
    }
    .sidebar-field .val {
        font-size: .76rem;
        color: #1e293b;
        font-weight: 500;
    }

    .read-only-badge {
        background: #fef3c7;
        color: #92400e;
        font-size: 0.65rem;
        border-radius: 4px;
        padding: 1px 4px;
        font-weight: 600;
    }

    .admin-badge {
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 0.62rem;
        border-radius: 4px;
        padding: 1px 4px;
        font-weight: 600;
        margin-left: 4px;
    }

    .btn {
        padding: 0.3rem 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.78rem;
        cursor: pointer;
        border: 1px solid transparent;
        display: inline-block;
        text-align: center;
        text-decoration: none;
        height: 28px;
        line-height: 1.4;
    }

    .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
        height: 22px;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .btn-danger {
        background-color: var(--danger);
        color: white;
        border-color: var(--danger);
    }

    .btn-success {
        background-color: var(--success);
        color: white;
        border-color: var(--success);
    }

    .btn-warning {
        background-color: var(--warning);
        color: white;
        border-color: var(--warning);
    }

    .btn-outline-secondary {
        background: transparent;
        border-color: var(--border);
        color: var(--text-color);
    }

    .btn-outline-primary {
        background: transparent;
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-outline-danger {
        background: transparent;
        border-color: var(--danger);
        color: var(--danger);
    }

    .btn-outline-warning {
        background: transparent;
        border-color: var(--warning);
        color: var(--warning);
    }

    .btn-outline-success {
        background: transparent;
        border-color: var(--success);
        color: var(--success);
    }

    .w-100 {
        width: 100%;
    }

    .mt-3 {
        margin-top: 0.8rem;
    }

    .mt-4 {
        margin-top: 1.2rem;
    }

    .mb-3 {
        margin-bottom: 0.8rem;
    }

    .mb-0 {
        margin-bottom: 0;
    }

    .gap-2 {
        gap: 4px;
    }

    .gap-3 {
        gap: 8px;
    }

    .text-muted {
        color: var(--text-muted);
    }

    .text-center {
        text-align: center;
    }

    .py-3 {
        padding-top: 0.6rem;
        padding-bottom: 0.6rem;
    }

    .py-4 {
        padding-top: 0.8rem;
        padding-bottom: 0.8rem;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin: -2px;
    }

    .col-md-6 {
        flex: 0 0 50%;
        padding: 2px;
    }

    .col-sm-6 {
        flex: 0 0 50%;
        padding: 2px;
    }

    .col-12 {
        flex: 0 0 100%;
        padding: 2px;
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
        margin-left: 0.35rem;
    }

    .reschedule-buttons {
        display: flex;
        gap: 4px;
        margin-top: 4px;
    }

    .tag-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 10001;
        align-items: center;
        justify-content: center;
    }

    .tag-modal.active {
        display: flex;
    }

    .tag-modal-content {
        background: white;
        border-radius: var(--radius-sm);
        padding: 0.6rem;
        width: 90%;
        max-width: 320px;
    }

    .tag-modal-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.4rem;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .tag-modal-close {
        cursor: pointer;
        font-size: 1.1rem;
        line-height: 1;
    }

    .tag-input-group input {
        width: 100%;
        padding: 0.3rem 0.5rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        margin-bottom: 0.4rem;
        font-size: 0.78rem;
    }

    .suggested-tags-list {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 4px;
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
        padding: .75rem;
        background: linear-gradient(135deg, #ede5f8 0%, #f4edfb 100%);
    }

    .student-header-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
        border: 1px solid #e5e7eb;
    }

    .student-header-content {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 1rem;
    }

    /* Left Column */
    .header-left-col {
        display: flex;
        gap: 1rem;
        flex: 2;
        min-width: 280px;
    }

    .student-avatar-wrapper { flex-shrink: 0; }

    .student-avatar-img {
        width: 80px; height: 80px;
        object-fit: cover;
        border-radius: 12px;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        background: white;
    }

    .student-avatar-placeholder {
        width: 80px; height: 80px;
        border-radius: 12px;
        background: linear-gradient(135deg, #820b5c, #1a0262);
        display: flex;
        align-items: center; justify-content: center;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }
    .student-avatar-placeholder i { font-size: 2rem; color: white; }

    .student-identity { flex: 1; }

    .student-name {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0 0 .5rem 0;
        color: #0f172a;
    }

    .student-badges {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-bottom: .5rem;
    }

    .badge-applying,
    .badge-country {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .2rem .55rem;
        background: #f1f5f9;
        border-radius: 16px;
        font-size: .68rem;
        font-weight: 500;
        color: #334155;
    }

    .student-contact-info {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        margin-bottom: .4rem;
    }
    .student-contact-info span {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .75rem;
        color: #475569;
    }
    .student-contact-info i { width: .85rem; color: #94a3b8; }

    .student-agent-info {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: #ede5f8;
        padding: .2rem .6rem;
        border-radius: 16px;
        font-size: .72rem;
        color: #1a0262;
    }

    .agent-logo-sm {
        width: 18px; height: 18px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #c8b8e0;
    }

    /* Middle Column */
    .header-middle-col {
        flex: 1;
        min-width: 200px;
        background: #f8fafc;
        border-radius: 12px;
        padding: .75rem;
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }

    .tags-section { flex: 1; }

    .tags-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .5rem;
    }
    .tags-header span {
        font-size: .68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #64748b;
    }

    .tags-container {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }

    .tag-item {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        background: white;
        border-radius: 16px;
        padding: .15rem .4rem .15rem .55rem;
        font-size: .68rem;
        font-weight: 500;
        color: #1e293b;
        border: 1px solid #e2e8f0;
    }

    .no-tags { font-size: .68rem; color: #94a3b8; font-style: italic; }

    .btn-quick-edit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        background: white;
        border: 1px solid #e5e7eb;
        padding: .35rem .75rem;
        border-radius: 10px;
        font-size: .72rem;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        transition: all .15s;
        width: 100%;
    }
    .btn-quick-edit:hover {
        background: #f9fafb;
        border-color: #d1d5db;
    }

    /* Right Column */
    .header-right-col {
        flex: 1.5;
        min-width: 180px;
    }

    .revenue-card-modern {
        background: linear-gradient(145deg, #1a0262, #820b5c);
        border-radius: 12px;
        padding: .75rem;
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }

    .revenue-header {
        display: flex;
        align-items: center;
        gap: .4rem;
        color: #fde68a;
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 600;
    }
    .revenue-header i { font-size: .75rem; }

    .stat-label {
        display: block;
        font-size: .68rem;
        color: #e8d5f5;
        margin-bottom: .1rem;
    }

    .stat-value {
        font-size: .85rem;
        font-weight: 700;
        color: white;
    }

    .btn-add-revenue {
        width: 100%;
        padding: .35rem .5rem;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,.2);
        background: rgba(255,255,255,.08);
        color: #fde68a;
        font-size: .7rem;
        font-weight: 500;
        cursor: pointer;
        transition: all .12s;
    }
    .btn-add-revenue:hover {
        background: rgba(255,255,255,.15);
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
        background: #5c2d8a;
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

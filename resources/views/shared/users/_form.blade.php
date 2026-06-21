@php $isEdit = isset($user) && $user->exists; @endphp

{{-- Progress Steps --}}
<div class="d-flex justify-content-center mb-5">
    <div class="d-flex align-items-center gap-0" style="max-width: 500px; width: 100%;">
        @foreach ([
            ['icon' => 'fa-user-circle', 'label' => 'Basic Info', 'color' => 'primary'],
            ['icon' => 'fa-file-alt', 'label' => 'Documents', 'color' => 'success'],
            ['icon' => 'fa-lock', 'label' => 'Security', 'color' => 'warning'],
        ] as $i => $step)
        <div class="d-flex align-items-center flex-grow-1">
            <div class="d-flex flex-column align-items-center" style="position:relative;">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                    style="width:40px;height:40px;background:var(--{{ $step['color'] }});color:#fff;font-size:16px;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                    <i class="fas {{ $step['icon'] }}"></i>
                </div>
                <span class="small mt-1 fw-semibold" style="color:var(--{{ $step['color'] }});font-size:10px;text-transform:uppercase;letter-spacing:0.04em;">{{ $step['label'] }}</span>
            </div>
            @if($i < 2)
            <div class="flex-grow-1 mx-2" style="height:2px;background:linear-gradient(90deg, var(--{{ $step['color'] }}) 0%, var(--{{ ['primary','success','warning'][$i+1] }}) 100%);border-radius:2px;"></div>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- Basic Information --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="px-4 py-3 d-flex align-items-center gap-3" style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(255,255,255,0.2);">
            <i class="fas fa-user-circle text-white"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-0 text-white">Basic Information</h6>
            <small class="text-white-50">Personal and business details</small>
        </div>
        <div class="ms-auto">
            <div class="form-check form-switch d-flex align-items-center gap-2 mb-0">
                <input type="hidden" name="status" value="0">
                <input class="form-check-input" type="checkbox" name="status"
                    id="statusToggle" value="1" {{ $isEdit ? ($user->active ? 'checked' : '') : 'checked' }}
                    style="width:2.5rem;height:1.25rem;cursor:pointer;">
                <label class="form-check-label small fw-medium text-white" for="statusToggle" id="statusLabel">
                    <span class="fw-semibold">
                        <i class="fas fa-circle me-1" style="font-size:0.6rem;"></i>
                        {{ $isEdit && !$user->active ? 'Inactive' : 'Active' }}
                    </span>
                </label>
            </div>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="font-size:11px;letter-spacing:0.05em;color:var(--primary);">Business Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-building" style="color:var(--primary);"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 @error('business_name') is-invalid @enderror"
                        name="business_name" value="{{ old('business_name', $user->business_name ?? '') }}"
                        placeholder="Enter business name" {{ $isEdit ? '' : 'required' }}>
                </div>
                @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="font-size:11px;letter-spacing:0.05em;color:var(--primary);">Owner Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user-tie" style="color:var(--primary);"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 @error('owner_name') is-invalid @enderror"
                        name="owner_name" value="{{ old('owner_name', $user->owner_name ?? '') }}"
                        placeholder="Enter owner name" {{ $isEdit ? '' : 'required' }}>
                </div>
                @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="font-size:11px;letter-spacing:0.05em;color:var(--primary);">User Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user" style="color:var(--primary);"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror"
                        name="name" value="{{ old('name', $user->name ?? '') }}"
                        placeholder="Enter username" {{ $isEdit ? '' : 'required' }}>
                </div>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="font-size:11px;letter-spacing:0.05em;color:var(--primary);">Email <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope" style="color:var(--primary);"></i></span>
                    <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email', $user->email ?? '') }}" required
                        placeholder="Enter email address">
                </div>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="font-size:11px;letter-spacing:0.05em;color:var(--primary);">Contact Number</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone" style="color:var(--primary);"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 @error('contact') is-invalid @enderror"
                        name="contact" value="{{ old('contact', $user->contact ?? '') }}"
                        placeholder="Enter contact number">
                </div>
                @error('contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="font-size:11px;letter-spacing:0.05em;color:var(--primary);">Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-map-marker-alt" style="color:var(--primary);"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 @error('address') is-invalid @enderror"
                        name="address" value="{{ old('address', $user->address ?? '') }}"
                        placeholder="Enter address">
                </div>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small text-uppercase" style="font-size:11px;letter-spacing:0.05em;color:var(--primary);">Role <span class="text-danger">*</span></label>
                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="">Select user type</option>
                    @foreach (['admin', 'agent', 'staff', 'university', 'student'] as $r)
                        <option value="{{ $r }}" {{ old('role', $user->role ?? '') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div id="parentFieldContainer" class="col-md-4" style="display: {{ (old('role', $user->role ?? '') === 'staff') ? 'block' : 'none' }};">
                <label class="form-label fw-semibold small text-uppercase" style="font-size:11px;letter-spacing:0.05em;color:var(--primary);">Parent <span class="text-danger">*</span></label>
                <select name="parent_id" id="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                    <option value="">Select parent</option>
                    @if (isset($parents) && $parents->count())
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}"
                                {{ old('parent_id', $user->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->business_name ?? $parent->name }} ({{ ucfirst($parent->role) }})
                            </option>
                        @endforeach
                    @endif
                </select>
                <small class="text-muted">Parent company (Admin/Agent)</small>
                @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small text-uppercase" style="font-size:11px;letter-spacing:0.05em;color:var(--primary);">Agreement Status</label>
                <select name="agreement_status" class="form-select @error('agreement_status') is-invalid @enderror">
                    @foreach (['not_uploaded' => 'Not Uploaded', 'uploaded' => 'Uploaded', 'verified' => 'Verified'] as $val => $label)
                        <option value="{{ $val }}" {{ old('agreement_status', $user->agreement_status ?? 'not_uploaded') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('agreement_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Agent Features (shown when role is agent) --}}
<div id="agentFeaturesSection" class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="display:{{ (old('role', $user->role ?? '') === 'agent') ? 'block' : 'none' }};">
    <div class="px-4 py-3 d-flex align-items-center gap-3" style="background:linear-gradient(135deg, var(--info) 0%, #2563eb 100%);">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(255,255,255,0.2);">
            <i class="fas fa-user-tie text-white"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-0 text-white">Agent Features &amp; Limits</h6>
            <small class="text-white-50">Configure per-agent limits and feature access</small>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-12">
                <label class="form-label fw-semibold small">Select Plan / Package</label>
                <div class="d-flex flex-wrap gap-2" id="planSelector">
                    @php
                        $planColors = ['secondary', 'info', 'success', 'warning', 'danger', 'primary'];
                        $savedPlans = \App\Models\Setting::getValue('agent_plans', []);
                        $plans = array_merge(
                            [['id' => '', 'name' => 'Custom', 'staff_limit' => 1, 'student_limit' => 0, 'crm_enabled' => false]],
                            $savedPlans
                        );
                        $currentPlan = old('subscription_plan', $user->subscription_plan ?? '');
                    @endphp
                    @foreach($plans as $pi => $plan)
                    <button type="button" class="btn btn-outline-{{ $planColors[$pi] ?? 'secondary' }} btn-sm plan-option {{ $currentPlan === $plan['id'] ? 'active' : '' }}"
                        data-plan="{{ $plan['id'] }}" data-staff="{{ $plan['staff_limit'] ?? 1 }}" data-students="{{ $plan['student_limit'] ?? 0 }}" data-crm="{{ ($plan['crm_enabled'] ?? false) ? '1' : '0' }}">
                        <span class="fw-bold d-block">{{ $plan['name'] }}</span>
                        <small>{{ $plan['staff_limit'] ?? 1 }} staff, {{ $plan['student_limit'] ?? 0 }} students{{ ($plan['crm_enabled'] ?? false) ? ', CRM' : '' }}</small>
                    </button>
                    @endforeach
                </div>
                <input type="hidden" name="subscription_plan" id="subscriptionPlan" value="{{ $currentPlan }}">
            </div>
        </div>
        <div class="row g-4 border-top pt-4">
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Max Staff Members</label>
                <input type="number" name="max_staff" id="agentMaxStaff" class="form-control" value="{{ old('max_staff', $user->max_staff ?? 1) }}" min="0" max="100">
                <small class="text-muted">Number of staff accounts this agent can create</small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Max Students</label>
                <input type="number" name="max_students" id="agentMaxStudents" class="form-control" value="{{ old('max_students', $user->max_students ?? 0) }}" min="0" max="10000">
                <small class="text-muted">Total student limit (0 = unlimited)</small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small">CRM Access</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="paid_crm" value="1" role="switch" id="crmToggle" {{ old('paid_crm', $user->paid_crm ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="crmToggle">Enable CRM module for this agent</label>
                </div>
                <small class="text-muted d-block mt-1">Grants access to CRM features</small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const planSelector = document.getElementById('planSelector');
    if (!planSelector) return;
    planSelector.addEventListener('click', function(e) {
        const btn = e.target.closest('.plan-option');
        if (!btn) return;
        planSelector.querySelectorAll('.plan-option').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('subscriptionPlan').value = btn.dataset.plan;
        // If not custom, auto-fill the fields
        if (btn.dataset.plan !== '') {
            document.getElementById('agentMaxStaff').value = btn.dataset.staff;
            document.getElementById('agentMaxStudents').value = btn.dataset.students;
            document.getElementById('crmToggle').checked = btn.dataset.crm === '1';
        }
    });
});
</script>
@endpush

{{-- Documents & Files --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="px-4 py-3 d-flex align-items-center gap-3" style="background:linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(255,255,255,0.2);">
            <i class="fas fa-file-alt text-white"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-0 text-white">Documents &amp; Files</h6>
            <small class="text-white-50">Upload business documents and registrations</small>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            @php
                $docs = [
                    ['id' => 'logoPreview',  'name' => 'business_logo', 'label' => 'Business Logo',       'placeholder' => 'Logo',        'existing' => $user->business_logo ?? null],
                    ['id' => 'regPreview',  'name' => 'registration',  'label' => 'Registration File',   'placeholder' => 'File',       'existing' => $user->registration ?? null],
                    ['id' => 'panPreview',  'name' => 'pan',           'label' => 'PAN Registration',    'placeholder' => 'No+File',    'existing' => $user->pan ?? null],
                    ['id' => 'agreementPreview', 'name' => 'agreement_file', 'label' => 'Agreement File','placeholder' => 'No+File',    'existing' => $user->agreement_file ?? null],
                ];
            @endphp
            @foreach ($docs as $doc)
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100" style="background:#fafafa;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--success)';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.1)'" onmouseout="this.style.borderColor='';this.style.boxShadow=''">
                    <label class="form-label fw-semibold small mb-3" style="color:var(--success-dark);">
                        <i class="fas fa-file me-1"></i> {{ $doc['label'] }}
                    </label>
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0">
                            @if ($doc['existing'])
                                <a href="{{ Storage::url($doc['existing']) }}" target="_blank"
                                   class="previewable" data-url="{{ Storage::url($doc['existing']) }}"
                                   data-filename="{{ $doc['label'] }}" data-preview-type="document">
                                    <img id="{{ $doc['id'] }}" src="{{ Storage::url($doc['existing']) }}"
                                         class="rounded border shadow-sm" width="70" height="70" style="object-fit:cover;">
                                </a>
                            @else
                                <div class="rounded border d-flex align-items-center justify-content-center bg-white" style="width:70px;height:70px;">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <input type="file" name="{{ $doc['name'] }}" class="form-control form-control-sm"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,image/*"
                                onchange="previewImage(this, '{{ $doc['id'] }}')">
                            <small class="text-muted">Allowed: Any file type &mdash; Max 10MB</small>
                            @error($doc['name'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @if ($doc['existing'] && $doc['name'] === 'agreement_file')
                            <button type="button" class="btn btn-outline-danger btn-sm flex-shrink-0"
                                onclick="deleteAgreement('{{ route('admin.users.agreement.delete', $user->slug ?? '') }}', '{{ $user->business_name ?? '' }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Security Settings --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="px-4 py-3 d-flex align-items-center gap-3" style="background:linear-gradient(135deg, var(--warning) 0%, var(--warning-dark) 100%);">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(255,255,255,0.2);">
            <i class="fas fa-lock text-white"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-0 text-white">Security Settings</h6>
            <small class="text-white-50">{{ !$isEdit ? 'Set a strong password for the new account' : 'Update account password if needed' }}</small>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="p-3 rounded-3 mb-4 d-flex align-items-center gap-3" style="background:var(--warning-soft);border-left:3px solid var(--warning);">
            <i class="fas fa-info-circle" style="color:var(--warning-dark);font-size:1.2rem;"></i>
            <small style="color:var(--warning-dark);">
                {{ !$isEdit ? 'Password is required for new users. Use at least 6 characters with a mix of letters, numbers, and symbols.' : 'Leave password fields blank if you do not want to change the current password.' }}
            </small>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="font-size:11px;letter-spacing:0.05em;color:var(--warning-dark);">
                    {{ !$isEdit ? 'Password' : 'New Password' }} @if(!$isEdit)<span class="text-danger">*</span>@endif
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-key" style="color:var(--warning);"></i></span>
                    <input type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password" id="passwordField"
                        placeholder="{{ !$isEdit ? 'Enter password' : 'Enter new password' }}"
                        {{ !$isEdit ? 'required' : '' }}>
                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                        <i id="passwordIcon" class="fas fa-eye"></i>
                    </span>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @if($isEdit)<small class="text-muted">Minimum 6 characters</small>@endif
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="font-size:11px;letter-spacing:0.05em;color:var(--warning-dark);">
                    Confirm Password @if(!$isEdit)<span class="text-danger">*</span>@endif
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-check-circle" style="color:var(--warning);"></i></span>
                    <input type="password"
                        class="form-control @error('password_confirmation') is-invalid @enderror"
                        name="password_confirmation" id="confirmPassword"
                        placeholder="{{ !$isEdit ? 'Confirm password' : 'Confirm new password' }}"
                        {{ !$isEdit ? 'required' : '' }}>
                    <span class="input-group-text"><span id="passwordMatchIcon"></span></span>
                    @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Footer Actions --}}
<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
    <div>
        @if($isEdit)
        <a href="{{ route('admin.users.show', $user->slug) }}" class="btn btn-outline-secondary px-4">
            <i class="fas fa-arrow-left me-2"></i>Cancel
        </a>
        @else
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4">
            <i class="fas fa-arrow-left me-2"></i>Cancel
        </a>
        @endif
    </div>
    <button type="submit" class="btn px-5" style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);color:#fff;border:none;">
        <i class="fas fa-save me-2"></i>{{ $isEdit ? 'Update User' : 'Create User' }}
    </button>
</div>

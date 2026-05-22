{{-- resources/views/crm/partials/_add_student_modal.blade.php --}}

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--active); color: white;">
                <h5 class="modal-title" id="addStudentModalLabel">
                    <i class="fas fa-user-plus"></i> Quick Add Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div id="modalMessage"></div>

                <form id="quickAddStudentForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label required">Full Name</label>
                            <input type="text" class="form-control" id="modal_full_name" name="full_name" required
                                placeholder="Enter student's full name">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Phone Number</label>
                            <input type="tel" class="form-control" id="modal_phone_number" name="phone_number"
                                required placeholder="+1234567890">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="modal_email" name="email"
                                placeholder="student@example.com">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preferred Country</label>
                            <select class="form-select" id="modal_country" name="country">
                                <option value="">Select Country</option>
                                <option value="Australia">🇦🇺 Australia</option>
                                <option value="Canada">🇨🇦 Canada</option>
                                <option value="United Kingdom">🇬🇧 United Kingdom</option>
                                <option value="USA">🇺🇸 USA</option>
                                <option value="New Zealand">🇳🇿 New Zealand</option>
                                <option value="Germany">🇩🇪 Germany</option>
                                <option value="Ireland">🇮🇪 Ireland</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preferred Course</label>
                            <input type="text" class="form-control" id="modal_course" name="course"
                                placeholder="e.g., Business, IT, Engineering">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Qualification</label>
                            <select class="form-select" id="modal_qualification" name="qualification">
                                <option value="">Select Highest Qualification</option>
                                <option value="High School">High School</option>
                                <option value="Bachelor's Degree">Bachelor's Degree</option>
                                <option value="Master's Degree">Master's Degree</option>
                                <option value="PhD">PhD</option>
                                <option value="Diploma">Diploma</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Assign to Agent</label>
                            <select class="form-select" id="modal_agent_id" name="agent_id">
                                <option value="">Auto-assign</option>
                                @foreach ($agents ?? [] as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->business_name ?? $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitQuickStudentBtn">
                    <i class="fas fa-save"></i> Add Student
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const submitBtn = document.getElementById('submitQuickStudentBtn');
        const form = document.getElementById('quickAddStudentForm');
        const messageDiv = document.getElementById('modalMessage');

        if (submitBtn) {
            submitBtn.addEventListener('click', async function() {
                // Disable button to prevent double submission
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

                // Clear previous message
                messageDiv.innerHTML = '';

                // Get form data
                const formData = {
                    full_name: document.getElementById('modal_full_name').value,
                    phone_number: document.getElementById('modal_phone_number').value,
                    email: document.getElementById('modal_email').value,
                    country: document.getElementById('modal_country').value,
                    course: document.getElementById('modal_course').value,
                    qualification: document.getElementById('modal_qualification').value,
                    agent_id: document.getElementById('modal_agent_id').value || null,
                    source: 'crm_modal',
                    return_format: 'json'
                };

                // Validate required fields
                if (!formData.full_name || !formData.phone_number) {
                    messageDiv.innerHTML =
                        '<div class="alert alert-danger">❌ Please fill in all required fields (Name and Phone)</div>';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Student';
                    return;
                }

                try {
                    const response = await fetch('/api/student/intake', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        messageDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> ✅ Student Added Successfully!<br>
                            <strong>Student ID:</strong> ${result.student.id}<br>
                            <strong>Name:</strong> ${result.student.name}<br>
                            <strong>Phone:</strong> ${result.student.phone}
                        </div>
                    `;

                        // Reset form
                        form.reset();

                        // Close modal after 2 seconds and refresh page
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document
                                .getElementById('addStudentModal'));
                            if (modal) modal.hide();
                            // Refresh the page to show new student
                            location.reload();
                        }, 2000);
                    } else {
                        messageDiv.innerHTML =
                            `<div class="alert alert-danger">❌ Error: ${result.message || 'Please try again'}</div>`;
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Student';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    messageDiv.innerHTML =
                        '<div class="alert alert-danger">❌ Network error. Please try again.</div>';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Student';
                }
            });
        }
    });
</script>

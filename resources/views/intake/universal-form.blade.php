{{-- resources/views/intake/universal-form.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quick Student Registration</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--active);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            margin-top: 0;
            color: #333;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin: 8px 0 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }

        button {
            background: #1a0262;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #4338ca;
        }

        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        small {
            color: #666;
            display: block;
            margin-top: -15px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>📝 Register as Student</h1>
        <p>Fill out this form to get started</p>

        <div id="message"></div>

        <form id="studentForm">
            @csrf
            <input type="text" id="full_name" placeholder="Full Name *" required>
            <input type="tel" id="phone_number" placeholder="Phone Number *" required>
            <input type="email" id="email" placeholder="Email Address">
            <input type="text" id="address" placeholder="Permanent Address">
            <input type="text" id="country" placeholder="Preferred Country">
            <input type="text" id="course" placeholder="Preferred Course">
            <select id="qualification">
                <option value="">Highest Qualification</option>
                <option>High School</option>
                <option>Bachelor's Degree</option>
                <option>Master's Degree</option>
                <option>PhD</option>
            </select>
            <button type="submit">Submit Registration →</button>
        </form>
        <br>
        <small class="fw-bold text-danger">We'll contact you within 24 hours</small>
    </div>

    <script>
        document.getElementById('studentForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = '<div class="success">Submitting...</div>';

            const formData = {
                full_name: document.getElementById('full_name').value,
                phone_number: document.getElementById('phone_number').value,
                email: document.getElementById('email').value,
                country: document.getElementById('country').value,
                course: document.getElementById('course').value,
                qualification: document.getElementById('qualification').value,
                source: 'web_form'
            };

            try {
                const response = await fetch('/api/student/intake', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                // Check if response is a redirect (HTML)
                if (response.redirected) {
                    // Browser will automatically follow redirect
                    window.location.href = response.url;
                    return;
                }

                const result = await response.json();

                if (result.success) {
                    messageDiv.innerHTML =
                        `<div class="success">✅ ${result.message}<br>Student ID: ${result.student.id}<br>Redirecting...</div>`;

                    // Redirect to thank you page after 1 second
                    setTimeout(() => {
                        window.location.href = '/thank-you';
                    }, 1000);
                } else {
                    messageDiv.innerHTML =
                        `<div class="error">❌ Error: ${result.message || 'Please try again'}</div>`;
                }
            } catch (error) {
                console.error('Error:', error);
                messageDiv.innerHTML = `<div class="error">❌ Network error. Please try again.</div>`;
            }
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Registration Successful</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .thankyou-container {
            max-width: 600px;
            width: 100%;
            animation: fadeIn 0.5s ease-out;
        }

        .thankyou-card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            animation: scaleIn 0.3s ease-out 0.2s both;
        }

        .success-icon svg {
            width: 45px;
            height: 45px;
            color: white;
        }

        h1 {
            color: #1a1f36;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .message {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f3f4f6;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            text-align: left;
        }

        .info-title {
            font-weight: 600;
            color: #1a1f36;
            margin-bottom: 15px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6b7280;
            font-size: 14px;
        }

        .info-value {
            color: #1a1f36;
            font-weight: 600;
            font-size: 14px;
        }

        .next-steps {
            text-align: left;
            margin: 25px 0;
        }

        .next-steps h3 {
            color: #1a1f36;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding: 10px;
            background: #f9fafb;
            border-radius: 10px;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background: #4f46e5;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .step-text {
            color: #374151;
            font-size: 14px;
        }

        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-primary {
            background: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    <div class="thankyou-container">
        <div class="thankyou-card">
            <div class="success-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1>Thank You! 🎉</h1>

            <div class="message">
                Your registration has been submitted successfully.<br>
                Our team will review your information and contact you soon.
            </div>

            <div class="info-box">
                <div class="info-title">📋 Registration Summary</div>
                <div class="info-item">
                    <span class="info-label">Reference Number:</span>
                    <span class="info-value">#{{ $student->id ?? 'STU' . date('Ymd') . rand(100, 999) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">{{ $student->full_name ?? 'Student' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone Number:</span>
                    <span class="info-value">{{ $student->phone_number ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Submitted On:</span>
                    <span class="info-value">{{ now()->format('d M Y, h:i A') }}</span>
                </div>
            </div>

            <div class="next-steps">
                <h3>📌 What Happens Next?</h3>
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-text">Our team will review your application within 24 hours</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-text">You'll receive a confirmation call/WhatsApp message</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-text">We'll guide you through the admission process</div>
                </div>
            </div>

            <div class="buttons">
                <a href="{{ url('/') }}" class="btn btn-primary">
                    🏠 Back to Home
                </a>
                @if (isset($student) && $student->id)
                    <a href="{{ route('student.intake.form') }}" class="btn btn-secondary">
                        📝 Register Another
                    </a>
                @endif
            </div>
        </div>
    </div>
</body>

</html>

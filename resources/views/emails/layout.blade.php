<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? 'Notification' }}</title>
    <style>
        /* Base styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e6e9ee;
        }

        .email-header {
            padding: 20px;
            text-align: center;
            background: #1a0262;
        }

        .email-header img {
            width: 120px;
        }

        .email-body {
            padding: 20px;
        }

        .email-body h2 {
            color: #820b5c;
            margin-top: 0;
        }

        .email-body p {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.5;
            margin: 10px 0;
        }

        .email-button {
            text-align: center;
            margin: 20px 0;
        }

        .email-button a {
            background: #820b5c;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
        }

        .email-footer {
            padding: 10px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
            background: #f0f0f0;
        }

        /* Mobile responsiveness */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 95% !important;
                margin: 10px auto;
            }
        }

        /* Custom styles for agreement reminder */
        .email-info-bar {
            background: #e7f3ff;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }

        .warning-box {
            margin: 25px 0;
            padding: 15px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
        }

        .warning-box strong {
            color: #bd951a;
        }

        .warning-box p {
            margin: 5px 0 0 0;
            color: #bd951a;
        }
    </style>
</head>

<body>
    <div class="email-container">

        {{-- Header --}}
        <div class="email-header">
            <img src="{{ asset('images/logo.png') }}" alt="IDEA Consultancy Logo">
        </div>

        {{-- Body --}}
        <div class="email-body">
            {{-- Check if we have custom content (for agreement reminder) --}}
            @if (isset($customContent))
                {!! $customContent !!}
            @else
                {{-- Default content for backward compatibility --}}
                <h2>{{ $greeting ?? 'Hello!' }}</h2>

                @if (isset($introLines))
                    @foreach ($introLines as $line)
                        <p>{!! $line !!}</p>
                    @endforeach
                @endif

                @isset($actionText)
                    <div class="email-button">
                        <a href="{{ $actionUrl }}">{{ $actionText }}</a>
                    </div>
                @endisset

                @if (isset($outroLines))
                    @foreach ($outroLines as $line)
                        <p style="font-size:14px; color:#777;">{!! $line !!}</p>
                    @endforeach
                @endif

                <p style="font-size:14px; color:#4b5563;">Thanks,</p>
                <p><strong>IDEA Consultancy Team</strong></p>
                <p><strong>Contact: 01-4547547/01-5318333</strong></p>
            @endif
        </div>

        {{-- Footer --}}
        <div class="email-footer">
            © {{ date('Y') }} IDEA Consultancy. All rights reserved.
            @if (!isset($customContent))
                <p style="margin: 5px 0 0 0; font-size: 11px;">This is an automated message. Please do not reply.</p>
            @endif
        </div>
    </div>
</body>

</html>

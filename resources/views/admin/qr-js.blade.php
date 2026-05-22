@extends('layouts.app')

@section('title', 'Student Intake QR Code')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">📱 Student Registration QR Code</h4>
                    </div>
                    <div class="card-body text-center">

                        {{-- QR Code appears here --}}
                        <div id="qrcode" class="d-flex justify-content-center my-4"></div>

                        <div class="alert alert-info">
                            <strong>📌 How to use:</strong><br>
                            1. Right-click on QR code → Save Image<br>
                            2. Print and display in your office<br>
                            3. Students scan with phone camera<br>
                            4. They fill form → Auto-added to CRM!
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <button onclick="downloadQR()" class="btn btn-primary w-100">
                                    ⬇️ Download QR Code
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button onclick="copyLink()" class="btn btn-secondary w-100">
                                    📋 Copy Form Link
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded">
                            <strong>Form URL:</strong><br>
                            <code id="formUrl" style="word-break: break-all;">{{ url('/student-intake-form') }}</code>
                        </div>

                        <p class="text-muted mt-3 small">
                            <i class="fas fa-info-circle"></i> QR code is permanent and won't change.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- QR Code Library --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <script>
        // The URL that will be encoded in QR code (THIS NEVER CHANGES)
        const formUrl = "{{ url('/student-intake-form') }}";

        // Display the URL on page
        document.getElementById('formUrl').innerHTML = formUrl;

        // Generate QR code (SAME every time because URL is same)
        new QRCode(document.getElementById("qrcode"), {
            text: formUrl,
            width: 250,
            height: 250,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        // Download QR code as image
        function downloadQR() {
            const canvas = document.querySelector("#qrcode canvas");
            if (canvas) {
                const link = document.createElement('a');
                link.download = 'student-intake-qr.png';
                link.href = canvas.toDataURL();
                link.click();
            } else {
                alert('Please wait for QR code to load');
            }
        }

        // Copy form link to clipboard
        function copyLink() {
            navigator.clipboard.writeText(formUrl);
            alert('✅ Form link copied to clipboard!');
        }
    </script>
@endsection

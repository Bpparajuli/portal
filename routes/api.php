<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentIntakeController;
use App\Http\Controllers\Api\StudentApiController;
use Illuminate\Http\Request;

// ============================================
// PUBLIC API ROUTES
// ============================================

// Student Intake Routes
Route::post('/student/intake', [StudentIntakeController::class, 'intake'])->name('api.student.intake');
Route::get('/student/intake-form', [StudentIntakeController::class, 'showForm'])->name('api.student.form');
Route::get('/student/quick-add', [StudentIntakeController::class, 'quickAdd'])->name('api.student.quick');

// ============================================
// WHATSAPP WEBHOOK
// ============================================
Route::post('/whatsapp-webhook', function (Request $request) {
    $from = $request->input('From');
    $message = $request->input('Body');

    Log::info('WhatsApp message received', ['from' => $from, 'message' => $message]);

    // Expected format: "John Doe, 1234567890, Australia"
    $parts = explode(',', $message);

    if (count($parts) >= 2) {
        $name = trim($parts[0]);
        $phone = trim($parts[1]);
        $country = trim($parts[2] ?? '');

        // Call your internal API
        $response = Http::post(url('/api/student/intake'), [
            'full_name' => $name,
            'phone_number' => $phone,
            'country' => $country,
            'source' => 'whatsapp'
        ]);

        $result = $response->json();

        // Prepare reply message
        if ($result['success'] ?? false) {
            $reply = "✅ Student Added Successfully!\n\n";
            $reply .= "ID: {$result['student']['id']}\n";
            $reply .= "Name: {$result['student']['name']}\n";
            $reply .= "Phone: {$result['student']['phone']}";
        } else {
            $reply = "❌ Failed to add student.\n\n";
            $reply = "Please send in format:\n";
            $reply .= "Name, Phone Number, Country\n\n";
            $reply .= "Example: John Doe, 1234567890, Australia";
        }

        // Send reply via Twilio (uncomment when Twilio is configured)
        /*
$twilio = new \Twilio\Rest\Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
$twilio->messages->create($from, [
'from' => env('TWILIO_WHATSAPP_NUMBER'),
'body' => $reply
]);
*/

        return response($reply);
    }

    return response("Send format: Name, Phone, Country");
});

// ============================================
// FACEBOOK LEAD WEBHOOK
// ============================================
Route::post('/facebook-lead-webhook', function (Request $request) {
    Log::info('Facebook lead received', $request->all());

    // Facebook sends verification request
    if ($request->input('hub_mode') === 'subscribe') {
        return response($request->input('hub_challenge'));
    }

    // Process lead data
    $leadData = $request->input('entry')[0]['changes'][0]['value']['leads'][0] ?? null;

    if ($leadData) {
        $fullName = $leadData['field_data'][0]['values'][0] ?? '';
        $email = $leadData['field_data'][1]['values'][0] ?? '';
        $phone = $leadData['field_data'][2]['values'][0] ?? '';

        // Call your internal API
        Http::post(url('/api/student/intake'), [
            'full_name' => $fullName,
            'phone_number' => $phone,
            'email' => $email,
            'source' => 'facebook_lead_ad'
        ]);
    }

    return response()->json(['status' => 'ok']);
});

// ============================================
// FACEBOOK VERIFICATION (GET request)
// ============================================
Route::get('/facebook-lead-webhook', function (Request $request) {
    $challenge = $request->input('hub_challenge');
    return response($challenge);
});

// ============================================
// PROTECTED API ROUTES (Sanctum)
// ============================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Student API
    Route::get('/students', [StudentApiController::class, 'index']);
    Route::get('/students/{student}', [StudentApiController::class, 'show']);
});

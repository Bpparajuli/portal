<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestEnquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_enquiry_form(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_guest_can_submit_enquiry(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'subject' => 'Test Enquiry',
            'message' => 'This is a test enquiry message.',
            'type' => 'general',
        ];

        $response = $this->post(route('guest.enquiries.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('enquiries', [
            'email' => 'john@example.com',
            'subject' => 'Test Enquiry',
            'is_read' => false,
        ]);
    }

    public function test_enquiry_validation_requires_name(): void
    {
        $response = $this->post(route('guest.enquiries.store'), [
            'email' => 'john@example.com',
            'message' => 'Test message',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_enquiry_validation_requires_valid_email(): void
    {
        $response = $this->post(route('guest.enquiries.store'), [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'message' => 'Test message',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_enquiry_validation_requires_message(): void
    {
        $response = $this->post(route('guest.enquiries.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertSessionHasErrors('message');
    }
}

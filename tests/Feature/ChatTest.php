<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create([
            'role' => 'superadmin',
            'active' => true,
            'agreement_status' => 'verified',
            'paid_crm' => true,
        ]);

        $this->agent = User::factory()->create([
            'role' => 'agent',
            'active' => true,
            'agreement_status' => 'verified',
            'paid_crm' => true,
        ]);
    }

    public function test_chat_messages_can_be_sent(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.chat.send'), [
            'receiver_id' => $this->agent->id,
            'message' => 'Hello from admin',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('chat_messages', [
            'sender_id' => $this->admin->id,
            'receiver_id' => $this->agent->id,
            'message' => 'Hello from admin',
        ]);
    }

    public function test_chat_users_list_returns_results(): void
    {
        ChatMessage::create([
            'sender_id' => $this->agent->id,
            'receiver_id' => $this->admin->id,
            'message' => 'Test message',
            'status' => 'sent',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.chat.users'));

        $response->assertJsonStructure(['users', 'total_unread']);
    }

    public function test_chat_messages_can_be_fetched(): void
    {
        ChatMessage::create([
            'sender_id' => $this->admin->id,
            'receiver_id' => $this->agent->id,
            'message' => 'Message 1',
            'status' => 'sent',
        ]);

        ChatMessage::create([
            'sender_id' => $this->agent->id,
            'receiver_id' => $this->admin->id,
            'message' => 'Message 2',
            'status' => 'sent',
        ]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.chat.messages', $this->agent->id)
        );

        $response->assertJsonCount(2);
    }
}

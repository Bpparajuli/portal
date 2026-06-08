<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalRoutesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agent;
    private User $staff;
    private User $freeAgent;

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

        $this->freeAgent = User::factory()->create([
            'role' => 'agent',
            'active' => true,
            'agreement_status' => 'verified',
            'paid_crm' => false,
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'active' => true,
            'parent_id' => $this->agent->id,
        ]);
    }

    public function test_guest_can_view_homepage(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_guest_can_view_universities(): void
    {
        $this->get(route('guest.universities.index'))->assertStatus(200);
    }

    public function test_guest_can_view_courses(): void
    {
        $this->get(route('guest.courses.index'))->assertStatus(200);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertStatus(200);
    }

    public function test_admin_can_access_students(): void
    {
        $this->actingAs($this->admin)->get(route('admin.students.index'))->assertStatus(200);
    }

    public function test_admin_can_access_email_inbox(): void
    {
        $this->actingAs($this->admin)->get(route('admin.emails.inbox'))->assertStatus(200);
    }

    public function test_admin_can_access_pages(): void
    {
        $this->actingAs($this->admin)->get(route('admin.pages.index'))->assertStatus(200);
    }

    public function test_admin_can_access_settings(): void
    {
        $this->actingAs($this->admin)->get(route('admin.settings.index'))->assertStatus(200);
    }

    public function test_admin_can_access_enquiries(): void
    {
        $this->actingAs($this->admin)->get(route('admin.enquiries.index'))->assertStatus(200);
    }

    public function test_admin_can_access_crm(): void
    {
        $this->actingAs($this->admin)->get(route('crm.dashboard'))->assertStatus(200);
    }

    public function test_agent_can_access_dashboard(): void
    {
        $this->actingAs($this->agent)->get(route('agent.dashboard'))->assertStatus(200);
    }

    public function test_agent_can_access_students(): void
    {
        $this->actingAs($this->agent)->get(route('agent.students.index'))->assertStatus(200);
    }

    public function test_agent_with_paid_crm_can_access_crm(): void
    {
        $this->actingAs($this->agent)->get(route('crm.dashboard'))->assertStatus(200);
    }

    public function test_agent_without_paid_crm_cannot_access_crm(): void
    {
        $this->actingAs($this->freeAgent)
            ->get(route('crm.dashboard'))
            ->assertStatus(302)
            ->assertSessionHas('error');
    }

    public function test_staff_can_access_dashboard(): void
    {
        $this->actingAs($this->staff)->get(route('staff.dashboard'))->assertStatus(200);
    }

    public function test_agent_cannot_access_admin(): void
    {
        $this->actingAs($this->agent)->get(route('admin.dashboard'))->assertStatus(403);
    }

    public function test_staff_cannot_access_admin(): void
    {
        $this->actingAs($this->staff)->get(route('admin.dashboard'))->assertStatus(403);
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertStatus(302);
        $this->get(route('agent.dashboard'))->assertStatus(302);
    }
}

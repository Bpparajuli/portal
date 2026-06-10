<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Course;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agent;
    private User $staff;

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

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'active' => true,
            'parent_id' => $this->agent->id,
        ]);
    }

    public function test_staff_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.dashboard'));

        $response->assertStatus(200);
    }

    public function test_staff_can_view_students_list(): void
    {
        Student::factory()->count(3)->create(['agent_id' => $this->agent->id]);

        $response = $this->actingAs($this->staff)->get(route('staff.students.index'));

        $response->assertStatus(200);
    }

    public function test_staff_can_view_student_details(): void
    {
        $student = Student::factory()->create(['agent_id' => $this->agent->id]);

        $response = $this->actingAs($this->staff)->get(route('staff.students.show', $student));

        $response->assertStatus(200);
    }

    public function test_staff_can_view_universities(): void
    {
        University::factory()->count(3)->create();

        $response = $this->actingAs($this->staff)->get(route('staff.universities'));

        $response->assertStatus(200);
    }

    public function test_staff_can_view_courses(): void
    {
        Course::factory()->count(3)->create();

        $response = $this->actingAs($this->staff)->get(route('staff.courses'));

        $response->assertStatus(200);
    }

    public function test_staff_can_view_applications(): void
    {
        $student = Student::factory()->create(['agent_id' => $this->agent->id]);
        $status = ApplicationStatus::factory()->create();
        Application::factory()->create([
            'student_id' => $student->id,
            'agent_id' => $this->agent->id,
            'application_status_id' => $status->id,
        ]);

        $response = $this->actingAs($this->staff)->get(route('staff.applications.index'));

        $response->assertStatus(200);
    }

    public function test_staff_can_view_notifications(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.notifications.index'));

        $response->assertStatus(200);
    }
}

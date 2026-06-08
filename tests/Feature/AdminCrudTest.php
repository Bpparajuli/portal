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

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agent;
    private University $university;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->agent = User::factory()->create();

        $this->university = University::factory()->create();
    }

    // ========== UNIVERSITIES ==========

    public function test_admin_can_view_universities_index(): void
    {
        University::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.universities.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_university(): void
    {
        $data = [
            'name' => 'New Test University',
            'short_name' => 'NTU',
            'country' => 'Testland',
            'city' => 'Test City',
            'website' => 'https://ntu.test',
            'description' => 'A test university',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.universities.store'), $data);

        $response->assertRedirect(route('admin.universities.index'));
        $this->assertDatabaseHas('universities', ['name' => 'New Test University']);
    }

    public function test_admin_can_view_university(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.universities.show', $this->university));

        $response->assertStatus(200);
    }

    public function test_admin_can_update_university(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.universities.update', $this->university), [
            'name' => 'Updated University Name',
            'short_name' => 'UUN',
            'country' => 'Updatedland',
            'city' => 'Updated City',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('universities', ['name' => 'Updated University Name']);
    }

    public function test_admin_can_delete_university(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.universities.destroy', $this->university));

        $response->assertRedirect(route('admin.universities.index'));
        $this->assertSoftDeleted($this->university);
    }

    // ========== COURSES ==========

    public function test_admin_can_view_courses_index(): void
    {
        Course::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.courses.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_course(): void
    {
        $data = [
            'university_id' => $this->university->id,
            'title' => 'Test Course',
            'course_code' => 'TC-001',
            'course_type' => 'UG',
            'fee' => '15000',
            'duration' => '3 years',
            'intakes' => 'September',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.courses.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', ['title' => 'Test Course']);
    }

    public function test_admin_can_view_course(): void
    {
        $course = Course::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.courses.show', $course));

        $response->assertStatus(200);
    }

    public function test_admin_can_update_course(): void
    {
        $course = Course::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('admin.courses.update', $course), [
            'university_id' => $this->university->id,
            'title' => 'Updated Course',
            'course_code' => 'UC-002',
            'course_type' => 'PG',
            'fee' => '25000',
            'duration' => '2 years',
            'intakes' => 'January',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', ['title' => 'Updated Course']);
    }

    public function test_admin_can_delete_course(): void
    {
        $course = Course::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.courses.destroy', $course));

        $response->assertRedirect();
        $this->assertSoftDeleted($course);
    }

    // ========== APPLICATIONS ==========

    public function test_admin_can_view_applications_index(): void
    {
        $agent = User::factory()->create();
        $student = Student::factory()->create(['agent_id' => $agent->id]);
        $status = ApplicationStatus::factory()->create();
        Application::factory()->create([
            'student_id' => $student->id,
            'agent_id' => $agent->id,
            'application_status_id' => $status->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.applications.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_application(): void
    {
        $agent = User::factory()->create();
        $student = Student::factory()->create(['agent_id' => $agent->id]);
        $status = ApplicationStatus::factory()->create();
        $application = Application::factory()->create([
            'student_id' => $student->id,
            'agent_id' => $agent->id,
            'application_status_id' => $status->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.applications.show', $application));

        $response->assertStatus(200);
    }
}

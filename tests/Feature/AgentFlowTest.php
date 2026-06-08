<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Course;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgentFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agent;
    private University $university;
    private Course $course;
    private Student $student;

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

        $this->university = University::factory()->create();
        $this->course = Course::factory()->create(['university_id' => $this->university->id]);
        $this->student = Student::factory()->create(['agent_id' => $this->agent->id]);
    }

    public function test_agent_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->agent)->get(route('agent.dashboard'));

        $response->assertStatus(200);
    }

    public function test_agent_can_view_own_students(): void
    {
        Student::factory()->count(3)->create(['agent_id' => $this->agent->id]);

        $response = $this->actingAs($this->agent)->get(route('agent.students.index'));

        $response->assertStatus(200);
    }

    public function test_agent_can_create_student(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.com',
            'phone_number' => '+1234567890',
            'gender' => 'Male',
            'nationality' => 'Testland',
            'source' => 'Website',
        ];

        $response = $this->actingAs($this->agent)->post(route('agent.students.store'), $data);

        $response->assertRedirect(route('agent.students.index'));
        $this->assertDatabaseHas('students', ['email' => 'john.doe@test.com']);
    }

    public function test_agent_can_view_applications(): void
    {
        $status = ApplicationStatus::factory()->create();
        Application::factory()->create([
            'student_id' => $this->student->id,
            'agent_id' => $this->agent->id,
            'application_status_id' => $status->id,
        ]);

        $response = $this->actingAs($this->agent)->get(route('agent.applications.index'));

        $response->assertStatus(200);
    }

    public function test_agent_can_create_application(): void
    {
        Storage::fake('public');

        $sopFile = UploadedFile::fake()->create('sop.pdf', 200, 'application/pdf');

        $data = [
            'student_id' => $this->student->id,
            'university_id' => $this->university->id,
            'course_id' => $this->course->id,
            'sop_file' => $sopFile,
        ];

        $response = $this->actingAs($this->agent)->post(route('agent.applications.store'), $data);

        $response->assertRedirect(route('agent.applications.index'));
        $this->assertDatabaseHas('applications', ['student_id' => $this->student->id]);
    }

    public function test_agent_can_upload_document(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf');

        $data = [
            'document_type' => 'passport',
            'file' => $file,
        ];

        $response = $this->actingAs($this->agent)->post(
            route('agent.documents.store', $this->student),
            $data
        );

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('documents', [
            'student_id' => $this->student->id,
            'document_type' => 'passport',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agent;
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

        $this->student = Student::factory()->create(['agent_id' => $this->agent->id]);
    }

    public function test_admin_can_upload_document_for_student(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf');

        $data = [
            'document_type' => 'passport',
            'file' => $file,
        ];

        $response = $this->actingAs($this->admin)->post(
            route('admin.documents.store', $this->student),
            $data
        );

        $response->assertRedirect(route('admin.documents.index', $this->student->id));
        $this->assertDatabaseHas('documents', [
            'student_id' => $this->student->id,
            'document_type' => 'passport',
            'uploaded_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_download_document(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('documents/test.pdf', 'fake content');

        $document = Document::create([
            'student_id' => $this->student->id,
            'uploaded_by' => $this->admin->id,
            'file_name' => 'test.pdf',
            'file_path' => 'documents/test.pdf',
            'file_type' => 'application/pdf',
            'document_type' => 'passport',
            'status' => 'uploaded',
        ]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.documents.download', [$this->student, $document])
        );

        $response->assertStatus(200);
    }

    public function test_admin_can_delete_document(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('documents/test.pdf', 'fake content');

        $document = Document::create([
            'student_id' => $this->student->id,
            'uploaded_by' => $this->admin->id,
            'file_name' => 'test.pdf',
            'file_path' => 'documents/test.pdf',
            'file_type' => 'application/pdf',
            'document_type' => 'passport',
            'status' => 'uploaded',
        ]);

        $response = $this->actingAs($this->admin)->delete(
            route('admin.documents.destroy', [$this->student, $document])
        );

        $response->assertRedirect(route('admin.documents.index', $this->student->id));
        $this->assertSoftDeleted($document);
    }

    public function test_admin_can_update_document_status(): void
    {
        Storage::fake('public');

        $document = Document::create([
            'student_id' => $this->student->id,
            'uploaded_by' => $this->admin->id,
            'file_name' => 'test.pdf',
            'file_path' => 'documents/test.pdf',
            'file_type' => 'application/pdf',
            'document_type' => 'passport',
            'status' => 'uploaded',
        ]);

        $response = $this->actingAs($this->admin)->patch(
            route('admin.documents.updateStatus', [$this->student, $document]),
            ['status' => 'reviewed']
        );

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'reviewed',
        ]);
    }
}

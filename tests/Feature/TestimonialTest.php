<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'superadmin',
            'active' => true,
            'agreement_status' => 'verified',
            'paid_crm' => true,
        ]);
    }

    public function test_admin_can_view_testimonials_index(): void
    {
        Testimonial::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.testimonials.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_testimonial(): void
    {
        $data = [
            'name' => 'John Doe',
            'location' => 'New York, USA',
            'content' => 'This consultancy helped me achieve my dream of studying abroad.',
            'rating' => 5,
            'is_active' => true,
            'sort_order' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.testimonials.store'), $data);

        $response->assertRedirect(route('admin.testimonials.index'));
        $this->assertDatabaseHas('testimonials', ['name' => 'John Doe']);
    }

    public function test_admin_can_edit_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create();

        $response = $this->actingAs($this->admin)->get(
            route('admin.testimonials.edit', $testimonial)
        );

        $response->assertStatus(200);
    }

    public function test_admin_can_update_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create([
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->admin)->put(
            route('admin.testimonials.update', $testimonial),
            [
                'name' => 'Updated Name',
                'content' => 'Updated content.',
                'rating' => 4,
                'is_active' => true,
            ]
        );

        $response->assertRedirect(route('admin.testimonials.index'));
        $this->assertDatabaseHas('testimonials', ['name' => 'Updated Name']);
    }

    public function test_admin_can_delete_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create();

        $response = $this->actingAs($this->admin)->delete(
            route('admin.testimonials.destroy', $testimonial)
        );

        $response->assertRedirect(route('admin.testimonials.index'));
        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }
}

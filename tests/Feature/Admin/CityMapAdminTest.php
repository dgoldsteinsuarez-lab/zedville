<?php

namespace Tests\Feature\Admin;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * City Map, Phase 1 (2026-10-19). Super-Admin-only: upload/replace the
 * one shared city map hero image, and a read-only per-Grade address list.
 */
class CityMapAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_upload_the_city_map_image(): void
    {
        $this->usePublicPathInTemp();
        $admin = User::factory()->create(['role' => 1]);
        // GD isn't available in this environment's PHP CLI, so
        // UploadedFile::fake()->image() (which renders real pixels) can't
        // be used - a tiny real PNG's bytes work without it.
        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        $file = UploadedFile::fake()->createWithContent('city.png', $pngBytes);

        $response = $this->actingAs($admin)->post(route('admin.city-map.upload'), ['image' => $file]);

        $response->assertRedirect();
        $this->assertDatabaseHas('city_map_settings', ['id' => 1]);
        $setting = \App\Models\CityMapSetting::current();
        $this->assertNotNull($setting->image);
        $this->assertFileExists(public_path('uploads/city/' . $setting->image));
    }

    public function test_admin_can_view_a_grades_assigned_addresses(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => uniqid() . '.test',
        ]);
        $grade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 8']);
        $class = SchoolClass::create(['sid' => (string) $school->id, 'name' => '8A', 'grade_id' => $grade->id]);
        $student = User::factory()->create(['role' => 4, 'sid' => (string) $school->id, 'grade' => $class->id, 'nickname' => 'Robin']);
        \App\Services\AddressService::assignForStudent($student);

        $response = $this->actingAs($admin)->get(route('admin.city-map.addresses', $grade->id));

        $response->assertOk();
        $response->assertSee('Robin');
    }

    public function test_tutors_cannot_access_the_city_map_admin_page(): void
    {
        $tutor = User::factory()->create(['role' => 3]);

        $response = $this->actingAs($tutor)->get(route('admin.city-map.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_edit_a_streets_name_and_bio(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $street = \App\Models\Street::where('name', 'Adam Smith Avenue')->first();

        $response = $this->actingAs($admin)->put(route('admin.city-map.streets.update', $street->id), [
            'name' => 'Adam Smith Lane',
            'bio' => 'Updated bio text.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('streets', [
            'id' => $street->id,
            'name' => 'Adam Smith Lane',
            'bio' => 'Updated bio text.',
        ]);
    }

    public function test_the_updated_street_bio_shows_to_students_on_the_city_map(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $street = \App\Models\Street::where('name', 'Adam Smith Avenue')->first();
        $this->actingAs($admin)->put(route('admin.city-map.streets.update', $street->id), [
            'name' => 'Adam Smith Avenue',
            'bio' => 'A brand new bio only an admin would write.',
        ]);

        $student = User::factory()->create(['role' => 4]);
        $response = $this->actingAs($student)->get(route('education.city.district', 'residential-quarter'));

        $response->assertSee('A brand new bio only an admin would write.');
    }
}

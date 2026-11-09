<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-26/27: City Hall, City Mall and the Supermarket fit the
 * screen as one picture (components/city-scene.blade.php), the Back button
 * on it: Back at its top left, the name at the top, the hint at the bottom
 * (2026-10-29: "put the text back on its positions like last time on the
 * photo. And the back button as well, so you can maximize the photo").
 */
class CitySceneTest extends TestCase
{
    use RefreshDatabase;

    /** Daniel, 2026-10-30, City Mall: "move the back button outside" - Back comes first, beside the picture
     *  where there's white for it (zsFit), the name and the hint stay on the picture. */
    public function test_each_place_is_one_picture_with_its_name_and_hint_on_it_and_back_beside_it(): void
    {
        $student = User::factory()->create(['role' => 4]);

        foreach (['/education/city-hall' => 'City Hall', '/education/city-mall' => 'City Mall', '/supermarket' => 'Supermarket'] as $url => $name) {
            // the Supermarket's own sign says its name (2026-10-29: "remove the top text 'supermarket'
            // because its already redundant") - only for screen readers there
            $heading = $url === '/supermarket' ? '<h1 class="sr-only">Supermarket</h1>' : '<h1 class="zs-title">' . $name . '</h1>';
            $this->actingAs($student)->get($url)->assertOk()
                ->assertSeeInOrder(['class="zs" data-zs', 'class="zvBackBtn zs-back"', 'class="zs-scene"', 'class="zs-frame"', $heading, '<p class="zs-hint">'], false)
                ->assertDontSee('class="zs-head"', false)->assertDontSee('class="zs-foot"', false) // nothing in rows of their own
                ->assertDontSee('zvBackBtn inline-flex items-center gap-2 py-2 px-4 mb-5', false); // no second Back from the layout
        }
        $this->actingAs($student)->get('/supermarket')->assertDontSee('<h1 class="zs-title">', false);
    }

    /** Daniel, 2026-10-27 and 2026-10-29, lines drawn on screenshots: the mall and the supermarket cut at the top and bottom, City Hall whole. */
    public function test_the_pictures_are_cut_where_daniel_drew_the_lines(): void
    {
        $student = User::factory()->create(['role' => 4]);

        foreach ([
            '/education/city-mall' => ['aspect-ratio: 3344 / 2093', 'aspect-ratio: 3344 / 2496; top: -11.3712%'],
            '/supermarket' => ['aspect-ratio: 2368 / 1372', 'aspect-ratio: 2368 / 1728; top: -17.2012%'],
            '/education/city-hall' => ['aspect-ratio: 4096 / 2288', 'aspect-ratio: 4096 / 2288; top: 0%'],
        ] as $url => [$scene, $frame]) {
            $this->actingAs($student)->get($url)->assertOk()
                ->assertSeeInOrder(['class="zs-scene"', $scene, 'class="zs-frame"', $frame, 'class="zs-img"'], false);
        }
    }

    /** Daniel, 2026-10-27: the City Map moved up so the whole map is on the first screen, Back like City Hall's,
     *  and "the text should not be on top of the map". */
    public function test_the_city_map_is_a_scene_with_the_address_on_it(): void
    {
        $school = SchoolDomain::create(['school_name' => 'Test School', 'school_code' => 'TS-' . uniqid(), 'school_domain' => uniqid() . '.test']);
        $grade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 8']);
        $class = SchoolClass::create(['sid' => (string) $school->id, 'name' => '8A', 'grade_id' => $grade->id]);
        $student = User::factory()->create(['role' => 4, 'sid' => (string) $school->id, 'grade' => $class->id]);

        $this->actingAs($student)->get(route('education.city'))->assertOk()
            ->assertSeeInOrder(['class="zs zs--outside zs--map', 'class="zvBackBtn zs-back"', '<h1 class="zs-head-title">City Map</h1>',
                '<p class="zs-head-sub">You live at ', 'class="zs-scene"', 'aspect-ratio: 2400 / 1435', 'alt="Top view of Zedville with its 9 districts"',
                'class="zv-district', 'class="zv-street"', '<p class="zs-foot">Click a district on the map to see it up close.</p>'], false)
            ->assertDontSee('class="zs-title"', false)->assertDontSee('class="zs-hint"', false) // nothing on top of the map
            ->assertDontSee('zvBackBtn inline-flex items-center gap-2 py-2 px-4 mb-5', false); // the layout's Back
    }

    /** Daniel, 2026-10-27: "make the back button consistent to all like the one from city hall". */
    public function test_pages_with_their_own_back_link_use_the_same_button(): void
    {
        $student = User::factory()->create(['role' => 4]);

        // a district is a scene now, Back beside its picture (2026-10-28)
        $this->actingAs($student)->get(route('education.city.district', 'civic-plaza'))->assertOk()
            ->assertSee('class="zvBackBtn zs-back"', false);
        $this->actingAs($student)->get(route('education.civic-chamber'))->assertOk()
            ->assertSee('class="zvBackBtn cc-back inline-flex items-center gap-2 py-2 px-4 text-sm"', false);
    }

    /** Daniel, 2026-10-27: "Do the same for this part" - the Educational Finance Department, board and all. */
    public function test_the_educational_finance_department_is_a_scene_too(): void
    {
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'preferred_language' => 'de']);

        $this->actingAs($student)->get(route('education.educational_finance_department'))->assertOk()
            ->assertSeeInOrder(['class="zs zs--efd" data-zs', 'class="zvBackBtn zs-back"', 'class="zs-scene"',
                'aspect-ratio: 1536 / 864', // the empty floor cut off, the hint just under the furniture
                'class="blackboardHolder"', 'floatingContainer library', '<h1 class="zs-title">Abteilung für Finanzbildung</h1>',
                '<p class="zs-hint">Klick auf eine Aktivität an der Tafel, auf die Bibliothek, auf Frau Rodriguez oder auf die Tür</p>'], false)
            ->assertDontSee('zvBackBtn inline-flex items-center gap-2 py-2 px-4 mb-5', false); // the layout's Back
    }

    public function test_the_other_looks_are_gone(): void
    {
        $student = User::factory()->create(['role' => 4, 'preferred_language' => 'es']);

        $this->actingAs($student)->get('/education/city-hall?look=c')->assertOk()
            ->assertDontSee('zs--c', false)
            ->assertDontSee('zs-preview', false)
            ->assertDontSee('zs-places', false)
            ->assertSee('Haz clic en la pantalla grande o en una puerta para entrar');
    }
}

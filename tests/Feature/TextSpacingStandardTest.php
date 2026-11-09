<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Daniel, 2026-10-29: "make the spacing of text on the sidebar like in a standard
 * website ... fix all the spacing of text in the whole website that it is proper or
 * standard in a website".
 */
class TextSpacingStandardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The standard spacing (40-44px rows, icons 12px from the label, the profile block right
     * under the logo) with the text of option 3 ("I chose option 3"): 16px semibold labels,
     * 24px icons, a 14px greeting, an 18px name.
     */
    public function test_the_sidebar_uses_option_3(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get(route('dashboard', ['side' => '2']))->assertOk()
            ->assertSee('class="flex items-center gap-3 px-3 py-2.5 text-base font-semibold rounded-md', false)
            ->assertSee('<span class="zv-nav-icon">', false)
            ->assertSee('.userAdmin aside .zv-nav-icon svg { width: 24px; height: 24px; }', false)
            ->assertSee('<div class="userDtls px-[22px]" ', false)
            ->assertSee('<p class="text-sm text-[#5C5C5C] singlelineTxt">', false)
            ->assertSee('<h4 class="text-lg leading-6 font-semibold text-black singlelineTxt">', false)
            ->assertSee('class="block px-3 py-2 text-[15px] leading-[22px] rounded-md', false) // the City group's pages
            ->assertSee('lg:static"', false)
            ->assertDontSee('space-x-2 text-base', false) // the old spacing
            ->assertDontSee('2xl:mt-[30px]', false)
            ->assertDontSee('zv-side-pick', false); // the comparison is gone
    }

    /** "Make the questions fit in the browser ... but not two columns of the choices" */
    public function test_answers_are_one_under_the_other(): void
    {
        $theme = File::get(public_path('asset/front/css/theme_style.css'));
        $this->assertStringContainsString('.quizQuestionSecItem .questionLists { display: grid; gap: 8px; }', $theme);
        $this->assertStringNotContainsString('minmax(max(240px', $theme);

        $survey = File::get(resource_path('views/surveys/_styles.blade.php'));
        $this->assertStringContainsString('.zvq-options { display: grid; gap: 8px; }', $survey);
        $this->assertStringNotContainsString('(100% - 30px) / 4', $survey);
        $this->assertStringNotContainsString('repeat(5, minmax(0, 1fr))', $survey);

        $department = File::get(resource_path('views/education/educational-finance-departmentex.blade.php'));
        $this->assertStringContainsString('#libraryModalapp .lib-options { display: grid; gap: 8px; }', $department);
        $this->assertStringNotContainsString('(100% - 10px) / 2', $department);
    }

    /**
     * Nothing a student reads is under 12px. Left alone on purpose: counts in a corner
     * (the bell's, a mood calendar day's "×2") and the map's NPO labels (sized with the picture).
     */
    public function test_no_student_text_is_smaller_than_12px(): void
    {
        $allowed = [
            'layouts/profile.blade.php' => 1,               // the bell's unread count
            'profile/partials/my-mood.blade.php' => 1,      // "×2" in a calendar day's corner
            'profile/city-mood.blade.php' => 1,             // the same, on City Mood
            'education/city/_building-spots.blade.php' => 1, // NPO labels on the map
        ];
        $small = '/font-size:\s*(?:[1-9]|1[01])(?:\.\d+)?px|font-size:\s*0?\.(?:[0-6]\d*|7[0-4]\d*)rem|text-\[(?:[1-9]|1[01])px\]/';
        $found = [];
        foreach (File::allFiles(resource_path('views')) as $file) {
            $rel = str_replace('\\', '/', $file->getRelativePathname());
            if (preg_match('#^(admin|tutor|emails|pdf)/|^layouts/admin#', $rel)) {
                continue;
            }
            $count = preg_match_all($small, $file->getContents());
            if ($count > ($allowed[$rel] ?? 0)) {
                $found[] = $rel . ' (' . $count . ')';
            }
        }
        foreach (['theme_style.css', 'surveys.css', 'citizen-activation.css'] as $css) {
            if (preg_match_all($small, File::get(public_path('asset/front/css/' . $css)))) {
                $found[] = $css;
            }
        }

        $this->assertSame([], $found);
    }

    /** City Hall's mood tiles: the mood, its share and what it means, one under the other. */
    public function test_city_hall_mood_tiles_stack_their_text(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get(route('education.main-hall'))->assertOk()
            ->assertSee('.quad-name, .quad-pct, .quad-sub { display: block; }', false);
    }

    /** No salary set for the task nor as a default: the city's monthly salary, never 0 ("Infinity%"). */
    public function test_a_finhero_task_without_a_salary_uses_the_citys_salary(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get('/finhero/task-4')->assertOk()
            ->assertViewHas('salary', config('zedville.monthly_salary'))
            ->assertSee('salary: ' . config('zedville.monthly_salary') . ',', false);
    }
}

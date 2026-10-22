<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\CurriculumQuestion;
use App\Models\CurriculumQuestionOption;
use App\Models\GradeLevel;
use App\Models\PisaProficiencyLevel;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Curriculum ("Academy") authoring: Level -> Categories -> Items -> Questions.
 * Super-Admin-only, gated entirely at the route-group level (routes/admin.php,
 * role:super_admin), matching /admin/pisa's pattern rather than sprinkling
 * isSuperAdmin() checks through every method.
 *
 * Daniel, 2026-10-20: "for PISA, i should be able to add tasks for each
 * level, for students to practice and also practice exams like in the real
 * pisa exams" + "i dont see the levels in admin" (his own Sept-13 doc's
 * Activities/Exams/Graphics-per-Level structure). Confirmed via
 * AskUserQuestion: real scored quizzes, a new area kept separate from the
 * existing per-Grade Education settings.
 */
class CurriculumController extends Controller
{
    public function index()
    {
        $levels = GradeLevel::orderBy('sort_order')->get();
        $categoryCounts = CurriculumCategory::selectRaw('grade_level_id, count(*) as c')
            ->groupBy('grade_level_id')
            ->pluck('c', 'grade_level_id');

        return view('admin.curriculum.index', compact('levels', 'categoryCounts'));
    }

    public function categories(GradeLevel $level)
    {
        $categories = CurriculumCategory::where('grade_level_id', $level->id)
            ->withCount('items')
            ->orderBy('sort_order')
            ->get();

        return view('admin.curriculum.categories', compact('level', 'categories'));
    }

    public function storeCategory(Request $request, GradeLevel $level)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'sort_order' => 'required|integer|min:1',
        ]);

        CurriculumCategory::create([
            'grade_level_id' => $level->id,
            'name' => $data['name'],
            'sort_order' => $data['sort_order'],
        ]);

        return redirect()->back()->with('success', 'Category added.');
    }

    public function updateCategory(Request $request, CurriculumCategory $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'sort_order' => 'required|integer|min:1',
        ]);

        $category->update($data);

        return redirect()->back()->with('success', 'Category updated.');
    }

    public function destroyCategory(CurriculumCategory $category)
    {
        $category->delete();

        return redirect()->back()->with('success', 'Category deleted.');
    }

    public function items(CurriculumCategory $category)
    {
        $items = $category->items()->withCount('questions')->orderBy('sort_order')->get();
        $pisaLevels = PisaProficiencyLevel::orderBy('sort_order')->get();

        return view('admin.curriculum.items', compact('category', 'items', 'pisaLevels'));
    }

    public function storeItem(Request $request, CurriculumCategory $category)
    {
        $data = $this->validateItem($request);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_real_exam'] = $request->boolean('is_real_exam', false);

        $image = $this->uploadCurriculumImage($request);
        if ($image !== null) {
            $data['image'] = $image;
        }

        $item = $category->items()->create($data);
        $this->syncRealExamCalendar($item);

        return redirect()->back()->with('success', 'Item added.');
    }

    public function updateItem(Request $request, CurriculumItem $item)
    {
        $data = $this->validateItem($request);
        // Unlike store(), an absent checkbox here means the admin unchecked
        // it (standard HTML form semantics) - default to false, not true,
        // or saving the form with "Active" unticked would silently re-tick it.
        // Same reasoning for is_real_exam.
        $data['is_active'] = $request->boolean('is_active', false);
        $data['is_real_exam'] = $request->boolean('is_real_exam', false);

        // Only touch the image column when a new file was actually
        // uploaded - $data otherwise has no 'image' key at all here, so
        // update() leaves the existing image alone instead of nulling it
        // out on every save (the validated array never carries the raw
        // UploadedFile object into update() - see uploadCurriculumImage()).
        $image = $this->uploadCurriculumImage($request);
        if ($image !== null) {
            $data['image'] = $image;
        }

        $previousTitle = $item->title;
        $item->update($data);
        $this->syncRealExamCalendar($item, $previousTitle);

        return redirect()->back()->with('success', 'Item updated.');
    }

    /**
     * A real exam's calendar event fans out to every class, not one - a
     * CurriculumItem is scoped only by the global grade_level_id on its
     * category (one Grade Level already spans multiple schools' classes),
     * unlike mba_position/finhero_activity_registry which are set up
     * per-class to begin with. Known, accepted limitation: this syncs to
     * the classes that exist right now; a class created for this Grade
     * Level afterward won't retroactively get the event unless the admin
     * re-saves (bugs.md, 2026-10-21).
     *
     * Deletes-then-recreates by title match (same "no item_id column on
     * calendar_events" constraint every other controller here works
     * around) rather than trying to diff surgically - simple and correct
     * at this app's scale, and naturally handles every edge case (title
     * changed, is_real_exam turned off, dates cleared) through the same
     * path instead of needing special-case branches for each.
     */
    private function syncRealExamCalendar(CurriculumItem $item, ?string $previousTitle = null): void
    {
        if ($previousTitle !== null && $previousTitle !== $item->title) {
            DB::table('calendar_events')->where('title', 'exam-' . $previousTitle)->delete();
        }

        $title = 'exam-' . $item->title;

        if (!$item->is_real_exam || !$item->start_date) {
            DB::table('calendar_events')->where('title', $title)->delete();

            return;
        }

        $classes = SchoolClass::whereHas('grade', function ($query) use ($item) {
            $query->where('grade_level_id', $item->category->grade_level_id);
        })->get();

        // Same FullCalendar exclusive-end +1-day convention used by every
        // other "position" screen this week (see MbaPositionController).
        $calendarStart = \Carbon\Carbon::parse($item->start_date)->startOfDay();
        $calendarEnd = $item->end_date
            ? \Carbon\Carbon::parse($item->end_date)->addDay()->startOfDay()
            : $calendarStart->copy()->addDay();

        foreach ($classes as $class) {
            $existing = DB::table('calendar_events')
                ->where('sid', $class->sid)
                ->where('classId', $class->id)
                ->where('title', $title)
                ->first();

            $fields = [
                'position' => 'required',
                'backgroundColor' => '#dc3545',
                'borderColor' => '#dc3545',
                'start' => $calendarStart,
                'end' => $calendarEnd,
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('calendar_events')->where('id', $existing->id)->update($fields);
            } else {
                DB::table('calendar_events')->insert($fields + [
                    'sid' => $class->sid,
                    'classId' => $class->id,
                    'title' => $title,
                    'description' => $item->description ?? '',
                    'repeatActivity' => '0',
                    'created_at' => now(),
                ]);
            }
        }
    }

    /**
     * Moves an uploaded image (an item's stimulus graphic, or a question's
     * own chart) into public/uploads/curriculum and returns its filename,
     * or null if no file was uploaded this request. Kept out of
     * validateItem()'s returned array on purpose - spreading the raw
     * UploadedFile object into storeItem()/updateItem()'s create()/
     * update() calls would throw (same upload convention as
     * Admin\CityMapController/NpoController/CityMallStoreController etc.,
     * none of which use the Storage facade in this app).
     */
    private function uploadCurriculumImage(Request $request, string $field = 'image'): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/curriculum'), $filename);

        return $filename;
    }

    public function destroyItem(CurriculumItem $item)
    {
        if ($item->is_real_exam) {
            DB::table('calendar_events')->where('title', 'exam-' . $item->title)->delete();
        }

        $item->delete();

        return redirect()->back()->with('success', 'Item deleted.');
    }

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'type' => 'required|in:quiz,content',
            'pisa_proficiency_level_id' => 'nullable|exists:pisa_proficiency_levels,id',
            'sort_order' => 'required|integer|min:1',
            // Not gated to any particular category (curriculum_categories
            // has no stable type signal, only a free-text, admin-editable
            // name) - shown on every item's form regardless of category,
            // same as pisa_proficiency_level_id already is. Display/
            // calendar metadata only; not an access window.
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }

    public function questions(CurriculumItem $item)
    {
        $questions = $item->questions()->with('options')->get();

        return view('admin.curriculum.questions', compact('item', 'questions'));
    }

    /**
     * Create-or-update a question + its options in one request, mirroring
     * AdminDashboardController::loginQuestionStore()'s shape exactly (an
     * optional hidden id decides create vs update; options are matched back
     * to DB rows by array index via a parallel option_ids[] array, not by
     * any value in option_text). Unlike that method, this one validates
     * (a real exam needs at least one marked-correct option - silently
     * grading every student wrong on a malformed question is a much worse
     * failure mode here than it is for the single daily Login Quiz
     * question) and wraps the question+options write in a transaction.
     */
    public function saveQuestion(Request $request, CurriculumItem $item)
    {
        $data = $request->validate([
            'id' => 'nullable|integer',
            'question_text' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            // Daniel, 2026-10-21: "are you sure I can add two graphics to
            // compare... sometimes I will compare one graphic, sometimes
            // they just need to answer from only one" - image_2 is only
            // filled in for the two-graphic comparison case.
            'image_2' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'sort_order' => 'required|integer|min:1',
            'options' => 'required|array|min:2|max:4',
            'options.*' => 'nullable|string|max:255',
            'option_ids' => 'nullable|array',
            'correct' => 'required|integer',
        ]);

        $correctIndex = (int) $data['correct'];
        $nonEmptyOptions = array_filter($data['options'], fn ($o) => trim((string) $o) !== '');
        abort_unless(count($nonEmptyOptions) >= 2, 422, 'A question needs at least 2 options.');
        abort_unless(
            array_key_exists($correctIndex, $data['options']) && trim((string) $data['options'][$correctIndex]) !== '',
            422,
            'Pick which option is correct.'
        );

        // Moved out of the transaction below on purpose - file-system I/O
        // doesn't belong inside a DB transaction, and (as in storeItem()/
        // updateItem()) the raw UploadedFile must never reach a
        // create()/update() array directly.
        $image = $this->uploadCurriculumImage($request);
        $image2 = $this->uploadCurriculumImage($request, 'image_2');

        DB::transaction(function () use ($request, $data, $correctIndex, $item, $image, $image2) {
            if (!empty($data['id'])) {
                $question = CurriculumQuestion::where('item_id', $item->id)->findOrFail($data['id']);
                $fields = [
                    'question_text' => $data['question_text'],
                    'sort_order' => $data['sort_order'],
                ];
                // Only touch the image columns when a new file was actually
                // uploaded, so re-saving a question without re-picking a
                // file doesn't wipe its existing image.
                if ($image !== null) {
                    $fields['image'] = $image;
                }
                if ($image2 !== null) {
                    $fields['image_2'] = $image2;
                }
                $question->update($fields);
            } else {
                $question = CurriculumQuestion::create([
                    'item_id' => $item->id,
                    'question_text' => $data['question_text'],
                    'image' => $image,
                    'image_2' => $image2,
                    'sort_order' => $data['sort_order'],
                ]);
            }

            foreach ($data['options'] as $key => $optionText) {
                if (trim((string) $optionText) === '') {
                    continue;
                }

                $optionId = $request->input("option_ids.$key");
                $fields = [
                    'option_text' => $optionText,
                    'is_correct' => ($key === $correctIndex),
                    'sort_order' => $key + 1,
                ];

                if ($optionId) {
                    CurriculumQuestionOption::where('question_id', $question->id)->where('id', $optionId)->update($fields);
                } else {
                    CurriculumQuestionOption::create($fields + ['question_id' => $question->id]);
                }
            }
        });

        $message = !empty($data['id']) ? 'Question updated.' : 'Question added.';

        return redirect()->back()->with('success', $message);
    }

    public function destroyQuestion(CurriculumQuestion $question)
    {
        $question->delete();

        return redirect()->back()->with('success', 'Question deleted.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\CurriculumQuestion;
use App\Models\CurriculumQuestionOption;
use App\Models\GradeLevel;
use App\Models\PisaProficiencyLevel;
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

        $category->items()->create($data);

        return redirect()->back()->with('success', 'Item added.');
    }

    public function updateItem(Request $request, CurriculumItem $item)
    {
        $data = $this->validateItem($request);
        // Unlike store(), an absent checkbox here means the admin unchecked
        // it (standard HTML form semantics) - default to false, not true,
        // or saving the form with "Active" unticked would silently re-tick it.
        $data['is_active'] = $request->boolean('is_active', false);

        $item->update($data);

        return redirect()->back()->with('success', 'Item updated.');
    }

    public function destroyItem(CurriculumItem $item)
    {
        $item->delete();

        return redirect()->back()->with('success', 'Item deleted.');
    }

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:quiz,content',
            'pisa_proficiency_level_id' => 'nullable|exists:pisa_proficiency_levels,id',
            'sort_order' => 'required|integer|min:1',
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

        DB::transaction(function () use ($request, $data, $correctIndex, $item) {
            if (!empty($data['id'])) {
                $question = CurriculumQuestion::where('item_id', $item->id)->findOrFail($data['id']);
                $question->update([
                    'question_text' => $data['question_text'],
                    'sort_order' => $data['sort_order'],
                ]);
            } else {
                $question = CurriculumQuestion::create([
                    'item_id' => $item->id,
                    'question_text' => $data['question_text'],
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

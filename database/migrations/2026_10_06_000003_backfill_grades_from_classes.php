<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// For every existing `classes` row, create one new `grades` row carrying
// its name/sid/grade_level_id, and point classes.grade_id at it. This
// preserves today's 1 Grade : 1 Class behavior exactly for every existing
// school — multi-Class Grades are something School Admins create going
// forward via the rebuilt admin screens, not something this migration
// invents. Irreversible by design (down() is a no-op), same convention as
// the civic-chamber school backfill.
return new class extends Migration {
    public function up()
    {
        $classes = DB::table('classes')->whereNull('grade_id')->get();

        foreach ($classes as $class) {
            $gradeId = DB::table('grades')->insertGetId([
                'sid' => $class->sid,
                'name' => $class->name,
                'grade_level_id' => $class->grade_level_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('classes')->where('id', $class->id)->update(['grade_id' => $gradeId]);
        }
    }

    public function down()
    {
        // Intentionally irreversible - see class comment.
    }
};

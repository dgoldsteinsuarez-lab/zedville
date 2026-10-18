<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class LoginQuestion extends Model
{
    public $timestamps = false;
    protected $fillable = ['sid', 'grade_level_id', 'month_name','month_id','question','type'];

    public function options()
    {
        //return $this->hasMany(LoginOption::class, 'question_id');
        return $this->hasMany(LoginOption::class, 'question_id', 'id');
    }

    public function month()
    {
        return $this->belongsTo(Month::class);
    }

    public function level()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }
    public function school()
    {
        return $this->belongsTo(\App\Models\SchoolDomain::class, 'sid', 'id');
    }
}
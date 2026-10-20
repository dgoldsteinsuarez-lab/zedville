<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    protected $fillable = [
        'sid',
        'name',
        'trigger_event',
        'subject',
        'content',
    ];

    public $timestamps = true;

    public function campaigns()
    {
        return $this->hasMany(EmailCampaign::class, 'template_id');
    }

    public function school()
    {
        return $this->belongsTo(SchoolDomain::class, 'sid');
    }
}

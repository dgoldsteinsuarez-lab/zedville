<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaignSend extends Model
{
    protected $table = 'email_campaign_sends';

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'student_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }
}

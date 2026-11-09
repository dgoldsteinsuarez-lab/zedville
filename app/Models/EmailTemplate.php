<?php

namespace App\Models;

use App\Helpers\MailboxHelper;
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
        'translations',
    ];

    protected $casts = [
        'translations' => 'array',
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

    /**
     * The versions an admin wrote in the other languages, e.g.
     * ['es' => ['subject' => ..., 'content' => ...]] (Daniel, 2026-10-25:
     * "debería haber templates para cada idioma"). The main subject/content
     * is the English one, and what a student whose language has no version
     * here receives.
     */
    public function versions(): array
    {
        return array_filter($this->translations ?? [], fn ($v) => filled($v['subject'] ?? null) && filled($v['content'] ?? null));
    }

    /**
     * The versions as they are stored on a sent message (mailbox.template_data),
     * with the student's details filled into the content - the Mailbox model
     * then shows the one in the reader's language. Null when there are none.
     */
    public function versionsFor(array $data, ?callable $subject = null): ?array
    {
        $versions = array_map(fn ($v) => [
            'subject' => $subject ? $subject($v['subject']) : $v['subject'],
            'content' => MailboxHelper::applyPlaceholders($v['content'], $data),
        ], $this->versions());

        return $versions ? ['translations' => $versions] : null;
    }
}

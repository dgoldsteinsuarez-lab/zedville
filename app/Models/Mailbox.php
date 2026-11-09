<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

class Mailbox extends Model
{
    protected $table = 'mailbox';

    protected $fillable = [
        'student_id', 'sid', 'subject', 'content', 'type', 'template', 'template_data', 'read', 'adminemail', 'created_at', 'updated_at'
    ];

    protected $casts = [
        'template_data' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /*
     * Daniel, 2026-10-25: a message sent from one of the city's templates
     * (resources/views/mailbox_templates) is shown in the reader's language,
     * rendered when it's read; the stored English text is the fallback (and
     * what a message an admin wrote by hand always shows).
     */
    public function getSubjectAttribute($value)
    {
        if ($version = $this->adminVersion()) {
            return $version['subject'];
        }

        if ($this->templateView() && \Lang::has("mail.{$this->template}.subject")) {
            return __("mail.{$this->template}.subject", ['name' => $this->readerName(), 'month' => $this->monthLabel()]);
        }

        return $value;
    }

    public function getContentAttribute($value)
    {
        if ($version = $this->adminVersion()) {
            return $version['content'];
        }

        if (!$view = $this->templateView()) {
            return $value;
        }

        try {
            return view($view, array_merge($this->template_data ?? [], [
                'user' => $this->student,
                'month' => $this->template_data['month'] ?? null,
            ]))->render();
        } catch (\Throwable $e) {
            return $value;
        }
    }

    /**
     * A message from an admin's template (Admin > Mail Box > Email Templates)
     * in the reader's language, when the admin wrote that version - see
     * EmailTemplate::versionsFor(). Null: show the main version.
     */
    private function adminVersion(): ?array
    {
        $version = $this->template_data['translations'][app()->getLocale()] ?? null;

        return is_array($version) ? $version : null;
    }

    private function templateView(): ?string
    {
        $template = $this->attributes['template'] ?? null;
        if (!$template || !$this->student) {
            return null;
        }

        $view = 'mailbox_templates.' . $template;

        return View::exists($view) ? $view : null;
    }

    private function readerName(): string
    {
        return (string) ($this->student->nickname ?: $this->student->name);
    }

    private function monthLabel(): string
    {
        $month = $this->template_data['month'] ?? null;

        return $month ? \Carbon\Carbon::createFromFormat('Y-m', $month)->locale(app()->getLocale())->isoFormat(__('transactions.month_year')) : '';
    }
}

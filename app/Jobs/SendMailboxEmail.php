<?php

namespace App\Jobs;

use App\Models\Mailbox;
use App\Helpers\MailboxHelper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMailboxEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $template;
    protected $subject;
    protected $extraData;

    


    public function __construct($userId, $template, $subject, $extraData = [])
    {
        $this->userId = $userId;
        $this->template = $template;
        $this->subject = $subject;
        $this->extraData = $extraData;
    }

    public function handle()
{
    $user = User::find($this->userId);
    if (!$user) {
        return; // user deleted
    }

    // 🔹 Replace placeholders in subject
    
    /*$subject = str_replace(
        ['{{citizenId}}', '{{name}}'],
        [$user->citizenId, $user->name],
        $this->subject
    );*/
    $subject = $this->subject ?? '';
    $replacements = array_merge([
        'citizenId' => $user->citizenId,
        'name'      => $user->nickname,
    ], $this->extraData);

    foreach ($replacements as $key => $value) {
        $subject = str_replace('{{' . $key . '}}', $value, $subject);
    }
    // 🔹 Make sure Laravel loads from resources/views/mailbox_templates
    //$viewPath = "mailbox_templates.{$this->template}";

    // 🔹 Render template with user data
    //$content = MailboxHelper::renderMailboxTemplate($viewPath, ['user' => $user]);
    
     $data = array_merge(['user' => $user], $this->extraData);
    $content = MailboxHelper::renderMailboxTemplate($this->template, $data);

    // 🔹 Save to mailbox table
    Mailbox::create([
        'student_id' => $user->id,
        'subject'    => $subject,
        'content'    => $content,
        'type'       => 'primary',
        'read'       => 0,
    ]);
}

}

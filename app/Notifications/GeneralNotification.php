<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;


class GeneralNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $message;

    public function __construct($title, $message)
    {
        $this->title = $title;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database']; // سيتم تخزين الإشعار في قاعدة البيانات
    }

    public function toDatabase($notifiable)
    {
        // dd($this->message);
        return [
            'title' => $this->title,
            'message' => $this->message,
        ];
    }
}

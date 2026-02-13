<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MatchNotification extends Notification
{
    use Queueable;

    protected $match;
    protected $role;

    public function __construct($match, $role)
    {
        $this->match = $match;
        $this->role = $role;
    }

    public function via($notifiable)
    {
        return ['database']; // or ['mail', 'database']
    }

    public function toArray($notifiable)
    {
        if ($this->role == 'donor') {
            return [
                'message' => 'You have been matched with a receiver.',
                'receiver_name' => $this->match->receiver->name,
                'receiver_phone' => $this->match->receiver->phone,
                'receiver_email' => $this->match->receiver->email,
            ];
        }

        return [
            'message' => 'You have been matched with a donor.',
            'donor_name' => $this->match->donor->name,
            'donor_phone' => $this->match->donor->phone,
            'donor_email' => $this->match->donor->email,
        ];
    }
}

<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class FeedbackReceivedAdmin extends Mailable
{
    public $feedback;

    public function __construct($feedback)
    {
        $this->feedback = $feedback;
    }

    public function build()
    {
        return $this->subject('New Feedback Received')
            ->view('emails.feedback-admin');
    }
}

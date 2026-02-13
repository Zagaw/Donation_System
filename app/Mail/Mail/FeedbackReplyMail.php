<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class FeedbackReplyMail extends Mailable
{
    public $feedback;

    public function __construct($feedback)
    {
        $this->feedback = $feedback;
    }

    public function build()
    {
        return $this->subject('Reply to your feedback')
            ->view('emails.feedback-reply');
    }
}

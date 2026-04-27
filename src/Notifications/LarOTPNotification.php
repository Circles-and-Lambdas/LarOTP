<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class LarOTPNotification
{
    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('OTP Request')
            ->markdown('larotp::email', ['otp' => $this->otp]);
    }
}

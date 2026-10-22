<?php

namespace App\Listeners;

use App\Events\RegistrationCancelled;
use App\Mail\RegistrationCancelledMail;
use Illuminate\Support\Facades\Mail;

class SendCancellationEmail
{
    public function handle(RegistrationCancelled $cancelled): void
    {
        Mail::to($cancelled->participant)->queue(new RegistrationCancelledMail($cancelled->event, $cancelled->participant));
    }
}

<?php

namespace App\Listeners;

use App\Mail\NewUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NewUserNotification implements ShouldQueue {

  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Handle the event.
   *
   * @param Registered $event
   *
   * @return void
   */
  public function handle(Registered $event)
  {
    $to = config('cultuzz.notify_email');
    if (!$to) return;
    Mail::send(new NewUser($event->user, $to));
  }
}

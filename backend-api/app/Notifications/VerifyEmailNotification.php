<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue {

  use Queueable;

  public function __construct()
  {
    $this->onQueue('mail');
    $this->locale = app()->getLocale();
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param mixed $notifiable
   *
   * @return MailMessage
   */
  public function toMail($notifiable)
  {
    $url = $this->getUrl($notifiable);
    return (new MailMessage)
      ->subject(__('mail.verify.subject'))
      ->line(__('mail.welcome'))
      ->line(__('mail.verify.heading'))
      ->action(__('mail.verify.button'), $url)
      ->line(__('mail.contact', ['email' => '[info@cultbooking.com](mailto:info@cultbooking.com)']));
  }

  public function getUrl($notifiable)
  {
    $url = $this->verificationUrl($notifiable);
    return Str::replaceFirst(config('app.url'), config('app.frontend_url'), $url);
  }

}

<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

class InviteUserNotification extends VerifyEmail implements ShouldQueue {

  use Queueable;

  private string $password;

  public function __construct(string $password)
  {
    $this->password = $password;
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
      ->subject(__('mail.invite_user.subject'))
      ->line(__('mail.welcome'))
      ->line(__('mail.invite_user.password', ['password' => $this->password]))
      ->action(__('mail.invite_user.button'), $url)
      ->line(__('mail.contact', ['email' => '[info@cultbooking.com](mailto:info@cultbooking.com)']));
  }

  public function getUrl($notifiable)
  {
    $url = $this->verificationUrl($notifiable);
    $url = Str::replaceFirst(config('app.url'), config('app.frontend_url'), $url);

    return $url;
  }

}

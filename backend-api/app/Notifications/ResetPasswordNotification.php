<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue {

  use Queueable;

  public function __construct($token)
  {
    parent::__construct($token);
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
    /** @var User $notifiable */
    $name = $notifiable->fullName;
    $greeting = 'mail.reset.greeting';
    if(!is_null($name)) {
      $greeting .= '_name';
    }
    $count = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
    $mail = parent::toMail($notifiable);
    $url = Str::replaceFirst(config('app.url'), config('app.frontend_url'), $mail->actionUrl);
    $contact = __('mail.contact', ['email' => '[info@cultbooking.com](mailto:info@cultbooking.com)']);
    $mail = (new MailMessage)
      ->subject(__('mail.reset.subject'))
      ->line(__($greeting, compact('name')))
      ->line(__('mail.reset.heading'))
      ->action(__('mail.reset.button'), $url)
      ->line(trans_choice('mail.reset.link_expiration', $count, compact('count')))
      ->line(__('mail.reset.tip', compact('contact')));

    return $mail;
  }

}

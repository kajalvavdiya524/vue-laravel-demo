<?php

namespace App\Mail;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\Authenticatable;

class NewUser extends Mailable {

  private $user;
  private $sendTo;

  /**
   * Create a new message instance.
   *
   * @param Authenticatable $user
   * @param string $to
   */
  public function __construct(Authenticatable $user, $to)
  {
    parent::__construct();
    $this->user = $user;
    $this->sendTo = $to;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    $url = (new VerifyEmailNotification())->getUrl($this->user);
    return $this->to($this->sendTo)
                ->subject("[report:{$this->user->id}] New registration")
                ->view('emails.info.registered', [
                  'id'    => $this->user->id,
                  'email' => $this->user->email,
                  'url'   => $url,
                  'date'  => $this->user->created_at->format('Y-m-d H:i:s'),
                ]);
  }
}

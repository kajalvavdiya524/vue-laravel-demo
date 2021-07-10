<?php

namespace App\Providers;

use App\Events\HotelRegistered;
use App\Events\ProfileFilled;
use App\Events\SetupComplete;
use App\Listeners\HotelRegisteredNotification;
use App\Listeners\NewUserNotification;
use App\Listeners\ProfileFilledNotification;
use App\Listeners\SetupCompleteNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

  /**
   * The event listener mappings for the application.
   *
   * @var array
   */
  protected $listen = [
    Registered::class      => [
      SendEmailVerificationNotification::class,
      NewUserNotification::class,
    ],
    ProfileFilled::class   => [
      ProfileFilledNotification::class,
    ],
    HotelRegistered::class => [
      HotelRegisteredNotification::class,
    ],
    SetupComplete::class   => [
      SetupCompleteNotification::class,
    ],
  ];

  /**
   * Register any events for your application.
   *
   * @return void
   */
  public function boot()
  {
    parent::boot();
  }
}

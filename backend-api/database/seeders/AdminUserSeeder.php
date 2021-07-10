<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\User;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder {

  /**
   * Run the database seeders.
   *
   * @return void
   */
  public function run()
  {
    if (User::count() || !app()->environment('local')) {
      return;
    }

    /** @var User $user */

    # create user
    $user = User::create([
      'email'      => config('dev.admin_email'),
      'password'   => Hash::make(config('dev.admin_password')),
      'tos_agreed' => true,
      'admin'      => true,
    ]);
    $user->email_verified_at = Carbon::now();
    $user->save();

    # fill user details
    $data = [
      'first_name' => 'John',
      'last_name'  => 'Smith',
    ];
    $user->updatePersonalDetails($data);
    $user->cd_filled_at = $user->freshTimestamp();
    $user->setup_at = $user->freshTimestamp();
    $user->save();

    $user = $user->fresh(['profile']);

    # create test hotel
    Hotel::create([
      'name'        => 'Paradise Lounge',
      'id'          => 58549,
      'ctx'         => 'CLTZ',
      'user_id'     => $user->id,
      'country_id'  => 232,
      'currency_id' => 2,
    ]);
  }
}

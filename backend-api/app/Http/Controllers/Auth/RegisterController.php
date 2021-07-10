<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller {

  /*
  |--------------------------------------------------------------------------
  | Register Controller
  |--------------------------------------------------------------------------
  |
  | This controller handles the registration of new users as well as their
  | validation and creation. By default this controller uses a trait to
  | provide this functionality without requiring any additional code.
  |
  */

  use RegistersUsers;

  /**
   * Where to redirect users after registration.
   *
   * @return string
   */
  protected function redirectTo()
  {
    return config('app.frontend_url');
  }

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('guest');
    $this->middleware('throttle:6,1')->only('register');
  }

  /**
   * Get a validator for an incoming registration request.
   *
   * @param array $data
   *
   * @return \Illuminate\Contracts\Validation\Validator
   */
  protected function validator(array $data)
  {
    return Validator::make($data, [
      'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
      'password'   => ['required', 'string', 'min:8'],
      'tos_agreed' => ['accepted'],
    ], [
      'tos_agreed.accepted' => 'You must accept this',
    ]);
  }

  /**
   * Create a new user instance after a valid registration.
   *
   * @param array $data
   *
   * @return User
   */
  protected function create(array $data)
  {
    return User::create([
      'email'      => $data['email'],
      'password'   => Hash::make($data['password']),
      'tos_agreed' => $data['tos_agreed'],
    ]);
  }
}

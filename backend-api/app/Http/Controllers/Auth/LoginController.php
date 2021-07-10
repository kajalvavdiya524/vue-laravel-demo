<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller {

  /*
  |--------------------------------------------------------------------------
  | Login Controller
  |--------------------------------------------------------------------------
  |
  | This controller handles authenticating users for the application and
  | redirecting them to your home screen. The controller uses a trait
  | to conveniently provide its functionality to your applications.
  |
  */

  use AuthenticatesUsers;

  /**
   * Where to redirect users after login.
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
    $this->middleware('guest')->except('logout');
  }

  /**
   * Redirect the user after determining they are locked out.
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return void
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  protected function sendLockoutResponse(Request $request)
  {
    $seconds = $this->limiter()->availableIn(
      $this->throttleKey($request)
    );

    $try_again_in = trans_choice('auth.throttle_try_again_in', $seconds, compact('seconds'));

    throw ValidationException::withMessages([
      $this->username() => [
        __('auth.throttle', compact('try_again_in')),
      ],
    ])->status(Response::HTTP_TOO_MANY_REQUESTS);
  }

  protected function authenticated(Request $request, User $user)
  {
    $request->session()->put('group', $user->group);
  }
}

<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\UsersCollection;
use App\Models\Hotel;
use App\Models\User;
use App\Rules\Password;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller {

  /**
   * @param Request $request
   * @param User|null $user
   * @param bool $delete
   *
   * @return array|true
   */
  private function validatePayloadAndRoles(Request $request, User $user = null, bool $delete = false)
  {
    $parent = $this->user($request);
    if (!$delete) {
      $requiredUnlessEdit = $user ? 'sometimes' : 'required';
      $rules = [
        'email'              => [$requiredUnlessEdit, 'string', 'email', 'max:255'], // should be in array form
        'password'           => [$requiredUnlessEdit, 'string', new Password],
        'profile.first_name' => 'required|string|max:255',
        'profile.last_name'  => 'required|string|max:255',
        'profile.tel'        => 'required|regex:/^\+\d{8,15}$/',
        'lang'               => 'required|string|size:2',
        'avatar.upload'      => 'nullable|file|mimetypes:image/png,image/jpeg',
        'roles'              => 'nullable|array',
        'roles.*'            => 'numeric|exists:roles,id',
        'all_group_hotels'   => 'boolean',
        'apages'             => 'array',
        'apages.*'           => 'sometimes|exists:pages,name',
        'pages'              => 'array',
        'pages.*'            => 'sometimes|exists:pages,name',
      ];
      if ($parent->admin) {
        $rules += ['group_id' => 'required|numeric|exists:groups,id'];
      }
      $uniq = Rule::unique('users');
      if ($user) {
        $uniq = $uniq->ignore($user->id);
        $rules += [
          'avatar.remove' => 'boolean',
        ];
      }
      $rules['email'][] = $uniq;
      $payload = $request->validate($rules);
    }

    $hotel = $this->hotel();
    if ($user) {
      if (!$parent->admin && $user->group_id && $user->group_owner) {
        abort(403, 'Access Denied');
      }
      if ($parent->id === $user->id) {
        abort(403, 'Access Denied');
      }
      if ($user->group_id != $parent->group_id) {
        abort(403, 'Access Denied');
      }
      if (!$user->isSubordinateOf($parent->id)) {
        abort(403, 'Access Denied');
      }
    }
    if (!$delete) {
      if ($hotel && !$hotel->isHotelRoles(Arr::get($payload, 'roles', []))) {
        abort(403, 'Access Denied');
      }
      if (collect(Arr::get($payload, 'apages', []))->diff($parent->adminPagesForGroup())->isNotEmpty()) {
        abort(403, 'Access Denied');
      }
      if (collect(Arr::get($payload, 'pages', []))->diff($parent->pagesForHotel($hotel))->isNotEmpty()) {
        abort(403, 'Access Denied');
      }
      if (!$user) {
        $payload += [
          'parent' => $parent,
        ];
      } else {
        $payload += [
          'hotel' => $hotel,
        ];
      }
    }

    return $delete ?: $payload;
  }

  /**
   * @param Request $request
   *
   * @return UsersCollection
   */
  public function index(Request $request): UsersCollection
  {
    $user = $this->user($request);
    $hotel = $this->hotel();
    $users = User::listWithRolesForHotel($hotel, $user);
    return UserResource::collection($users);
  }

  /**
   * @param Request $request
   *
   * @return UserResource
   * @throws ValidationException
   */
  public function createInviteUser(Request $request): UserResource
  {
    $root = $this->user($request);
    $payload = $this->validate($request, [
      'email'   => 'required|string|email|max:255|unique:users',
      'roles'   => 'array',
      'roles.*' => 'numeric|exists:roles,id',
    ]);
    // check that roles belong to current hotel
    // user's permission has already been checked by middleware
    $hotel = $this->hotel();
    if ($hotel && !$hotel->isHotelRoles(Arr::get($payload, 'roles', []))) {
      throw ValidationException::withMessages(['roles' => 'Invalid roles']);
    }
    $invitee = User::createUserInvite($root, $payload);
    $invitee = User::listWithRolesForHotel($hotel, $root, $invitee->id);
    return UserResource::make($invitee);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   *
   * @return UserResource
   */
  public function store(Request $request): UserResource
  {
    $payload = $this->validatePayloadAndRoles($request);
    $hotel = $this->hotel();
    $root = $this->user($request);
    $user = User::createNew($payload);
    if (!$root->admin) {
      $user = User::listWithRolesForHotel($hotel, $root, $user->id);
    }
    return UserResource::make($user);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   * @param User $user
   *
   * @return UserResource
   */
  public function update(Request $request, User $user): UserResource
  {
    $payload = $this->validatePayloadAndRoles($request, $user);
    $hotel = $this->hotel();
    $root = $this->user($request);
    $user = $user->modify($payload);
    $user = User::listWithRolesForHotel($hotel, $root, $user->id);
    return UserResource::make($user);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param Request $request
   * @param User $user
   *
   * @return array
   * @throws Exception
   */
  public function destroy(Request $request, User $user): array
  {
    $this->validatePayloadAndRoles($request, $user, true);
    $user->delete();
    return ['ok' => true];
  }
}

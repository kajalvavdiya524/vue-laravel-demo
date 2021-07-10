<?php

namespace App\Models;

use App\Contracts\MustFillDetails;
use App\Notifications\InviteUserNotification;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Support\AsIntCollection;
use App\Support\IntCollection;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens as HasSanctumTokens;

/**
 * Class User
 * @package App\Models
 *
 * @property int $id
 * @property string $email
 * @property Carbon $email_verified_at
 * @property Carbon $pd_filled_at
 * @property Carbon $cd_filled_at
 * @property Carbon $setup_at
 * @property int $setup_step
 * @property IntCollection $parents
 * @property bool $admin
 * @property int|null $group_id
 * @property bool $group_owner
 * @property bool $all_group_hotels
 * @property string $lang
 *
 * @property-read string $fullName
 * @property-read bool $emailVerified
 * @property-read bool $pdFilled
 * @property-read bool $cdFilled
 * @property-read bool $setupComplete
 * @property-read Carbon $created_at
 *
 * @property-read UserProfile|null $profile
 * @property-read UserImage|null $image
 * @property-read string|null $avatar
 *
 * @property-read Group|null $group
 * @property-read DBCollection|Page[] $pages
 *
 * @property-read DBCollection|Hotel[] $hotels
 * @property-read DBCollection|Hotel[] $createdHotels
 * @property-read DBCollection|Role[] $roles
 * @property-read DBCollection|Role[] $rolesWithPages
 * @property-read DBCollection|Role[] $applyingRoles
 * @property-read DBCollection|Role[] $createdRoles
 *
 */
class User extends Authenticatable implements MustVerifyEmail, MustFillDetails, CanResetPassword {

  use Notifiable, HasSanctumTokens, HasFactory;

  protected $fillable = [
    'email',
    'password',
    'tos_agreed',
    'lang',
    'all_group_hotels',
  ];

  protected $hidden = [
    'password', 'remember_token', 'source_id', 'parents',
    'email_verified_at', 'pd_filled_at', 'cd_filled_at', 'setup_at',
    'created_at', 'updated_at', 'image',
  ];

  protected $casts = [
    'id'                => 'int',
    'tos_agreed'        => 'boolean',
    'email_verified_at' => 'datetime',
    'cd_filled_at'      => 'datetime',
    'pd_filled_at'      => 'datetime',
    'setup_at'          => 'datetime',
    'setup_step'        => 'int',
    'admin'             => 'boolean',
    'group_owner'       => 'boolean',
    'all_group_hotels'  => 'boolean',
    'parents'           => AsIntCollection::class,
  ];

  protected $appends = [
    'email_verified', 'pd_filled', 'cd_filled', 'setup_complete', 'hotels', 'avatar',
  ];

  protected $with = ['profile', 'group'];

  protected static function boot()
  {
    static::deleting(function (User $user) {
      optional($user->image)->delete();
      if ($user->group_id) {
        $parent = $user->parents->last();
        $user->createdHotels()->update(['user_id' => $parent]);
        $user->createdRoles()->update(['user_id' => $parent]);
        $user->subordinateIds()->each(function (User $s) use ($user) {
          $s->parents->remove($user->id);
          $s->save();
        });
      }
    });

    parent::boot();
  }

  public function sendPasswordResetNotification($token)
  {
    $this->notify(new ResetPasswordNotification($token));
  }

  public function sendEmailVerificationNotification()
  {
    $this->notify(new VerifyEmailNotification());
  }

  public function sendInviteNotification($password)
  {
    $this->notify(new InviteUserNotification($password));
  }

  public function hasDetailsFilled()
  {
    return !is_null($this->pd_filled_at) && !is_null($this->cd_filled_at);
  }

  public function hasPersonalDetailsFilled()
  {
    return !is_null($this->pd_filled_at);
  }

  public function updateProperty($propertyDetails)
  {
    $this->profile->update($propertyDetails);

    return $this;
  }

  public function updateAddress($contactDetails)
  {

    if ($countryCode = Arr::get($contactDetails, 'country', null)) {
      $country = Country::query()->where('code', $countryCode)->first();
      $contactDetails['country_id'] = $country->id;
      Arr::forget($contactDetails, 'country');
    }
    $this->profile->update($contactDetails);

    return $this;
  }

  public function updatePersonalDetails($personalDetails)
  {
    if (is_null($this->profile)) {
      $profile = new UserProfile($personalDetails);
      $profile->user_id = $this->id;
      $profile->save();
    } else {
      $this->profile->update($personalDetails);
    }
    $this->pd_filled_at = $this->freshTimestamp();
    $this->save();
  }

  public function updateSetupStep($step)
  {
    if ($step > 5 || $step < 0) {
      $this->setup_at = $this->freshTimestamp();
    }
    if ($step >= 0) {
      // continue setup
      $this->setup_step = $step;
    }
    $this->save();
  }

  /**
   * @param array $attributes
   *
   * @return self
   */
  public static function createNew(array $attributes): self
  {
    $attributes['password'] = Hash::make($attributes['password']);
    /** @var self $user */
    $user = static::create($attributes);
    /** @var self $parent */
    if ($parent = Arr::get($attributes, 'parent')) {
      $now = Carbon::now();
      $go = Arr::get($attributes, 'group_id');
      $attrs = [
        'parents'           => $go ? null : $parent->parents->add($parent->id),
        'group_id'          => $go ?: $parent->group_id,
        'group_owner'       => !!$go,
        'tos_agreed'        => false,
        'email_verified_at' => $now,
        'pd_filled_at'      => $now,
        'cd_filled_at'      => $now,
        'setup_at'          => $now,
      ];
      self::unguarded(fn () => $user->update($attrs));
    }
    $user->roles()->attach(Arr::get($attributes, 'roles', []));
    $user->profile()->create(Arr::get($attributes, 'profile'));
    $pages = collect(Arr::get($attributes, 'apages', []))
      ->merge(Arr::get($attributes, 'pages', []));
    $user->pages()->attach(Page::idsByNames($pages));

    if ($avatar = Arr::get($attributes, 'avatar.upload')) {
      // create new image
      UserImage::create($avatar, $user);
    }
    return $user->fresh('profile', 'image');
  }

  /**
   * @param array $attributes
   *
   * @return self
   */
  public function modify(array $attributes): self
  {
    if ($password = Arr::get($attributes, 'password')) {
      Arr::set($attributes, 'password', Hash::make($password));
    }
    $this->update($attributes);
    $this->profile()->update(Arr::get($attributes, 'profile'));

    /** @var Hotel $hotel */
    $hotel = Arr::get($attributes, 'hotel');
    if ($hotel) {
      $oldRoles = $this->rolesForHotel($hotel);
      $this->roles()->detach($oldRoles);
      $this->roles()->attach(Arr::get($attributes, 'roles', []));
    }

    $pages = collect(Arr::get($attributes, 'apages', []))
      ->merge(Arr::get($attributes, 'pages', []));
    $this->pages()->sync(Page::idsByNames($pages));

    if (Arr::get($attributes, 'avatar.remove', false)) {
      // remove existing image
      optional($this->image)->delete();
    }
    if ($avatar = Arr::get($attributes, 'avatar.upload')) {
      // create/replace image
      optional($this->image)->delete();
      UserImage::create($avatar, $this);
    }

    return $this->fresh('profile', 'image');
  }

  /**
   * @param $group_id
   * @param bool $single
   *
   * @return DBCollection|self[]|self
   */
  public static function listWithRolesForGroup($group_id, $single = false)
  {
    $users = self::query()
                 ->where(compact('group_id'))
                 ->with(['roles' => fn ($q) => $q->select('id', 'name')->without('pages')])
                 ->without('group')
                 ->get()
                 ->each(fn (User $u) => ($u->makeHidden(['hotels', 'group'])));
    return $single ? $users->first() : $users;
  }

  /**
   * @param Hotel|null $hotel
   * @param bool $single
   *
   * @return DBCollection|mixed
   */
  public static function listWithRolesForHotel(?Hotel $hotel, User $root, $id = null)
  {
    $hid = $hotel->id ?? 0;
    $q = self::query()
             ->where('group_id', $root->group_id)
             ->whereJsonContains('parents', $root->id)
             ->with([
               'pages',
               'roles' => fn ($q) => $q->where('hotel_id', $hid)->select('id', 'name')->without('pages'),
             ])
             ->without('group');
    if ($id) {
      $q->where('id', $id);
    }
    $users = $q->get()
               ->each(fn (User $u) => ($u->makeHidden(['hotels', 'group'])));
    return $id ? $users->first() : $users;
  }

  public static function createUserInvite(User $parent, $data): ?self
  {
    $password = Str::random(10);
    $now = Carbon::now();
    $attributes = [
      'email'             => $data['email'],
      'password'          => Hash::make($password),
      'parents'           => $parent->parents->add($parent->id),
      'group_id'          => $parent->group_id,
      'lang'              => app()->getLocale(),
      'tos_agreed'        => false,
      'email_verified_at' => $now,
      'cd_filled_at'      => $now,
      'setup_at'          => $now,
    ];
    /** @var self $user */
    $user = static::unguarded(fn () => static::create($attributes));
    $user->roles()->attach(Arr::get($data, 'roles', []));
    $user->sendInviteNotification($password);
    return $user->fresh();
  }

  function subordinates(): Collection
  {
    return self::query()->whereJsonContains('parents', $this->id)->setEagerLoads([])->get();
  }

  function subordinateIds($includeSelf = false): Collection
  {
    $q = self::query()->whereJsonContains('parents', $this->id);
    if ($includeSelf) {
      $q->orWhere('id', $this->id);
    }
    return $q->setEagerLoads([])->get(['id'])->pluck('id');
  }

  function isSubordinateOf($id): bool
  {
    return self::query()->whereJsonContains('parents', $id)->where('id', $this->id)->exists();
  }

  function isRootOf($id): bool
  {
    return self::query()->whereJsonContains('parents', $this->id)->where('id', $id)->exists();
  }

  function rolesForHotel(?Hotel $hotel): Collection
  {
    if (!$hotel) return collect([]);
    return $this->roles->where('hotel_id', $hotel->id);
  }

  function pagesForHotel(?Hotel $hotel): Collection
  {
    if (!$this->group_id) {
      return Page::allHotelNames();
    }
    $g = $this->group->pages->filter(fn (Page $p) => $p->for_hotel)->pluck('name');
    if ($this->group_owner) {
      return $g;
    }
    $roles = $this->rolesForHotel($hotel)->reject(fn (Role $r) => $r->inherit_from_user);
    if ($this->all_group_hotels && $roles->isEmpty()) {
      return $this->pages->filter(fn (Page $p) => $p->for_hotel)->pluck('name')->intersect($g);
    }
    return $roles->flatMap->pages->pluck('name')->intersect($g);
  }

  function adminPagesForGroup(): Collection
  {
    if (!$this->group_id) {
      return Page::allUserNames(true);
    }
    if ($this->group_owner) {
      return $this->group->pages->reject(fn (Page $p) => $p->for_hotel)->pluck('name');
    }
    return $this->pages->reject(fn (Page $p) => $p->for_hotel)->pluck('name');
  }

  function hasAccessToHotel(Hotel $hotel): bool
  {
    if (!$this->group_id) {
      return $hotel->user_id === $this->id;
    }
    if ($this->group_owner || $this->all_group_hotels) {
      return $hotel->group_id === $this->group_id;
    }
    return $hotel->user_id === $this->id || $this->rolesForHotel($hotel)->isNotEmpty();
  }

  function setUserPages()
  {
    $this->perms = $this->adminPagesForGroup();
  }

//  function setHotelsPages()
//  {
//    $this->hotels->each(function (Hotel $hotel) {
//      $hotel->perms = $this->pagesForHotel($hotel);
//      logger()->info($hotel->perms);
//    });
//  }

  // Attributes

  public function getHotelsAttribute()
  {
    if (!$this->group_id) {
      // no group, standalone user, return their own hotels
      $hotels = $this->createdHotels;
    } elseif ($this->group_owner) {
      // if user is a group owner then fetch all group hotels
      $hotels = $this->group->hotels;
    } elseif ($this->all_group_hotels) {
      // return all hotels available to user's parent
      /** @var self $parent */
      $parent = self::query()->find($this->parents->last());
      $hotels = $parent->hotels;
    } else {
      // else fetch only hotels the user has roles for
      // merge with hotels of mine and subordinates
      $hotels = $this->applyingRoles->pluck('hotel')
                                    ->merge(Hotel::query()->whereIn('user_id', $this->subordinateIds(true))->get())
                                    ->unique('id')->values();
    }
    $hotels->each(fn (Hotel $hotel) => ($hotel->perms = $this->pagesForHotel($hotel)));
    return $hotels;
  }

  function getFullNameAttribute()
  {
    if (!$this->hasPersonalDetailsFilled()) {
      return null;
    }
    return $this->profile->first_name.' '.$this->profile->last_name;
  }

  function getEmailVerifiedAttribute()
  {
    return !is_null($this->email_verified_at);
  }

  function getPdFilledAttribute()
  {
    return !is_null($this->pd_filled_at);
  }

  function getCdFilledAttribute()
  {
    return !is_null($this->cd_filled_at);
  }

  function getSetupCompleteAttribute()
  {
    return !is_null($this->setup_at);
  }

  function getAvatarAttribute()
  {
    return $this->image->url ?? null;
  }

  // Relations

  function profile()
  {
    return $this->hasOne(UserProfile::class);
  }

  function image()
  {
    return $this->hasOne(UserImage::class);
  }

  function group()
  {
    return $this->belongsTo(Group::class);
  }

  function pages()
  {
    return $this->belongsToMany(Page::class);
  }

  /**
   * Returns user roles for <b>ALL</b> hotels
   */
  function roles()
  {
    return $this->belongsToMany(Role::class);
  }

  /**
   * Returns user roles with pages for <b>ALL</b> hotels
   */
  function rolesWithPages()
  {
    return $this->belongsToMany(Role::class)->whereHas('pages');
  }

  /**
   * Returns user roles with pages for <b>ALL</b> hotels
   */
  function applyingRoles()
  {
    return $this->belongsToMany(Role::class)->where(function ($q) {
      $q->where('inherit_from_user', true)->orWhereHas('pages');
    });
  }

  /**
   * Returns roles created by user
   */
  function createdRoles()
  {
    return $this->hasMany(Role::class);
  }

  /**
   * Returns hotel groups created by user
   */
  function createdHotels()
  {
    return $this->hasMany(Hotel::class);
  }

  /**
   * Returns hotel groups created by user
   */
  function createdGroups()
  {
    return $this->hasMany(Group::class);
  }

  function emailChanges()
  {
    return $this->hasMany(EmailChange::class);
  }

}

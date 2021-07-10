<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class Hotel
 * @package App\Models
 *
 * @property-read int $id
 * @property string $ctx
 * @property string $name
 * @property int $user_id
 * @property int $group_id
 * @property int|null $currency_id
 * @property int $country_id
 * @property bool $active
 * @property-read Carbon $created_at
 *
 * @property-read User $user
 * @property Currency|null $currency
 * @property Country|null $country
 * @property DBCollection|Role[] $roles
 * @property Group|null $group
 *
 * @property-read HotelImage|null $image
 * @property-read DBCollection|Image[] $images
 * @property-read DBCollection|Image[] $imagesWithRooms
 * @property-read string|null $logo
 */
class Hotel extends Model {

  public $incrementing = false;
  protected $fillable = ['id', 'ctx', 'name', 'user_id', 'currency_id', 'country_id', 'country', 'currency', 'active', 'group_id'];
  protected $hidden = ['user_id', 'country_id', 'ctx', 'image', 'updated_at'];
  protected $appends = ['logo'];
  protected $with = ['country', 'currency'];
  protected $casts = [
    'id'     => 'int',
    'active' => 'bool',
  ];
  protected $attributes = [
    'active' => false,
  ];

  const CAPACITY_ROOMS = 0;
  const CAPACITY_BEDS = 1;

  /**
   * @param array $data
   *
   * @return Hotel|Model|null
   */
  static function create(array $data)
  {
    $model = new static($data);
    $model->save();
    return $model->fresh(['user', 'currency', 'country', 'image']);
  }

  /**
   * @param array $data
   *
   * @return Hotel|Model|null
   */
  static function createEmpty(array $data)
  {
    $data += [
      'currency_id' => 1,
      'country_id' => 1,
      'ctx' => 'CLTZ',
      'name' => '',
    ];
    return self::create($data);
  }

  // Helpers

  function checkCurrency($code)
  {
    if ($code && (!$this->currency_id || optional($this->currency)->code !== $code)) {
      if ($c = Currency::findByCode($code)) {
        $this->currency()->associate($c)->save();
      }
    }
  }

  function toggleActive($active)
  {
    $this->update(['active' => !!$active]);
  }

  /**
   * @param Collection|array|null $ids
   *
   * @return bool
   */
  function isHotelRoles($ids): bool
  {
    if (!$ids) return true;
    $roles = Role::query()->setEagerLoads([])->findMany($ids);
    return $roles->every(fn (Role $role) => $role->hotel_id == $this->id);
  }

  function setPagesForUser(User $user)
  {
    $this->perms = $user->pagesForHotel($this);
  }

  function withPagesForUser(User $user): self
  {
    return tap($this, fn (Hotel $h) => $h->setPagesForUser($user));
  }

  // Attributes

  function getLogoAttribute()
  {
    return $this->image->url ?? null;
  }

  function setCountryAttribute($idOrCode)
  {
    $country = Country::normalize($idOrCode);
    $this->attributes['country_id'] = optional($country)->id;
  }

  function setCurrencyAttribute($idOrCode)
  {
    $currency = Currency::normalize($idOrCode);
    $this->attributes['currency_id'] = optional($currency)->id;
  }

  // Relations

  function user()
  {
    return $this->belongsTo(User::class);
  }

  function currency()
  {
    return $this->belongsTo(Currency::class);
  }

  function country()
  {
    return $this->belongsTo(Country::class);
  }

  function image()
  {
    return $this->hasOne(HotelImage::class);
  }

  function images()
  {
    return $this->hasMany(Image::class);
  }

  function imagesWithRooms()
  {
    return $this->images()->with('rooms');
  }

  public function group()
  {
    return $this->belongsTo(Group::class);
  }

  public function roles()
  {
    return $this->hasMany(Role::class);
  }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * Class Group
 * @package App\Models
 *
 * @property-read int $id
 * @property string $name
 * @property string $slug
 * @property string $style
 * @property int $user_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 *
 * @property-read GroupImage|null $image
 * @property-read string|null $logo
 *
 * @property-read string[] $adminPermissions
 *
 * @property-read DBCollection|Hotel[] $hotels
 * @property-read DBCollection|User[] $users
 * @property-read DBCollection|Role[] $roles
 * @property-read DBCollection|Page[] $pages
 * @property-read User|null $owner
 */
class Group extends Model {

  use HasFactory;

  protected $fillable = ['name', 'slug', 'style'];
  protected $appends = ['logo'];
  protected $hidden = ['image', 'updated_at', 'pages', 'hotels'];
  protected $withCount = ['hotels', 'users'];
  protected $casts = [
    'style' => 'array',
  ];

  protected static function boot()
  {
    static::deleting(function (Group $group) {
      optional($group->image)->delete();
    });

    parent::boot();
  }

  /** @return self */

  public static function create(array $data, User $user)
  {
    $model = new static($data);
    $model->user_id = $user->id;
    $model->save();
    if ($logo = Arr::get($data, 'logo.upload')) {
      // create new group image
      GroupImage::create($logo, $user, $model);
    }
    $model->pages()->attach(Page::idsByNames(Arr::get($data, 'pages', [])));
    return $model->fresh('image');
  }

  /** @return self */
  public function modify(array $data, User $user)
  {
    $this->update($data);
    if (Arr::get($data, 'logo.remove', false)) {
      // remove existing image
      optional($this->image)->delete();
    }
    if ($logo = Arr::get($data, 'logo.upload')) {
      // create new image
      optional($this->image)->delete();
      GroupImage::create($logo, $user, $this);
    }
    if ($user->admin) {
      $this->pages()->sync(Page::idsByNames(Arr::get($data, 'pages', [])));
    }
    return $this->fresh('image');
  }

  /** @return self */
  public function withData($owner = true, $pages = true): self
  {
    if ($pages) {
      $this->loadMissing('pages')->makeVisible('pages');
    }
    if ($owner) {
      optional($this->owner)->makeHidden(['hotels', 'group']);
    }
    return $this;
  }

  // Attributes

  public function getLogoAttribute()
  {
    return $this->image->url ?? null;
  }

  // Relations

  public function image()
  {
    return $this->hasOne(GroupImage::class);
  }

  public function hotels()
  {
    return $this->hasMany(Hotel::class);
  }

  public function users()
  {
    return $this->hasMany(User::class);
  }

  public function pages()
  {
    return $this->belongsToMany(Page::class);
  }

  public function roles()
  {
    return $this->hasMany(Role::class);
  }

  public function owner()
  {
    return $this->hasOne(User::class)->where('group_owner', true);
  }
}

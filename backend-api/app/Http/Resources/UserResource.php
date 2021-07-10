<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {

  public static $wrap = null;

  public static function collection($resource)
  {
    return new UsersCollection(parent::collection($resource));
  }

  public function toArray($request)
  {
    /** @var User $this */
    $ret = parent::toArray($request);
    if ($this->relationLoaded('roles')) {
      $ret['roles'] = RoleResource::collection($this->roles);
    }
    if ($this->relationLoaded('pages')) {
      $ret['pages'] = $this->pages->where('for_hotel')->pluck('name');
      $ret['apages'] = $this->pages->where('for_hotel', false)->pluck('name');
    }
    return $ret;
  }
}

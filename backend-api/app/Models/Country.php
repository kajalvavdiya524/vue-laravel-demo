<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 * @package App\Models
 *
 * @property-read int $id
 * @property string $name
 * @property string $code
 */
class Country extends Model {

  public $timestamps = false;
  protected $fillable = ['name', 'code'];
  protected $hidden = ['id'];

  /**
   * @param string $code
   *
   * @return static|Model|null
   */
  static function findByCode(string $code): ?Country
  {
    return static::query()->firstWhere('code', $code);
  }

  /**
   * @param mixed $idOrCode
   *
   * @return static|Model|null
   */
  static function normalize($idOrCode): ?Country
  {
    if (!$idOrCode) return null;
    if ($idOrCode instanceof static) return $idOrCode;
    $_ = is_numeric($idOrCode) ? 'id' : 'code';
    return static::query()->firstWhere($_, $idOrCode);
  }
}

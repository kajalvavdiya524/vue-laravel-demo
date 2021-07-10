<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Cultuzz;
use App\Managers\PMSManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DescriptionController extends Controller
{
  /**
   * Get descriptions
   *
   * @param Request $request
   * @param PMSManager $manager
   *
   * @return array|bool|string|null
   */
  public function get(Request $request, PMSManager $manager)
  {
    return $manager->getDescription();
  }

  /**
   * Update descriptions
   *
   * @param Request $request
   * @param PMSManager $manager
   *
   * @return bool[]
   * @throws ValidationException
   */
  public function update(Request $request, PMSManager $manager): array
  {
    $rules = collect(Cultuzz::DESCRIPTION_CODE_NAMES)->mapWithKeys(function ($key) {
      $key = strtolower($key);
      return ["descriptions.{$key}.lang.*" => 'nullable|max:1000'];
    })->toArray();

    $hotel   = session('hotel');
    $payload = $this->validate($request, $rules);
    $payload = array_merge($manager->getHotel($hotel), $payload);
    $manager->modifyHotel($payload, $hotel);
    return ['ok' => true];
  }
}

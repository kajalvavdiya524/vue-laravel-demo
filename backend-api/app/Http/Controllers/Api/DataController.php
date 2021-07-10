<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DataController extends Controller {

  /**
   * Get countries list
   *
   * @return Response
   */
  public function getCountries()
  {
    return Country::get();
  }

  /**
   * Get currencies list
   *
   * @return Response
   */
  public function getCurrencies()
  {
    return Currency::get()->each(function (Currency $c) { $c->makeVisible(['id', 'name']); });
  }

  public function getPages()
  {
    return Page::allPages();
  }
}

<?php

use App\Models\Country;
use App\Models\UserProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration {

  private $countries = [
    ["Afghanistan", "AF"],
    ["Albania", "AL"],
    ["Algeria", "DZ"],
    ["American Samoa", "AS"],
    ["Andorra", "AD"],
    ["Angola", "AO"],
    ["Anguilla", "AI"],
    ["Antarctica", "AQ"],
    ["Antigua and Barbuda", "AG"],
    ["Argentina", "AR"],
    ["Armenia", "AM"],
    ["Aruba", "AW"],
    ["Australia", "AU"],
    ["Austria", "AT"],
    ["Azerbaijan", "AZ"],
    ["Bahamas", "BS"],
    ["Bahrain", "BH"],
    ["Bangladesh", "BD"],
    ["Barbados", "BB"],
    ["Belarus", "BY"],
    ["Belgium", "BE"],
    ["Belize", "BZ"],
    ["Benin", "BJ"],
    ["Bermuda", "BM"],
    ["Bhutan", "BT"],
    ["Bolivia", "BO"],
    ["Bonaire", "BQ"],
    ["Bosnia and Herzegovina", "BA"],
    ["Botswana", "BW"],
    ["Bouvet Island", "BV"],
    ["Brazil", "BR"],
    ["British Indian Ocean Territory", "IO"],
    ["British Virgin Islands", "VG"],
    ["Brunei Darussalam", "BN"],
    ["Bulgaria", "BG"],
    ["Burkina Faso", "BF"],
    ["Burundi", "BI"],
    ["Cambodia", "KH"],
    ["Cameroon", "CM"],
    ["Canada", "CA"],
    ["Cape Verde", "CV"],
    ["Cayman Islands", "KY"],
    ["Central African Republic", "CF"],
    ["Chad", "TD"],
    ["Chile", "CL"],
    ["China", "CN"],
    ["Christmas Island", "CX"],
    ["Cocos (Keeling) Islands", "CC"],
    ["Colombia", "CO"],
    ["Comoros", "KM"],
    ["Congo", "CG"],
    ["Cook Islands", "CK"],
    ["Costa Rica", "CR"],
    ["Cote d'Ivoire", "CI"],
    ["Croatia", "HR"],
    ["Cuba", "CU"],
    ["Curacao", "CW"],
    ["Cyprus", "CY"],
    ["Czech Republic", "CZ"],
    ["Congo", "CD"],
    ["Denmark", "DK"],
    ["Djibouti", "DJ"],
    ["Dominica", "DM"],
    ["Dominican Republic", "DO"],
    ["Ecuador", "EC"],
    ["Egypt", "EG"],
    ["El Salvador", "SV"],
    ["Equatorial Guinea", "GQ"],
    ["Eritrea", "ER"],
    ["Estonia", "EE"],
    ["Ethiopia", "ET"],
    ["Falkland Islands (Malvinas)", "FK"],
    ["Faroe Islands", "FO"],
    ["Fiji", "FJ"],
    ["Finland", "FI"],
    ["France", "FR"],
    ["French Guiana", "GF"],
    ["French Polynesia", "PF"],
    ["French Southern Territories", "TF"],
    ["Gabon", "GA"],
    ["Gambia", "GM"],
    ["Georgia", "GE"],
    ["Germany", "DE"],
    ["Ghana", "GH"],
    ["Gibraltar", "GI"],
    ["Greece", "GR"],
    ["Greenland", "GL"],
    ["Grenada", "GD"],
    ["Guadeloupe", "GP"],
    ["Guam", "GU"],
    ["Guatemala", "GT"],
    ["Guernsey", "GG"],
    ["Guinea", "GN"],
    ["Guinea-Bissau", "GW"],
    ["Guyana", "GY"],
    ["Haiti", "HT"],
    ["Heard Island and McDonald Islands", "HM"],
    ["Holy See (Vatican City State)", "VA"],
    ["Honduras", "HN"],
    ["Hong Kong", "HK"],
    ["Hungary", "HU"],
    ["Iceland", "IS"],
    ["India", "IN"],
    ["Indonesia", "ID"],
    ["Iran, Islamic Republic of", "IR"],
    ["Iraq", "IQ"],
    ["Ireland", "IE"],
    ["Isle of Man", "IM"],
    ["Israel", "IL"],
    ["Italy", "IT"],
    ["Jamaica", "JM"],
    ["Japan", "JP"],
    ["Jersey", "JE"],
    ["Jordan", "JO"],
    ["Kazakhstan", "KZ"],
    ["Kenya", "KE"],
    ["Kiribati", "KI"],
    ["North Korea", "KP"],
    ["South Korea", "KR"],
    ["Kuwait", "KW"],
    ["Kyrgyzstan", "KG"],
    ["Lao People's Democratic Republic", "LA"],
    ["Latvia", "LV"],
    ["Lebanon", "LB"],
    ["Lesotho", "LS"],
    ["Liberia", "LR"],
    ["Libya", "LY"],
    ["Liechtenstein", "LI"],
    ["Lithuania", "LT"],
    ["Luxembourg", "LU"],
    ["Macao", "MO"],
    ["Macedonia", "MK"],
    ["Madagascar", "MG"],
    ["Malawi", "MW"],
    ["Malaysia", "MY"],
    ["Maldives", "MV"],
    ["Mali", "ML"],
    ["Malta", "MT"],
    ["Marshall Islands", "MH"],
    ["Martinique", "MQ"],
    ["Mauritania", "MR"],
    ["Mauritius", "MU"],
    ["Mayotte", "YT"],
    ["Mexico", "MX"],
    ["Micronesia, Federated States of", "FM"],
    ["Moldova", "MD"],
    ["Monaco", "MC"],
    ["Mongolia", "MN"],
    ["Montenegro", "ME"],
    ["Montserrat", "MS"],
    ["Morocco", "MA"],
    ["Mozambique", "MZ"],
    ["Myanmar", "MM"],
    ["Namibia", "NA"],
    ["Nauru", "NR"],
    ["Nepal", "NP"],
    ["Netherlands", "NL"],
    ["New Caledonia", "NC"],
    ["New Zealand", "NZ"],
    ["Nicaragua", "NI"],
    ["Niger", "NE"],
    ["Nigeria", "NG"],
    ["Niue", "NU"],
    ["Norfolk Island", "NF"],
    ["Northern Mariana Islands", "MP"],
    ["Norway", "NO"],
    ["Oman", "OM"],
    ["Pakistan", "PK"],
    ["Palau", "PW"],
    ["Palestine", "PS"],
    ["Panama", "PA"],
    ["Papua New Guinea", "PG"],
    ["Paraguay", "PY"],
    ["Peru", "PE"],
    ["Philippines", "PH"],
    ["Pitcairn", "PN"],
    ["Poland", "PL"],
    ["Portugal", "PT"],
    ["Puerto Rico", "PR"],
    ["Qatar", "QA"],
    ["Reunion", "RE"],
    ["Romania", "RO"],
    ["Russian Federation", "RU"],
    ["Rwanda", "RW"],
    ["Saint Barthelemy", "BL"],
    ["Saint Helena", "SH"],
    ["Saint Kitts and Nevis", "KN"],
    ["Saint Lucia", "LC"],
    ["Saint Martin", "MF"],
    ["Saint Pierre and Miquelon", "PM"],
    ["Saint Vincent and the Grenadines", "VC"],
    ["Samoa", "WS"],
    ["San Marino", "SM"],
    ["Sao Tome and Principe", "ST"],
    ["Saudi Arabia", "SA"],
    ["Senegal", "SN"],
    ["Serbia", "RS"],
    ["Seychelles", "SC"],
    ["Sierra Leone", "SL"],
    ["Singapore", "SG"],
    ["Sint Maarten", "SX"],
    ["Slovakia", "SK"],
    ["Slovenia", "SI"],
    ["Solomon Islands", "SB"],
    ["Somalia", "SO"],
    ["South Africa", "ZA"],
    ["South Georgia and the South Sandwich Islands", "GS"],
    ["South Sudan", "SS"],
    ["Spain", "ES"],
    ["Sri Lanka", "LK"],
    ["Sudan", "SD"],
    ["Suriname", "SR"],
    ["Svalbard and Jan Mayen", "SJ"],
    ["Swaziland", "SZ"],
    ["Sweden", "SE"],
    ["Switzerland", "CH"],
    ["Syrian Arab Republic", "SY"],
    ["Taiwan", "TW"],
    ["Tajikistan", "TJ"],
    ["Thailand", "TH"],
    ["Timor-Leste", "TL"],
    ["Togo", "TG"],
    ["Tokelau", "TK"],
    ["Tonga", "TO"],
    ["Trinidad and Tobago", "TT"],
    ["Tunisia", "TN"],
    ["Turkey", "TR"],
    ["Turkmenistan", "TM"],
    ["Turks and Caicos Islands", "TC"],
    ["Tuvalu", "TV"],
    ["Uganda", "UG"],
    ["Ukraine", "UA"],
    ["United Arab Emirates", "AE"],
    ["United Kingdom", "GB"],
    ["Tanzania", "TZ"],
    ["United States", "US"],
    ["United States Minor Outlying Islands", "UM"],
    ["Uruguay", "UY"],
    ["US Virgin Islands", "VI"],
    ["Uzbekistan", "UZ"],
    ["Vanuatu", "VU"],
    ["Venezuela", "VE"],
    ["Viet Nam", "VN"],
    ["Wallis and Futuna", "WF"],
    ["Western Sahara", "EH"],
    ["Yemen", "YE"],
    ["Zambia", "ZM"],
    ["Zimbabwe", "ZW"],
  ];

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('countries', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->char('code', 2);
    });

    $cache = [];

    foreach($this->countries as [$name, $code]) {
      /** @var Country $_ */
      $_ = Country::create(compact('name', 'code'));
      $_->save();
      $cache[$name] = $_->id;
    }

    Schema::table('user_profiles', function (Blueprint $table) {
      $table->unsignedMediumInteger('country_id')->index()->nullable()->after('country');
    });
    UserProfile::query()->whereNotNull('country')->get()->each(function (UserProfile $profile) use (&$cache) {
      $id = Arr::get($cache, $profile->country, null);
      $profile->country_id = $id ?? null;
      $profile->save();
    });
    Schema::table('user_profiles', function (Blueprint $table) {
      $table->dropColumn('country');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    $cache = Country::get(['id', 'name'])->pluck('name', 'id');

    Schema::table('user_profiles', function (Blueprint $table) {
      $table->string('country', 255)->nullable()->after('country_id');
    });
    UserProfile::query()->whereNotNull('country_id')->get()->each(function (UserProfile $profile) use (&$cache) {
      $id = Arr::get($cache, $profile->country_id, null);
      $profile->country = $id ?? null;
      $profile->save();
    });
    Schema::table('user_profiles', function (Blueprint $table) {
      $table->dropColumn('country_id');
    });

    Schema::dropIfExists('countries');
  }
}

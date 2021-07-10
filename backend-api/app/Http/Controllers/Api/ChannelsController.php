<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Managers\PMSManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ChannelsController extends Controller {

  /**
   * Get channels list
   *
   * @param PMSManager $manager
   *
   * @return Response|Collection|array
   */
  public function list(PMSManager $manager)
  {
    $channels = $manager->channelsList(['_fields' => true]);
    $mappings = $manager->channelPullMappings();
    return compact('channels') + $mappings;
  }

  /**
   * Get channel mappings
   *
   * @param PMSManager $manager
   * @param string $id
   *
   * @return Response|Collection|array
   */
  public function get(PMSManager $manager, string $id)
  {
    if (!$manager->isEnabledChannel($id)) {
      throw new BadRequestHttpException('Not an enabled channel');
    }
    return $manager->channelInfoWithMappings($id);
  }

  /**
   * Get channel fields
   *
   * @param PMSManager $manager
   * @param string $id
   *
   * @return Response|Collection|array
   */
  public function getFields(PMSManager $manager, string $id)
  {
    if (!$manager->isEnabledChannel($id)) {
      throw new BadRequestHttpException('Not an enabled channel');
    }
    return $manager->channelsList(compact('id'));
  }

  /**
   * Change channel state
   *
   * @param Request $request
   * @param PMSManager $manager
   * @param string $id
   *
   * @return Response|array
   * @throws ValidationException
   */
  public function state(Request $request, PMSManager $manager, string $id)
  {
    if (!$manager->isEnabledChannel($id)) {
      throw new BadRequestHttpException('Not an enabled channel');
    }
    if (!$channel = $manager->channelsList(compact('id'))) {
      throw new BadRequestHttpException('Unknown channel');
    }
    $isPush = Arr::get($channel, 'type') === 'push';
    $rules = [
      'mode' => 'required|string|in:start,activate,block,deactivate',
    ];
    $fields = Arr::get($channel, 'fields', []);
    if ($isPush) {
      $rules['mode'] .= ',update,disconnect';
      $rules += [
        'period'        => 'exclude_unless:mode,start,update|array',
        'period.type'   => 'exclude_unless:mode,start,update|in:0,1',
        'period.number' => 'exclude_unless:period.type,0|numeric|min:1',
        'period.unit'   => 'exclude_unless:period.type,0|in:d,w,m,y',
        'period.until'  => 'exclude_unless:period.type,1|nullable|date_format:Y-m-d',
      ];
      foreach ($fields as $field) {
        $rules[$field['key']] = 'exclude_unless:mode,start,update';
      }
    }
    $payload = $this->validate($request, $rules);
    $manager->modifyChannel(compact('id') + $payload, $fields);
    if ($payload['mode'] === 'update') {
      return $manager->channelInfoWithMappings($id);
    }
    return $manager->channelsList(compact('id'));
  }

  /**
   * Change PULL/PUSH channel mappings
   *
   * @param Request $request
   * @param PMSManager $manager
   * @param string $id
   *
   * @return Response|array
   * @throws ValidationException
   */
  public function mappings(Request $request, PMSManager $manager, string $id)
  {
    if (!$manager->isEnabledChannel($id)) {
      throw new BadRequestHttpException('Not an enabled channel');
    }
    if (!$type = $manager->getChannelType($id)) {
      throw new BadRequestHttpException('Unknown channel');
    }
    if ($type === 'pull') {
      $payload = $this->validate($request, [
        'rooms'       => 'required|array',
        'rooms.*.id'  => 'required|numeric',
        'rooms.*.inv' => 'required|boolean',
      ]);
      $manager->modifyPullMappings(compact('id') + $payload);
    } else {
      $payload = $this->validate($request, [
        'rooms'          => 'required|array',
        'rooms.*.id'     => 'required|alpha_num',
        'rooms.*.rid'    => 'required|alpha_num',
        'rooms.*.typeid' => 'required|alpha_num',
        'rooms.*.inv'    => 'required|boolean',
        'rooms.*.mode'   => 'required_if:rooms.*.inv,1|numeric|in:0,1,2,3',
      ]);
      $manager->modifyPushMappings(compact('id') + $payload);
    }
    return ['ok' => true];
  }

  private function promoValidationRules($mode, $edit = false)
  {
    $ret = [
      'name'  => 'required|string|min:3|max:200',
      'from'  => 'required|date_format:Y-m-d|lte:until',
      'until' => 'required|date_format:Y-m-d',
    ];
    if ($mode === 'promo') {
      $ret += ['discount' => 'required|numeric|between:1,99'];
    }
    if (!$edit) {
      $ret += [
        'code'    => 'required|string|min:3|max:50',
        'plans'   => 'required_with:id|array|min:1',
        'plans.*' => 'numeric',
      ];
    }
    return $ret;
  }

  /**
   * @param Request $request
   * @param PMSManager $manager
   * @param string $id
   * @param string $mode
   *
   * @return array
   * @throws ValidationException
   */
  public function createPromo(Request $request, PMSManager $manager, string $id, string $mode)
  {
    if (!$manager->isDefaultChannel($id)) {
      throw new BadRequestHttpException('Not an enabled channel');
    }
    $payload = $request->validate($this->promoValidationRules($mode));

    // check contract code
    $contractors = $manager->getAutoContractors($id);
    $items = $contractors['codes'];
    $code = Arr::get($payload, 'code');
    if ($items->firstWhere('code', $code)) {
      throw ValidationException::withMessages(['code' => __('promo.duplicated_code')]);
    }
    $iids = $items->pluck('id');
    // create contract
    $payload['cid'] = $id;
    $payload['mode'] = $mode;
    // FIXME: use return value after CS bugfix
    $manager->modifyContract($payload);
    $contractors = $manager->getAutoContractors($id);
    $promos = $contractors['codes'];
    $contract = $promos->whereNotIn('id', $iids)->first();

    // duplicate rate plans and assign them to the contract
    $plans = $manager->getRatePlan(['ids' => Arr::get($payload, 'plans')]);
    $copies = $plans->map(function ($plan) use ($payload, $manager, $mode) {
      Arr::forget($plan, 'id');
      if ($mode === 'promo') {
        // set price to a standard with discount
        Arr::set($plan, 'price.mode', 'standard');
        Arr::set($plan, 'price.stdcalc.mode', 'reduction');
        Arr::set($plan, 'price.stdcalc.reduction.mode', 'percent');
        Arr::set($plan, 'price.stdcalc.reduction.value', Arr::get($payload, 'discount'));
      }
      // set plan validity to contract validity
      Arr::set($plan, 'validity.from', Arr::get($payload, 'from'));
      Arr::set($plan, 'validity.until', Arr::get($payload, 'until'));
      Arr::set($plan, 'validity.unlim', false);

      Arr::set($plan, '_idonly', true);
//      try {
      return $manager->modifyRatePlan($plan);
//      } catch (Throwable $e) {
//        logger()->error($e->getMessage());
//        return null;
//      }
    })->filter()->values();

    // activate newly created plans
    $manager->activateRatePlan(['ids' => $copies]);

    // map newly created plans to contract
    $_ = [
      'id'    => $id,
      'promo' => Arr::get($payload, 'code'),
      'rooms' => $copies->map(fn ($id) => ['id' => $id, 'inv' => true]),
    ];
    $manager->modifyPullMappings($_);
    // fetch new plans
    $plans = $manager->getRatePlan(['ids' => $copies]);

    return compact('contract', 'plans');
  }

  /**
   * @param Request $request
   * @param PMSManager $manager
   * @param string $id
   * @param string $mode
   * @param string $item
   *
   * @return array|null
   */
  public function updatePromo(Request $request, PMSManager $manager, string $id, string $mode, string $item)
  {
    if (!$manager->isDefaultChannel($id)) {
      throw new BadRequestHttpException('Not an enabled channel');
    }
    $contract = $manager->getContract($id, $item);
    if (!$contract) {
      throw new BadRequestHttpException('Nonexisting contract');
    }
    if ($contract['outdated']) {
      throw new BadRequestHttpException('Outdated contract');
    }
    $payload = $request->validate($this->promoValidationRules($mode, true));

    $payload['cid'] = $id;
    $payload['id'] = $item;
    $payload['mode'] = $mode;
    $manager->modifyContract($payload);

    $isPromo = $mode === 'promo';
    $discount = intval(Arr::get($payload, 'discount'));
    $plans = $manager->getContractRatePlans($contract['code']);
    if ($plans) {
      $oldDiscount = Arr::get($plans[0], 'price.stdcalc.reduction.value');
      $oldFrom = $contract['from'];
      $oldUntil = $contract['until'];
      if (($isPromo && $discount !== intval($oldDiscount))
        || $oldFrom !== $payload['from'] || $oldUntil !== $payload['until']) {
        // update promo plans with new discount/validity
        $plans->each(function ($plan) use ($payload, $manager, $isPromo) {
          if ($isPromo) {
            // set price to a standard with discount
            Arr::set($plan, 'price.mode', 'standard');
            Arr::set($plan, 'price.stdcalc.mode', 'reduction');
            Arr::set($plan, 'price.stdcalc.reduction.mode', 'percent');
            Arr::set($plan, 'price.stdcalc.reduction.value', Arr::get($payload, 'discount'));
          }
          // set plan validity to contract validity
          Arr::set($plan, 'validity.from', Arr::get($payload, 'from'));
          Arr::set($plan, 'validity.until', Arr::get($payload, 'until'));
          Arr::set($plan, 'validity.unlim', false);

          Arr::set($plan, '_idonly', true);

          $manager->modifyRatePlan($plan);
        });
      }
    }

    $contract = $manager->getContract($id, $item);
    return compact('contract', 'discount');
  }

  /**
   * @param PMSManager $manager
   * @param string $id
   * @param string $mode
   * @param string $item
   *
   * @return array
   */
  public function deletePromo(PMSManager $manager, string $id, string $mode, string $item)
  {
    if (!$manager->isDefaultChannel($id)) {
      throw new BadRequestHttpException('Not an enabled channel');
    }
    $contract = $manager->getContract($id, $item);
    if (!$contract) {
      throw new BadRequestHttpException('Nonexisting contract');
    }
    // get promo plans
    $ids = $manager->getContractRatePlans($contract['code'])->pluck('id');
    // unmap plans
    $_ = [
      'id'    => $id,
      'promo' => false,
      'rooms' => $ids->map(fn ($id) => ['id' => $id, 'inv' => false]),
    ];
    $manager->modifyPullMappings($_);
    // delete contract
    $payload = [
      'cid'     => $id,
      'id'      => $item,
      'mode'    => $mode,
      '_delete' => true,
    ];
    $manager->modifyContract($payload);
    // delete plans
    $ids->each(fn ($id) => $manager->modifyRatePlan(['id' => $id, '_delete' => true]));

    return compact('ids');
  }
}

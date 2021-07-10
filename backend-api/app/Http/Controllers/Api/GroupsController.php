<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupsCollection;
use App\Models\Group;
use App\Models\GroupImage;
use App\Models\Page;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PHPUnit\TextUI\XmlConfiguration\GroupCollection;

class GroupsController extends Controller {

  private function validationRules($id = null): array
  {
    $ret = [
      'name'        => 'required|min:2|max:80',
      'slug'        => ['required', 'max:80'], // should be in array form
      'logo'        => 'sometimes|array',
      'logo.remove' => 'boolean',
      'logo.upload' => 'nullable|file|mimetypes:image/png,image/jpeg',
      'pages.*'     => 'sometimes|exists:pages,name',
      'style'       => 'max:2000',
    ];
    $uniq = Rule::unique('groups');
    if ($id) {
      $uniq = $uniq->ignore($id);
    }
    $ret['slug'][] = $uniq;
    return $ret;
  }

  /**
   * @param Request $request
   *
   * @return array
   */
  public function index(Request $request)
  {
    $list = Group::query()->with('owner', 'pages')->get();
    $list->each(fn (Group $g) => $g->withData());
    $groups = GroupResource::collection($list);
    $pages = Page::allNames();
    return compact('groups', 'pages');
  }

  /**
   * @param Request $request
   * @param Group $group
   *
   * @return GroupResource
   */
  public function show(Request $request, Group $group): ?GroupResource
  {
    $user = $this->user($request);
    $asAdmin = $group && $group->id && $user->admin;
    if (!$asAdmin) {
      $group = $user->group;
      if (!$group) {
        abort(400, 'No group');
      }
    }
    $group->loadMissing('hotels');
    return GroupResource::make($group);
  }

  /**
   *
   * @param Request $request
   *
   * @return GroupResource
   * @throws ValidationException
   */
  public function store(Request $request): GroupResource
  {
    $user = $this->user($request);
    $payload = $this->validate($request, $this->validationRules());
    $group = Group::create($payload, $user);
    return GroupResource::make($group->withData(false));
  }

  /**
   * @param Request $request
   * @param Group $group
   *
   * @return GroupResource
   * @throws ValidationException
   */
  public function update(Request $request, Group $group): GroupResource
  {
    $user = $this->user($request);
    if (!$user->admin && $user->group_id !== $group->id) {
      abort(403, 'Access Denied');
    }
    $payload = $this->validate($request, $this->validationRules($group->id));
    $group->modify($payload, $user);
    return GroupResource::make($group->withData($user->admin));
  }

  /**
   * @param Request $request
   * @param Group $group
   *
   * @return array
   * @throws Exception
   */
  public function destroy(Request $request, Group $group): array
  {
    $user = $this->user($request);
    if ($group->user_id !== $user->id) {
      abort(403, 'Access Denied');
    }
    $group->delete();
    return ['ok' => 1];
  }
}

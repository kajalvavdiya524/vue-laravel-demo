<template>
  <div class="page-groups">
    <b-modal
      id="groupModal"
      ref="groupModal"
      no-fade
      centered
      static
      size="lg"
      modal-class="form-modal"
      ignore-enforce-focus-selector=".ss-search > input"
      :ok-title="$t(`buttons.${group.id != null ? 'update' : 'save'}`)"
      ok-variant="primary"
      :cancel-title="$t('buttons.cancel')"
      cancel-variant="outline-primary"
      @show="modalScroll"
      @hidden="modalScroll"
      @ok.prevent="processForm"
      :ok-disabled="updatePending || !$refs.groupForm || formInvalid"
      :cancel-disabled="updatePending"
      :no-close-on-esc="updatePending"
      :no-close-on-backdrop="updatePending"
      :hide-header-close="updatePending"
    >
      <template #modal-header-close>
        <icon width="20" height="20" class="d-none d-md-block" type="times"/>
        <icon width="10" height="18" class="d-md-none" type="arrow-left"/>
      </template>
      <template #modal-title>
        {{ modalTitle }}
      </template>
      <ValidationObserver ref="groupForm" slim>
        <form @submit.prevent="processForm">
          <div class="row">
            <div class="col-12 col-md">
              <div class="row groupModal">
                <div class="col-md-12 col-sm-12">
                  <label class="text-xs" for="group-name">{{ $t('pages.groups.modal.field.group.title') }}</label>
                  <ValidatedField
                    :disabled="updatePending"
                    type="text" id="group-name" name="group-name" class="mb-2"
                    v-model.trim="group.name"
                    :placeholder="$t('pages.groups.modal.field.group.placeholder')"
                    rules="required|min:2|max:80"
                  />
                </div>
                <div class="col-md-12 col-sm-12">
                  <label class="text-xs" for="group-slug">{{ $t('pages.groups.modal.field.url.title') }}</label>
                  <ValidatedField
                    :disabled="updatePending"
                    v-model.trim="group.slug"
                    :placeholder="$t('pages.groups.modal.field.url.placeholder')"
                    type="text" id="group-slug" name="group-slug" class="mb-2"
                    rules="required|min:2|max:80"
                  />
                  <hr class="hrGroup">
                </div>
                <div class="col-md-12 col-sm-12">
                  <p class="titlePageModal">{{ $t('pages.groups.modal.color.title') }}</p>
                  <ImageSelector v-model="logo" />
                </div>
                <div class="col-md-12 col-sm-12 wrapperColorPicker">
                  <div v-if="group.style.color_schema.rgba">
                    <p>{{ $t('pages.groups.modal.color.main') }}</p>
                    <span
                      id="group-color-picker"
                      tabindex="-1"
                      class="colorPicker"
                      :style="`background: ${rgbaFrom(group.style.color_schema)}`"
                    />
                    <b-popover
                      target="group-color-picker"
                      no-fade
                      triggers="focus"
                      placement="topright"
                      container="group-color-picker"
                    >
                      <chrome-picker :value="group.style.color_schema" @input="changeColorSchema" />
                    </b-popover>
                  </div>

                  <div
                    class="colorText">
                    <p>{{ $t('pages.groups.modal.color.text.title') }}</p>

                    <div class="wrapperColor">
                      <div>
                        <span
                          aria-label="white"
                          @click="textColor='white'"
                          class="colorPicker"
                          :style="`background: ${rgbaFrom(defaultTextColors.white)}`"
                        />
                        <radio v-model="textColor" val="white" :disabled="updatePending">
                          {{ $t('pages.groups.modal.color.text.colorwhite') }}
                        </radio>
                      </div>

                      <div>
                        <span
                          aria-label="black"
                          @click="textColor='black'"
                          class="colorPicker"
                          :style="`background: ${rgbaFrom(defaultTextColors.black)}`"
                        />
                        <radio v-model="textColor" val="black" :disabled="updatePending">
                          {{ $t('pages.groups.modal.color.text.colordark') }}
                        </radio>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
            <div class="col-12 col-md cell-perms">
              <label class="text-xs">{{ $t('pages.groups.modal.field-perms') }}</label>
              <pages-selector
                show-all
                mode="group"
                :allowed="pages"
                v-model="group.pages"
                :disabled="updatePending"
              />
            </div>
          </div>
        </form>
      </ValidationObserver>
    </b-modal>

    <user-manage-modal
      ref="ownerModal"
      group-owner
      :pending="usersUpdatePending"
      :error-bag="usersUpdateError"
      @ok="createOwner"
    />

    <div ref="title" class="panel-title position-relative w-100 title">
      <p>{{ $t('pages.groups.title') }}</p>
    </div>

    <tabs :items="tabs" v-model="activetab" withContent>
      <template #tab(1)>
        <div class="tab-content tab-content-groups">
          <div class="panel position-relative panel-content panel-content_groups">
            <div class="head-line head-line-groups justify-content-between">
              <search-filter v-model="filter"
                             :placeholder="$t('pages.hotels.filter-tip')"/>
              <button
                @click="openCreateForm"
                v-if="loaded"
                class="new_group_btn new_group_btn-2">
                <icon class="arrow" stroke-width="2" width="10" height="10" type="cross"/>
                <p>{{ $t('pages.groups.btn-add') }}</p>
              </button>
            </div>
            <div class="list d-none d-md-block">
              <div class="hotels-table group-table">
                <table class="w-100" v-if="loaded">
                  <thead>
                    <tr>
                      <th class="w-id">
                        {{ $t('id') }}
                        <sort-indicator v-model="sort" field="id"/>
                      </th>
                      <th class="w-name">
                        {{ $t('pages.groups.headers.name') }}
                        <sort-indicator v-model="sort" field="name"/>
                      </th>
                      <th class="w-name">
                        {{ $t('pages.groups.headers.owner') }}
                      </th>
                      <th class="w-name">
                        {{ $t('pages.groups.headers.properties') }}
                        <sort-indicator v-model="sort" field="hotels_count"/>
                      </th>
                      <th class="w-name">
                        {{ $t('pages.groups.headers.users') }}
                        <sort-indicator v-model="sort" field="users_count"/>
                      </th>
                      <th class="w-name">
                        {{ $t('pages.groups.headers.url') }}
                        <sort-indicator v-model="sort" field="slug"/>
                      </th>
                      <th class="w-actions">{{ $t('actions') }}</th>
                    </tr>
                  </thead>

                  <tbody v-for="item in filteredGroups" :key="`group-${item.id}`">
                    <tr class="separator before"></tr>
                    <tr>
                      <td>
                        {{ item.id }}
                      </td>
                      <td>
                        {{ item.name }}
                      </td>
                      <td>
                        <span v-if="item.owner">
                          {{ item.owner.profile.name }}
                        </span>
                        <b-btn v-else variant="link" size="sm" class="m-0 p-0" @click="openCreateOwner(item)">
                          {{ $t('pages.groups.btn-assign') }}
                        </b-btn>
                      </td>
                      <td>
                        {{ item.hotels_count }}
                      </td>
                      <td>
                        {{ item.users_count }}
                      </td>
                      <td class="text-monospace">
                        {{ item.slug }}
                      </td>
                      <td class="actions">
                        <b-btn
                          :disabled="updatePending"
                          class="btn-icon btn-tiny" :to="{ name: 'admin-group', params: { id: item.id } }">
                          <icon width="19" height="19" type="settings"/>
                        </b-btn>
                        <b-btn
                          :disabled="updatePending"
                          class="btn-icon btn-tiny" @click="editGroup(item)">
                          <icon width="16" height="16" type="edit"/>
                        </b-btn>
                        <b-btn
                          :disabled="updatePending || item.users_count > 0"
                          class="btn-icon btn-tiny"
                          @click="deleteGroup(item.id)">
                          <icon class="text-danger" width="19" height="19" type="delete"/>
                        </b-btn>
                      </td>
                    </tr>
                    <tr class="separator after"></tr>
                  </tbody>
                  <tbody v-if="noGroups">
                    <tr>
                      <td colspan="5" class="w-empty">{{ $t('pages.groups.no-groups') }}</td>
                    </tr>
                  </tbody>
                  <tbody v-else-if="filter && !filteredGroups.length">
                    <tr>
                      <td colspan="5" class="w-empty">{{ $t('pages.groups.filter-no-groups') }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="d-md-none list-item" v-if="noGroups">
              <div class="w-empty">{{ $t('pages.groups.no-groups') }}</div>
            </div>
            <div class="d-md-none list-item" v-else
                 v-for="item in filteredGroups"
                 :key="`m-group-${item.id}`"
            >
              <div class="d-flex">
                <div class="c-1">
                  <p class="label">{{ $t('pages.groups.headers.name') }}</p>
                  <p>{{ item.name }}</p>
                </div>
                <div class="dots">
                  <b-dropdown size="sm" toggle-tag="span" variant="link" no-caret right>
                    <template #button-content>
                      <icon width="20" height="19" class="label" type="dots-h"/>
                    </template>
                    <b-dropdown-item :disabled="updatePending"
                                     @click="editGroup(item)">{{ $t('buttons.edit') }}</b-dropdown-item>
                    <b-dropdown-item :disabled="updatePending || item.users_count > 0"
                                     @click="deleteGroup(item.id)">{{ $t('buttons.delete') }}</b-dropdown-item>
                  </b-dropdown>
                </div>
              </div>
              <div
                class="d-flex line lineSmGroup">
                <div class="w-25">
                  <p class="label">{{ $t('id') }}</p>
                  <p>{{ item.id }}</p>
                </div>
                <div class="w-33">
                  <p class="label">{{ $t('pages.groups.headers.properties') }}</p>
                  <p>{{ item.hotels_count }}</p>
                </div>
                <div class="w-33">
                  <p class="label"> {{ $t('pages.groups.headers.url') }}</p>
                  <p class="text-monospace">{{ item.slug }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </tabs>
  </div>
</template>

<script>
  import { mapActions, mapGetters, mapState } from 'vuex';
  import { defaultTextColors, defaultBackgroundColor } from '@/shared';
  import { pick } from '@/helpers';
  import { Chrome } from 'vue-color';
  import UserManageModal from '@/components/UserManageModal.vue';

  export default {
    name: 'Groups',
    components: {
      UserManageModal,
      'chrome-picker': Chrome,
    },
    data() {
      return {
        activetab: 1,
        tabs: [
          { id: 1, title: this.$t('pages.groups.tabs.groups') },
        ],
        group: {
          name: '',
          slug: '',
          style: {
            color_schema: { rgba: null },
            color_font: { rgba: null },
          },
          pages: [],
          apages: [],
        },
        group_id: null,
        textColor: 'white',
        filter: '',
        sort: '+id',
        logo: null,
      };
    },
    async created() {
      await this.getGroups(true);
    },
    watch: {
      textColor(val) {
        if (this.group != null && this.group.style != null) {
          this.group.style.color_font = defaultTextColors[val];
        }
      },
    },
    computed: {
      ...mapGetters('groups', ['loaded', 'groups', 'pages', 'noGroups']),
      ...mapState('groups', ['error', 'pending', 'updatePending']),
      ...mapState('users', { usersUpdatePending: 'updatePending', usersUpdateError: 'updateError' }),
      defaultTextColors: () => defaultTextColors,
      formInvalid() {
        return this.$refs.groupForm && this.$refs.groupForm.flags.invalid;
      },
      modalTitle() {
        if (this.groups.id == null) {
          return this.$t('pages.groups.modal.title-add');
        }
        return this.$t('pages.groups.modal.title-edit', this.groups.name);
      },
      filteredGroups() {
        const filter = this.filter.trim().toLowerCase();
        let ret = [...this.groups];
        if (filter) {
          ret = ret.filter(({ id, name }) => (`${id}`.includes(filter) || name.toLowerCase().includes(filter)));
        }
        const field = this.sort.substr(1);
        const k = this.sort.charAt(0) === '+' ? 1 : -1;
        ret = ret.sort((a, b) => {
          const v1 = pick(a, field);
          const v2 = pick(b, field);
          // eslint-disable-next-line no-nested-ternary
          return k * (v1 > v2 ? 1 : (v1 < v2 ? -1 : 0));
        });
        return ret;
      },
    },
    methods: {
      ...mapActions('groups', ['createGroup', 'getGroups', 'deleteGroup', 'updateGroup']),
      ...mapActions('users', ['createUser']),
      rgbaFrom(item) {
        if (item == null || item.rgba == null) return 'none';
        const {
          r, g, b, a,
        } = item.rgba;
        return `rgba(${r}, ${g}, ${b}, ${a})`;
      },
      changeColorSchema(value) {
        if (value == null) return;
        const { rgba } = value;
        this.group.style.color_schema = { rgba };
      },
      openCreateOwner(group) {
        this.group_id = group.id;
        this.$refs.ownerModal.show();
      },
      async createOwner(user) {
        // eslint-disable-next-line camelcase
        const { group_id } = this;
        const payload = {
          ...user,
          group_id,
        };
        try {
          const ok = await this.createUser(payload);
          this.$refs.ownerModal.reset();
          if (!ok) return;
          this.$refs.ownerModal.hide();
          this.$toastr.s({
            title: this.$t('pages.masterdata.alert-saved'),
            msg: '',
            timeout: 3000,
            progressbar: false,
          });
        } catch (error) {
          this.$toastr.e(error.message, this.$t('error'));
        }
      },
      openCreateForm() {
        this.resetGroupModal();
        this.$nextTick(this.$refs.groupModal.show);
      },
      modalScroll(ev) {
        const modal = ev.target;
        this.$nextTick(() => {
          if (modal != null) modal.scrollTop = 0;
        });
      },
      resetGroupModal() {
        this.group = {
          name: '',
          slug: '',
          style: {
            color_schema: defaultBackgroundColor,
            color_font: defaultTextColors.white,
          },
          pages: [],
          apages: [],
        };
        this.logo = {
          original: null,
          upload: null,
          remove: false,
        };
        if (this.$refs.groupForm != null) {
          this.$refs.groupForm.reset();
        }
      },
      editGroup(group) {
        this.group = {};
        this.resetGroupModal();
        this.group = {
          ...this.group,
          ...JSON.parse(JSON.stringify(group)),
        };
        if (this.rgbaFrom(this.group.style.color_font) === 'rgba(0, 0, 0, 1)') {
          this.textColor = 'black';
        }
        this.logo = {
          original: group.logo || null,
          upload: null,
          remove: false,
        };
        this.$nextTick(() => {
          this.$refs.groupForm.reset();
          this.$refs.groupModal.show();
        });
      },
      async processForm() {
        if (this.$refs.groupForm.flags.invalid) return;
        const { upload, remove } = this.logo;
        try {
          if (this.group.id != null) {
            await this.updateGroup({
              ...this.group,
              logo: { upload, remove },
            });
          } else {
            await this.createGroup({
              ...this.group,
              logo: { upload },
            });
          }
          this.$refs.groupForm.reset();
          this.$refs.groupModal.hide();
          this.$toastr.s({
            title: this.$t('pages.masterdata.alert-saved'),
            msg: '',
            timeout: 3000,
            progressbar: false,
          });
        } catch (error) {
          this.$toastr.e(error.message, this.$t('error'));
        }
        // const testObject = {
        //   logo: this.logo.upload.name,
        //   bgColor: this.colors.rgba,
        //   textColor: this.text.check.rgba,
        // };
        // localStorage.setItem('testObject', JSON.stringify(testObject));
      },
    },
  };
</script>

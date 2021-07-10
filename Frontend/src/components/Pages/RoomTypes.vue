<template>
  <div class="component-roomtypes">
    <b-modal
      id="roomModal"
      ref="roomModal"
      no-fade
      centered
      static
      size="lg"
      modal-class="form-modal"
      :ok-title="$t(`buttons.${room.id != null ? 'update' : 'save'}`)"
      ok-variant="primary"
      :cancel-title="$t('buttons.cancel')"
      cancel-variant="outline-primary"
      :ok-disabled="updatePending || !$refs.roomForm || !formValid || !$refs.roomForm.flags.valid"
      :cancel-disabled="updatePending"
      :no-close-on-esc="updatePending"
      :no-close-on-backdrop="updatePending"
      :hide-header-close="updatePending"
      @show="modalScroll"
      @hidden="modalScroll"
      @ok.prevent="processForm"
    >
      <template #modal-header-close>
        <icon width="20" height="20" class="d-none d-md-block" type="times"/>
        <icon width="10" height="18" class="d-md-none" type="arrow-left"/>
      </template>
      <template #modal-title>
        {{ modalTitle }}
      </template>
      <ValidationObserver ref="roomForm" slim>
        <div class="edge" :class="{ active: edgeRoomLang }">
          <h3 :content="$t('pages.roomtypes.modal.section-room-name')"></h3>
          <lang-selector v-model="lang" :valid="langsValid" ref="langSel" />
          <div class="link-figure">
            <icon width="22" height="22" class="link-figure-icon" type="link"/>
            <div class="lang-choice" v-for="code in langCodes" :key="code" v-show="lang === code">
              <ValidatedField type="text" :name="`lang-${code}`" class="room-name" no-icon
                              v-model="room.langs[code].name" :disabled="updatePending"
                              @input="toggleLangValid($event, code)"
                              :rules="rulesForLang(code, 'name')"/>
              <ValidatedField type="textarea" class="room-desc" :placeholder="$t('pages.roomtypes.modal.description')"
                              no-icon :rules="rulesForLang(code, 'desc')" :name="`lang-${code}-desc`"
                              v-model="room.langs[code].desc" :disabled="updatePending"></ValidatedField>
            </div>
            <h5>{{ $t('pages.roomtypes.modal.section-room-type') }}</h5>
          </div>
          <drop-down
            id="dd-typecodes"
            v-model="room.typecode"
            :items="typecodes"
            @input="updateTypecode"
            :disabled="updatePending || room.protected"
          />
        </div>
        <div class="edge edge-amount" :class="{ active: edgeRoomAmount }">
          <h3 :content="$t('pages.roomtypes.modal.section-amount')"></h3>
          <div class="row row-edit">
            <div class="col cell-edit-field">
              <ValidatedField
                type="number" id="amount" name="amount" min="1" max="999" class="mb-0" no-icon no-tooltip
                v-model="room.amount" :disabled="updatePending" ref="fieldAmount"
                :rules="rulesFor('amount')"
              />
            </div>
          </div>
        </div>
        <div class="edge edge-amount" :class="{ active: edgeRoomPrice }" v-if="room.id == null">
          <h3 :content="$t('pages.roomtypes.modal.section-initial-price')"></h3>
          <div class="row row-edit">
            <div class="col cell-edit-field">
              <AmountPercent simple accept-zero required :disabled="updatePending" v-model="room.initprice"/>
            </div>
            <div class="col cell-edit-tip">
              {{ $t('pages.roomtypes.modal.initial-price-tip') }}
            </div>
          </div>
        </div>
        <div class="edge edge-occupancy" :class="{ active: edgeRoomOccStd }">
          <h3 :content="$t('pages.roomtypes.modal.section-occupancy')"></h3>
          <div class="row row-edit">
            <div class="col cell-edit-field with-guests">
              <ValidatedField
                type="number" id="occ-std" name="occ-std" min="1" max="99" class="mb-0" no-icon no-tooltip
                :value="room.occupancy.std" disabled
                :rules="rulesFor('occ-std')"
              />
              <guests :guests="room.occupancy.std" max="99" />
            </div>
            <div class="col cell-edit-tip">
              {{ $t('pages.roomtypes.modal.section-occupancy-tip') }}
            </div>
          </div>
          <h6 :class="{ opened: occupancyOpened }" @click="occupancyOpened=!occupancyOpened">
            {{ $t('pages.roomtypes.modal.section-occupancy-more') }}
            <icon width="12" height="7" stroke-width="2" type="arrow-down"/>
          </h6>
          <div>
            <div class="row row-occupancy">
              <div class="col cell-edit-field">
                <label class="text-xs" for="occ-min">{{ $t('pages.roomtypes.modal.min-occupancy') }}</label>
                <div class="with-guests">
                  <ValidatedField
                    type="number" id="occ-min" name="occ-min" min="1" max="999" class="mb-0" no-icon no-tooltip
                    v-model="room.occupancy.min" :disabled="updatePending"
                    :rules="rulesFor('occ-min')"
                  />
                  <guests :guests="room.occupancy.min" max="99"/>
                </div>
              </div>
              <div class="col cell-edit-field">
                <label class="text-xs" for="occ-max">{{ $t('pages.roomtypes.modal.max-occupancy') }}</label>
                <div class="with-guests">
                  <ValidatedField
                    type="number" id="occ-max" name="occ-max" min="1" max="999" class="mb-0" no-icon no-tooltip
                    v-model="room.occupancy.max" :disabled="updatePending"
                    :rules="rulesFor('occ-max')"
                  />
                  <guests :guests="room.occupancy.max" max="99"/>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="edge active">
          <h3 :content="$t('date.validity-period')"></h3>
          <div class="row row-validity">
            <div class="col-12 cell-val-from">
              <label class="text-xs" for="date-val-from">{{ $t('date.from') }}</label>
              <date-picker id="date-val-from" v-model="room.validity.from" :min-date="today" grow="md-up"
                           :disabled="updatePending" @input="validityFromChanged" />
            </div>
            <div class="col-12 cell-val-until">
              <label class="text-xs" for="date-val-until">{{ $t('date.until') }}</label>
              <div class="d-flex align-center">
                <radio v-model="room.validity.unlim" :val="false"/>
                <date-picker id="date-val-until" v-model="room.validity.until" :min-date="today" grow="md-up"
                             ref="validityUntil" position="left-md-right"
                             :disabled="room.validity.unlim || updatePending" @input="validityUntilChanged" />
              </div>
              <div class="validity-unlim-wrapper">
                <radio v-model="room.validity.unlim" :val="true">{{ $t('date.unlim') }}</radio>
              </div>
            </div>
          </div>
          <h6 :class="{ opened: blockoutsOpened }" @click="blockoutsOpened=!blockoutsOpened">
            {{ $t('date.blockout-periods') }}
            <icon width="12" height="7" stroke-width="2" type="arrow-down"/>
          </h6>
          <div class="danger">
            <div class="row row-blockout" v-for="(blockout, idx) in room.blockouts" :key="`bl-${idx}`">
              <div class="col-12 cell-block-from">
                <label class="text-xs" :for="`date-block-from-${idx}`">{{ $t('date.from') }}</label>
                <date-picker :id="`date-block-from-${idx}`" v-model="blockout.from" :min-date="today"
                             grow="up" :disabled="updatePending" @input="blockFromChanged($event, idx)" />
              </div>
              <div class="col-12 cell-block-until">
                <label class="text-xs" :for="`date-block-until-${idx}`">{{ $t('date.until') }}</label>
                <date-picker :id="`date-block-until-${idx}`" v-model="blockout.until" :min-date="today"
                             grow="up" ref="blockUntil" position="left-md-right"
                             :disabled="updatePending" @input="blockUntilChanged($event, idx)" />
              </div>
              <div class="col-12 cell-block-delete">
                <b-btn class="btn-icon btn-tiny" @click="removeBlockout(idx)">
                  <icon width="18" height="18" type="delete"/>
                </b-btn>
              </div>
            </div>
            <div class="row row-blockout">
              <div class="col">
                <b-btn pill variant="outline-danger" class="add-new-blockout" @click="addBlockout">
                  <icon width="10" height="11" type="plus"/>
                  {{ $t('date.button-add-blockout') }}
                </b-btn>
              </div>
            </div>
          </div>
        </div>
      </ValidationObserver>
    </b-modal>

    <div class="panel position-relative panel-content">
      <div class="list d-none d-md-block left-edge">
        <p class="head-line"><span>{{ $t('pages.roomtypes.heading') }}</span><spinner v-if="!loaded" /></p>
        <div class="plans-table" v-if="loaded">
          <table class="w-100">
            <thead>
              <tr>
                <th class="w-id">{{ $t('id') }}</th>
                <th class="w-name">{{ $t('pages.roomtypes.headers.name') }}</th>
                <th class="w-amount text-center">{{ $t('pages.roomtypes.headers.amount') }}</th>
                <th class="w-occupancy text-center">{{ $t('pages.roomtypes.headers.occupancy') }}</th>
                <th class="w-actions">{{ $t('actions') }}</th>
              </tr>
            </thead>

            <tbody v-if="noRooms">
              <tr>
                <td colspan="6" class="w-empty">{{ $t('pages.roomtypes.no-rooms') }}</td>
              </tr>
            </tbody>
            <tbody v-for="row in rooms" :key="row.id">
              <tr class="separator before"></tr>
              <tr>
                <td>
                  {{ row.id }}
                </td>
                <td>
                  <p>{{ row.langs.en.name }}</p>
                </td>
                <td class="text-center">
                  {{ row.amount }}
                </td>
                <td class="text-center">
                  {{ occupancyForRoom(row) }}
                </td>
                <td class="actions">
                  <b-btn class="btn-icon btn-tiny" @click="editRoom(row)" :disabled="updatePending">
                    <icon width="17" height="17" type="edit"/>
                  </b-btn>
                  <b-btn class="btn-icon btn-tiny" @click="duplicateRoom(row.pid)" :disabled="updatePending">
                    <icon width="18" height="18" type="copy"/>
                  </b-btn>
                  <b-btn class="btn-icon btn-tiny" @click="deleteRoom(row.pid)"
                         :disabled="updatePending || row.protected">
                    <icon width="19" height="19" type="delete"/>
                  </b-btn>
                </td>
              </tr>
              <tr class="separator after"></tr>
            </tbody>
          </table>
        </div>
        <div v-if="loaded">
          <b-btn pill variant="outline-primary" class="add-new-plan"
                 @click="openCreateForm">
            <icon width="10" height="11" type="plus"/>
            {{ $t('pages.roomtypes.button-add') }}
          </b-btn>
          <p class="bottom-tip">{{ $t('pages.roomtypes.footer') }}</p>
        </div>
      </div>

      <p class="head-line d-md-none"><span>{{ $t('pages.roomtypes.heading') }}</span><spinner v-if="!loaded" /></p>
      <div class="d-md-none list-item" v-if="noRooms">
        <div class="w-empty">{{ $t('pages.roomtypes.no-rooms') }}</div>
      </div>
      <div class="d-md-none list-item" v-for="row in rooms" :key="row.id">
        <div class="d-flex">
          <div class="c-1">
            <p class="label">{{ $t('pages.roomtypes.headers.name') }}</p>
            <p>{{ row.langs.en.name }}</p>
          </div>
          <div class="dots">
            <b-dropdown size="sm" toggle-tag="span" variant="link" no-caret right :disabled="updatePending">
              <template #button-content>
                <icon width="20" height="19" class="label" type="dots-h"/>
              </template>
              <b-dropdown-item @click="duplicateRoom(row.pid)">{{ $t('buttons.duplicate') }}</b-dropdown-item>
              <b-dropdown-item @click="editRoom(row)">{{ $t('buttons.edit') }}</b-dropdown-item>
              <b-dropdown-item @click="deleteRoom(row.pid)"
                               :disabled="row.protected">{{ $t('buttons.delete') }}</b-dropdown-item>
            </b-dropdown>
          </div>
        </div>
        <div class="d-flex line">
          <div class="w-33">
            <p class="label">{{ $t('id') }}</p>
            <p>{{ row.id }}</p>
          </div>
          <div class="w-33">
            <p class="label">{{ $t('pages.roomtypes.headers.amount') }}</p>
            <p>{{ row.amount }}</p>
          </div>
          <div class="w-33">
            <p class="label">{{ $t('pages.roomtypes.headers.occupancy') }}</p>
            <p>{{ occupancyForRoom(row) }}</p>
          </div>
        </div>
      </div>
    </div>
    <div class="d-md-none add-new-plan-block" v-if="loaded">
      <b-btn pill variant="outline-primary" class="add-new-plan" @click="openCreateForm">
        <icon width="10" height="10" type="plus"/>
        {{ $t('pages.roomtypes.button-add') }}
      </b-btn>
      <p class="bottom-tip">{{ $t('pages.roomtypes.footer') }}</p>
    </div>
  </div>
</template>

<script>
  import moment from 'moment';
  import { mapState, mapGetters, mapActions } from 'vuex';
  import { langCodes } from '@/shared';

  export default {
    name: 'RoomTypes',
    props: {
      payload: {
        validator: (v) => ['object', 'undefined'].includes(typeof v),
        default: null,
      },
    },
    data: () => ({
      room: {},
      lang: 'en',
      langsValid: [],
      occupancyOpened: false,
      blockoutsOpened: false,
      today: moment(),
    }),
    async created() {
      this.resetRoomModal();
      await this.fetchData(true);
      // document.querySelector('.add-new-plan').click();
    },
    computed: {
      ...mapGetters('roomtypes', ['loaded', 'noRooms', 'rooms', 'typecodes']),
      ...mapState('roomtypes', ['error', 'pending', 'updatePending']),
      langCodes: () => langCodes,
      edgeRoomLang() {
        return this.langsValid.includes('en') && !!this.room.typecode;
      },
      edgeRoomAmount() {
        return !!this.room.amount;
      },
      edgeRoomPrice() {
        return this.room.initprice !== '';
      },
      edgeRoomOccStd() {
        return !!this.room.occupancy.std;
      },
      formValid() {
        return this.edgeRoomLang && this.edgeRoomAmount;
      },
      modalTitle() {
        if (this.room.id == null) {
          return this.$t('pages.roomtypes.modal.title-add');
        }
        const { id, langs: { en: { name } } } = this.room;
        return this.$t('pages.roomtypes.modal.title-edit', { id, name });
      },
    },
    methods: {
      ...mapActions('roomtypes', ['fetchData', 'createRoom', 'updateRoom', 'duplicateRoom', 'deleteRoom']),

      modalScroll(ev) {
        const modal = ev.target;
        this.$nextTick(() => {
          if (modal != null) modal.scrollTop = 0;
        });
      },
      resetRoomModal() {
        this.lang = 'en';
        this.langsValid = [];
        this.occupancyOpened = false;
        this.blockoutsOpened = false;
        this.room = {
          langs: Object.fromEntries(langCodes.map((c) => [c, {}])),
          amount: '',
          typecode: '',
          occupancy: {
            std: '',
            min: '',
            max: '',
          },
          initprice: '0',
          validity: {
            from: moment(),
            until: moment().add(1, 'year'),
            unlim: true,
          },
          blockouts: [],
        };
        this.$nextTick(() => {
          this.$refs.langSel.resetScroller();
        });
        if (this.$refs.roomForm != null) {
          this.$refs.roomForm.reset();
        }
      },
      normalizeLangs() {
        langCodes.forEach((c) => {
          if (this.room.langs[c] == null) {
            this.room.langs[c] = {};
          }
        });
      },
      openCreateForm() {
        this.resetRoomModal();
        this.$nextTick(this.$refs.roomModal.show);
      },
      editRoom(room) {
        this.room = {};
        this.resetRoomModal();
        this.room = {
          ...this.room,
          ...JSON.parse(JSON.stringify(room)), // deep clone without bindings
        };
        this.normalizeLangs();
        this.blockoutsOpened = this.room.blockouts.length > 0;
        Object.keys(this.room.langs).forEach((code) => {
          this.toggleLangValid(this.room.langs[code].name, code);
        });
        this.$nextTick(() => {
          this.$refs.roomForm.reset();
          this.$refs.roomModal.show();
        });
      },
      updateTypecode() {
        const { typecode } = this.room;
        if (!typecode) {
          this.room.occupancy.std = '';
          return;
        }
        const type = this.typecodes.find((t) => t.id === typecode);
        if (!type) return;
        this.room.occupancy = {
          min: 1,
          std: type.occupancy,
          max: type.occupancy,
        };
      },
      async processForm() {
        const room = { ...this.room };
        if (this.payload != null) {
          Object.keys(this.payload).forEach((k) => {
            room[k] = this.payload[k];
          });
        }
        // fix dates
        ['from', 'until'].forEach((k) => {
          const v = room.validity[k];
          if (moment.isMoment(v)) {
            room.validity[k] = v.format('YYYY-MM-DD');
          }
        });
        (room.blockouts || []).forEach((b) => {
          ['from', 'until'].forEach((k) => {
            const v = b[k];
            if (moment.isMoment(v)) {
              // eslint-disable-next-line no-param-reassign
              b[k] = v.format('YYYY-MM-DD');
            }
          });
        });
        try {
          if (room.id != null) {
            await this.updateRoom(room);
          } else {
            await this.createRoom(room);
          }
          this.$refs.roomModal.hide();
        } catch (error) {
          // eslint-disable-next-line no-console
          console.error(error.message);
        }
      },
      toggleLangValid(val, code) {
        const idx = this.langsValid.indexOf(code);
        if (val != null && `${val}`.trim()) {
          if (idx === -1) {
            this.langsValid.push(code);
          }
        } else if (idx !== -1) {
          this.langsValid.splice(idx, 1);
        }
      },
      validityFromChanged(dt) {
        if (dt.isAfter(this.room.validity.until, 'date')) {
          this.room.validity.until = moment(dt);
        }
        this.$nextTick(() => {
          this.$refs.validityUntil.$el.focus();
        });
      },
      validityUntilChanged(dt) {
        if (dt.isBefore(this.room.validity.from, 'date')) {
          this.room.validity.from = moment(dt);
        }
      },
      addBlockout() {
        this.room.blockouts.push({
          from: moment(),
          until: moment(),
        });
      },
      removeBlockout(idx) {
        this.room.blockouts.splice(idx, 1);
      },
      blockFromChanged(dt, idx) {
        if (dt.isAfter(this.room.blockouts[idx].until, 'date')) {
          this.room.blockouts[idx].until = moment(dt);
        }
        this.$nextTick(() => {
          this.$refs.blockUntil[idx].$el.focus();
        });
      },
      blockUntilChanged(dt, idx) {
        if (dt.isBefore(this.room.blockouts[idx].from, 'date')) {
          this.room.blockouts[idx].from = moment(dt);
        }
      },
      rulesFor(field) {
        const rules = {
          required: true,
          numeric: true,
        };
        switch (field) {
          case 'amount':
            rules.between = { min: 1, max: 999 };
            break;
          case 'occ-std':
            rules.between = { min: 1, max: 99 };
            break;
          case 'occ-min':
            rules.between = { min: 1, max: 99 };
            rules.maxvalue = this.room.occupancy.std;
            break;
          case 'occ-max':
            rules.between = { min: 1, max: 99 };
            rules.minvalue = this.room.occupancy.std;
            break;
          default:
            break;
        }
        return rules;
      },
      rulesForLang(lang, type) {
        const rules = {};
        if (lang === 'en' && type === 'name') {
          rules.required = true;
        }
        rules.max = type === 'name' ? 200 : 2000;
        return rules;
      },
      occupancyForRoom(room) {
        const { std, min, max } = room.occupancy;
        return std - min || std - max ? `${min}-${max}` : std;
      },
    },
  };
</script>

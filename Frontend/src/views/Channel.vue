<template>
  <div class="page-channel">
    <b-modal
      id="promoModal"
      ref="promoModal"
      no-fade
      static
      centered
      size="md"
      modal-class="form-modal"
      :ok-title="promoOkTitle"
      :ok-variant="promo.outdated ? 'outline-primary' : 'primary'"
      :ok-only="promo.outdated"
      :cancel-title="$t('buttons.cancel')"
      cancel-variant="outline-primary"
      :ok-disabled="updatePending || !promoFormValid"
      :cancel-disabled="updatePending"
      :no-close-on-esc="updatePending"
      :no-close-on-backdrop="updatePending"
      :hide-header-close="updatePending"
      @show="modalScroll"
      @hidden="modalScroll"
      @ok.prevent="processPromoForm"
    >
      <template #modal-header-close>
        <icon width="20" height="20" class="d-none d-md-block" type="times"/>
        <icon width="10" height="18" class="d-md-none" type="arrow-left"/>
      </template>
      <template #modal-title>
        {{ promoModalTitle }}
      </template>
      <ValidationObserver ref="promoForm" slim>
        <div class="row">
          <div class="col-12">
            <h5 class="pb-2">{{ $t(`pages.channels.promo.modal.field-name-${promo.mode}`) }}</h5>
            <ValidatedField id="promo-name" name="name" no-icon
                            rules="required|min:3"
                            :placeholder="$t(`pages.channels.promo.modal.field-name-placeholder-${promo.mode}`)"
                            v-model="promo.name" :disabled="updatePending || promo.outdated"/>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <h5 class="pb-2">{{ $t(`pages.channels.promo.modal.field-code-${promo.mode}`) }}</h5>
            <ValidatedField name="code" no-icon
                            rules="required|min:3" :error-bag="updateError"
                            :placeholder="$t(`pages.channels.promo.modal.field-code-placeholder-${promo.mode}`)"
                            v-model.trim="promo.code" :disabled="updatePending || promoIsEdit || promo.outdated"/>
          </div>
        </div>
        <div class="row" v-if="promo.mode === 'promo'">
          <div class="col-12">
            <h5 class="pb-2">{{ $t('pages.channels.promo.modal.field-discount') }}</h5>
            <AmountPercent simple="percent" v-model="promo.discount" required
                           :disabled="updatePending || promo.outdated"
                           class="mb-3" min="1" max="99" />
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <h5 class="pb-4 pt-2">{{ $t('pages.channels.promo.modal.field-plans') }}</h5>
            <products-selector
              v-if="!promoIsEdit"
              plans-only
              :plans="promoCreatePlans"
              :rooms="rooms"
              :selected-plans.sync="promo.plans"
              :disabled="updatePending"
            />
            <div v-else>
              <p v-for="({ id, text, rid, rtext }, idx) in promoRates(promo)" :key="`pre-${promo.code}-${idx}`">
                {{ rtext }} ({{ rid }}) &centerdot; {{ text }} ({{ id }})
              </p>
            </div>
          </div>
        </div>
        <h5 class="pb-2 pt-3">{{ $t('pages.channels.promo.modal.field-validity') }}</h5>
        <div class="row">
          <div class="col-12 col-md-6">
            <label class="text-xs">{{ $t('date.from') }}</label>
            <date-picker
              id="promo-from" v-model="promo.from" :min-date="today" grow="md-up" ref="promoFrom"
              :disabled="updatePending || promo.outdated" :placeholder="$t('date.from-placeholder')"
              @input="promoFromChanged" />
          </div>
          <div class="col-12 col-md-6">
            <label class="text-xs">{{ $t('date.until') }}</label>
            <date-picker
              id="promo-until" v-model="promo.until" :min-date="today" grow="md-up" ref="promoUntil"
              position="left-md-right"
              :disabled="updatePending || promo.outdated" :placeholder="$t('date.until-placeholder')"
              @input="promoUntilChanged" />
          </div>
        </div>
      </ValidationObserver>
    </b-modal>
    <div>
      <div class="panel-title position-relative w-100 title">
        <p>{{ channelName }}</p>
      </div>
      <div class="row" v-if="loaded && channel.type === 'push'">
        <push-channel-info-modal
          :pending="updatePending"
          ref="updateModal"
          @ok="updateChannel"
        />
        <div class="col-6">
          <div class="row" v-if="channel.period">
            <div class="col cell-field">{{ periodTitle }}</div>
            <div class="col cell-value">{{ periodValue }}</div>
          </div>
          <div class="row" v-for="field in fieldsValues" :key="`ccf-${field.key}`">
            <div class="col cell-field">{{ field.title }}</div>
            <div class="col cell-value">{{ field.value }}</div>
          </div>
          <div class="row">
            <div class="col cell-change-settings">
              <b-btn variant="outline-primary" @click="openUpdateModal">
                {{ $t('pages.channels.connect.btn-change-settings') }}
              </b-btn>
            </div>
          </div>
        </div>
      </div>

      <floater v-if="channelIsValid && !isPullChannel" :shown="cmap.selected.length > 0">
        <template #content>
          <div class="floater-table">
            <div class="connect-heading">
              <p class="headline">{{ channel.name }}</p>
              <p class="separator"></p>
              <p class="headline">{{ $t('pages.channels.connect.my-rate-plans') }}</p>
            </div>
            <div class="connect-table">
              <div class="connect-row" v-for="{ plan, cplan, uniq, mode, ctype, ptype } in cmap.selected"
                   :key="`cnn-${uniq}`">
                <div class="cell-dst">
                  <p class="room"><b>{{ $t('pages.channels.connect.mapped-room') }}:</b>&nbsp;{{ ctype }}
                    ({{ cplan.typeid }})</p>
                  <p><b>{{ $t('pages.channels.connect.mapped-plan') }}:&nbsp;</b>{{ cplan.name }} ({{ cplan.id }})</p>
                </div>
                <div class="cell-link"><icon type="link" width="20" height="20"/></div>
                <div class="cell-src">
                  <p class="room"><b>{{ $t('pages.channels.connect.mapped-room') }}:</b>&nbsp;{{ ptype.text }}
                    ({{ ptype.id }})</p>
                  <p><b>{{ $t('pages.channels.connect.mapped-plan') }}:&nbsp;</b>{{ plan.text }} ({{ plan.id }})</p>
                </div>
                <div class="cell-upd text-nowrap">
                  <b-dropdown size="sm" toggle-tag="span" variant="link" no-caret dropup right
                              :disabled="updatePending">
                    <template #button-content>
                      {{ updateTypeText(mode) }}
                      <icon width="18" height="18" type="expand"/>
                    </template>
                    <b-dropdown-item v-for="{ id, text } in updateTypes" :key="`mode-${uniq}-${id}`"
                                     :disabled="updatePending || id === mode" @click="changeUpdateType(uniq, id)"
                    >{{ $t(text) }}</b-dropdown-item>
                  </b-dropdown>
                </div>
                <div class="cell-action">
                  <b-btn class="btn-icon btn-tiny" @click="unlinkPlan(uniq)" :disabled="updatePending">
                    <icon type="delete" width="14" height="16"/>
                  </b-btn>
                </div>
              </div>
            </div>
          </div>
        </template>
        <template #footer>
          <span class="connections-count">
            {{ $tc('pages.channels.connect.selected-connections-count', cmap.selected.length) }}
          </span>
          <b-btn pill variant="outline-primary" size="sm" :disabled="updatePending"
                 @click="connectPlans">{{ $t('pages.channels.connect.btn-connect') }}</b-btn>
        </template>
      </floater>

      <floater v-if="channelIsValid && isPullChannel" :shown="hasPullChanges" no-content>
        <template #footer>
          <span class="connections-count">
            {{ $tc('pages.channels.connect.selected-changes-count', pullChangesCount) }}
          </span>
          <b-btn pill variant="outline-primary" size="sm" :disabled="updatePending"
                 @click="updatePullMappings">{{ $t('buttons.update') }}</b-btn>
        </template>
      </floater>

      <tabs :items="tabs" v-model="tab" with-content v-if="channelIsValid" @switch="seluniq = null">
        <template #tab(pending) v-if="!isPullChannel">
          <div class="tablist-none" v-if="!pendingCTypes.length">
            {{ $t('pages.channels.connect.no-pending') }}
          </div>
          <div class="pending-table" v-else>
            <div class="dst-list">
              <p class="headline">{{ channel.name }}</p>
              <div class="rates-list" :class="{ active: seluniq != null }">
                <div class="type-item" v-for="{ name: tname, typeid, plans } in pendingCTypes"
                     :key="`ctype-${typeid}`">
                  <h6 :class="{ opened: isOpen(typeid) }" @click="toggleOpen(typeid)">
                    {{ tname }}&nbsp;({{ typeid }})
                    <icon width="13" height="7" stroke-width="2" type="arrow-down" class="icon-open"/>
                    <icon width="13" height="7" stroke-width="2" type="arrow-up" class="icon-close"/>
                  </h6>
                  <div>
                    <p v-for="{ id, name, uniq } in plans" :key="`cplan-${id}-${typeid}`"
                       @click="setCRate(uniq)" :class="{ active: isActiveCRate(uniq) }">
                      <icon width="20" height="20" type="link"/>{{ name }} ({{ id }})
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="src-list" :class="{ 'd-none': seluniq == null }">
              <p class="headline">{{ $t('pages.channels.connect.my-rate-plans') }}</p>
              <div class="rates-list">
                <div class="type-item" v-for="({ pid, id, text, rates }) in pendingRooms" :key="`type-${pid}`">
                  <h6 :class="{ opened: isOpen(pid) }" @click="toggleOpen(pid)">
                    {{ text }}&nbsp;({{ id }})
                    <icon width="13" height="7" stroke-width="2" type="arrow-down" class="icon-open"/>
                    <icon width="13" height="7" stroke-width="2" type="arrow-up" class="icon-close"/>
                  </h6>
                  <div>
                    <p v-for="plan in rates" :key="`plan-${plan.id}-${pid}`"
                       @click="linkPlan(plan)">
                      <icon width="20" height="20" type="link"/>{{ plan.text }} ({{ plan.id }})
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
        <template #tab(connected) v-if="!isPullChannel">
          <div class="tablist-none" v-if="!hasMappedPlans">
            {{ $t('pages.channels.connect.no-connected') }}
          </div>
          <div class="mapped-table" v-else>
            <div class="mapped-heading">
              <p class="headline">{{ channel.name }}</p>
              <p class="separator"></p>
              <p class="headline">{{ $t('pages.channels.connect.my-rate-plans') }}</p>
            </div>
            <table class="w-100">
              <tbody v-for="item in mappedPlans" :key="`mapped-${item.uniq}`">
                <tr>
                  <td class="cell-dst">
                    <p class="room"><b>{{ $t('pages.channels.connect.mapped-room') }}:&nbsp;</b>{{ item.ctype }}
                      ({{ item.cplan.typeid }})</p>
                    <p><b>{{ $t('pages.channels.connect.mapped-plan') }}:&nbsp;</b>{{ item.cplan.name }}
                      ({{ item.cplan.id }})</p>
                  </td>
                  <td class="cell-src">
                    <p class="room"><b>{{ $t('pages.channels.connect.mapped-room') }}:&nbsp;</b>{{ item.room.text }}
                      ({{ item.room.id }})</p>
                    <p><b>{{ $t('pages.channels.connect.mapped-plan') }}:&nbsp;</b>{{ item.plan.text }}
                      ({{ item.plan.id }})</p>
                    <p>
                      <b-dropdown size="sm" toggle-tag="span" variant="link" no-caret dropup right
                                  :disabled="updatePending">
                        <template #button-content>
                          {{ updateTypeText(item.mode) }}
                          <icon width="18" height="18" type="expand"/>
                        </template>
                        <b-dropdown-item v-for="{ id, text } in updateTypes" :key="`mode-${item.uniq}-${id}`"
                                         :disabled="updatePending || id === item.mode"
                                         @click="updateConnection(item, { mode: id })"
                        >{{ $t(text) }}</b-dropdown-item>
                      </b-dropdown>
                    </p>
                  </td>
                  <td class="cell-action">
                    <b-btn pill variant="danger" size="sm"
                           @click="disconnectPlan(item)" :disabled="updatePending"
                    >{{ $t('pages.channels.connect.btn-disconnect') }}</b-btn>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
        <template #tab(mapping) v-if="isPullChannel">
          <div class="tablist-none" v-if="!hasPlans">
            {{ $t('pages.channels.connect.no-plans') }}
          </div>
          <div class="plans-table" v-else>
            <div class="plans-heading">
              <p class="headline">
                <check-box :value="isAllMySelected" @input="toggleMyAll" :disabled="updatePending || !plans.length"
                           class="select-all">{{ $t('pages.channels.map-modal.checkbox-toggle-all') }}</check-box>
              </p>
            </div>
            <table class="w-100">
              <tbody v-for="{ plan, room } in mappedMyPlans" :key="`mapped-my-${plan.id}`">
                <tr>
                  <td class="cell-onoff">
                    <check-box v-model="cmap.pselected" :val="plan.id" :disabled="updatePending" empty/>
                  </td>
                  <td class="cell-src">
                    <p class="room"><b>{{ $t('pages.channels.connect.mapped-room') }}:&nbsp;</b>{{ room.text }}
                      ({{ room.id }})</p>
                    <p><b>{{ $t('pages.channels.connect.mapped-plan') }}:&nbsp;</b>{{ plan.text }}
                      ({{ plan.id }})</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
        <template #tab(urls)>
          <div class="urls-block">
            <p>{{ $t('pages.channels.urls.main-link') }}</p>
            <b-form-row class="link-fields">
              <div class="col cell-link-field copyable copy-icon">
                <button class="btn-icon" @click="copyMainUrl">
                  <icon w="19" h="19" type="copy"></icon>
                </button>
                <b-input readonly class="mb-0" ref="urlMain" :value="urlMain" />
                <b-form-valid-feedback tooltip ref="urlMainTip">
                  {{ $t('setup.steps.5.msg-copied') }}
                </b-form-valid-feedback>
              </div>
              <div class="col cell-link-tip">
                <a :href="urlMain" target="_blank">
                  {{ $t('pages.channels.urls.open-in-new-tab') }}
                  <icon w="16" h="16" type="new-window" class="ml-1" />
                </a>
              </div>
            </b-form-row>
            <p class="mt-4">{{ $t('pages.channels.urls.custom-link') }}</p>
            <b-form-row class="link-fields">
              <div class="col cell-link-field copyable copy-icon">
                <button class="btn-icon" @click="copyCustomUrl" :disabled="!urlCustom">
                  <icon w="19" h="19" type="copy"></icon>
                </button>
                <b-input readonly class="mb-0" ref="urlCustom" :value="urlCustom" />
                <b-form-valid-feedback tooltip ref="urlCustomTip">
                  {{ $t('setup.steps.5.msg-copied') }}
                </b-form-valid-feedback>
              </div>
              <div class="col cell-link-tip">
                <a :href="urlCustom" target="_blank" :disabled="!urlCustom" :class="{ disabled: !urlCustom }">
                  {{ $t('pages.channels.urls.open-in-new-tab') }}
                  <icon w="16" h="16" type="new-window" class="ml-1" />
                </a>
              </div>
              <p class="col-12 text-sm mt-3">
                {{ $t('pages.channels.urls.custom-link-tip') }}
              </p>
            </b-form-row>
            <products-selector
              parted
              v-if="mapped.length > 0"
              :plans="mappedMyPlansForSelector"
              :rooms="rooms"
              :selected-plans.sync="umap.plans"
              :selected-rooms.sync="umap.rooms"
            />
            <p v-else>{{ $t('pages.channels.urls.no-mapped-products') }}</p>
          </div>
        </template>
        <template #tab(promos)>
          <div class="promo-block">
            <div class="promo-header">
              <div class="promo-header-filters">
                <search-filter v-model="promoFilter.promo" autofocus :placeholder="$t('pages.channels.promo.search')"
                               :disabled="updatePending"/>
                <check-box v-model="promoAll.promo" :disabled="updatePending">
                  {{ $t('pages.channels.promo.all-promos') }}
                </check-box>
              </div>
              <b-btn variant="secondary" pill @click="openCreatePromo('promo')" :disabled="updatePending">
                <icon type="plus" w="10" h="10"/>
                {{ $t('pages.channels.promo.button-add-promo') }}
              </b-btn>
            </div>
            <table class="w-100">
              <thead>
                <tr>
                  <th class="w-name">{{ $t('pages.channels.promo.headers.promo') }}</th>
                  <th class="w-discount">{{ $t('pages.channels.promo.headers.discount') }}</th>
                  <th class="w-plans">{{ $t('pages.channels.promo.headers.plans') }}</th>
                  <th class="w-validity">{{ $t('pages.channels.promo.headers.validity') }}</th>
                  <th class="w-actions">{{ $t('actions') }}</th>
                </tr>
              </thead>
              <tbody v-if="!promoItems('promo').length">
                <tr>
                  <td colspan="5" class="w-empty">
                    {{ $t(`pages.channels.promo.${promoFilter.promo ? 'filter-no-codes' : 'no-codes'}-promo`) }}
                  </td>
                </tr>
              </tbody>
              <tbody v-for="promo in promoItems('promo')" :key="`promo-${promo.id}`">
                <tr class="separator before"></tr>
                <tr>
                  <td>
                    <p>
                      {{ promo.name }}
                      <b-badge variant="primary" class="ml-1">{{ promo.code }}</b-badge>
                    </p>
                  </td>
                  <td>{{ promoDiscount(promo, true) }}</td>
                  <td>
                    <p v-for="({ id, text, rid, rtext }, idx) in promoRates(promo)" :key="`pr-${promo.code}-${idx}`">
                      {{ rtext }} ({{ rid }}) &centerdot; {{ text }} ({{ id }})
                    </p>
                  </td>
                  <td>
                    <p>{{ formatDate(promo, 'from', false) }} ~ {{ formatDate(promo, 'until', false) }}</p>
                  </td>
                  <td class="actions">
                    <b-btn v-if="!promo.outdated" class="btn-icon btn-tiny"
                           @click="editPromo(promo)" :disabled="updatePending">
                      <icon width="17" height="17" type="edit"/>
                    </b-btn>
                    <b-btn v-else class="btn-icon btn-tiny"
                           @click="editPromo(promo)" :disabled="updatePending">
                      <icon width="20" height="17" type="visible"/>
                    </b-btn>
                    <b-btn class="btn-icon btn-tiny" @click="deletePromo(promo)"
                           :disabled="updatePending">
                      <icon width="19" height="19" type="delete"/>
                    </b-btn>
                  </td>
                </tr>
                <tr class="separator after"></tr>
              </tbody>
            </table>
          </div>
        </template>
        <template #tab(contracts)>
          <div class="promo-block">
            <div class="promo-header">
              <div class="promo-header-filters">
                <search-filter v-model="promoFilter.contract" autofocus :placeholder="$t('pages.channels.promo.search')"
                               :disabled="updatePending"/>
                <check-box v-model="promoAll.contract" :disabled="updatePending">
                  {{ $t('pages.channels.promo.all-promos') }}
                </check-box>
              </div>
              <b-btn variant="secondary" pill @click="openCreatePromo('contract')" :disabled="updatePending">
                <icon type="plus" w="10" h="10"/>
                {{ $t('pages.channels.promo.button-add-contract') }}
              </b-btn>
            </div>
            <table class="w-100">
              <thead>
                <tr>
                  <th class="w-name">{{ $t('pages.channels.promo.headers.contract') }}</th>
                  <th class="w-plans">{{ $t('pages.channels.promo.headers.plans') }}</th>
                  <th class="w-validity">{{ $t('pages.channels.promo.headers.validity') }}</th>
                  <th class="w-actions">{{ $t('actions') }}</th>
                </tr>
              </thead>
              <tbody v-if="!promoItems('contract').length">
                <tr>
                  <td colspan="4" class="w-empty">
                    {{ $t(`pages.channels.promo.${promoFilter.promo ? 'filter-no-codes' : 'no-codes'}-contract`) }}
                  </td>
                </tr>
              </tbody>
              <tbody v-for="promo in promoItems('contract')" :key="`contract-${promo.id}`">
                <tr class="separator before"></tr>
                <tr>
                  <td>
                    <p>
                      {{ promo.name }}
                      <b-badge variant="success" class="ml-1">{{ promo.code }}</b-badge>
                    </p>
                  </td>
                  <td>
                    <p v-for="({ id, text, rid, rtext }, idx) in promoRates(promo)" :key="`cr-${promo.code}-${idx}`">
                      {{ rtext }} ({{ rid }}) &centerdot; {{ text }} ({{ id }})
                    </p>
                  </td>
                  <td>
                    <p>{{ formatDate(promo, 'from', false) }} ~ {{ formatDate(promo, 'until', false) }}</p>
                  </td>
                  <td class="actions">
                    <b-btn v-if="!promo.outdated" class="btn-icon btn-tiny"
                           @click="editPromo(promo)" :disabled="updatePending">
                      <icon width="17" height="17" type="edit"/>
                    </b-btn>
                    <b-btn v-else class="btn-icon btn-tiny"
                           @click="editPromo(promo)" :disabled="updatePending">
                      <icon width="20" height="17" type="visible"/>
                    </b-btn>
                    <b-btn class="btn-icon btn-tiny" @click="deletePromo(promo)"
                           :disabled="updatePending">
                      <icon width="19" height="19" type="delete"/>
                    </b-btn>
                  </td>
                </tr>
                <tr class="separator after"></tr>
              </tbody>
            </table>
          </div>
        </template>
      </tabs>

      <b-alert v-if="channelIsInvalid && !updatePending" variant="danger" show>
        <h4 class="alert-heading">{{ $t('error') }}</h4>
        <p class="mb-0">{{ $t('pages.channels.connect.error-invalid-property-data') }}</p>
      </b-alert>
    </div>
  </div>
</template>

<script>
  import moment from 'moment';
  import {
    mapActions, mapGetters, mapState, mapMutations,
  } from 'vuex';
  import { channelUpdateTypes, externalEngineURL } from '@/shared';
  import { HttpError, PMSError, ValidationError } from '@/errors';
  import PushChannelInfoModal from '@/components/PushChannelInfoModal.vue';

  export default {
    name: 'Channel',
    components: { PushChannelInfoModal },
    data: () => ({
      period: {
        type: 0,
        number: '',
        unit: 'd',
        until: null,
      },
      cmap: {
        selected: [],
        open: [],
        pselected: [],
      },
      id: null,
      tab: null, // 'connected',

      seluniq: null,

      umap: {
        rooms: [],
        plans: [],
      },

      promoFilter: { promo: '', contract: '' },
      promoAll: { promo: true, contract: true },
      promo: {
        mode: 'promo',
        plans: [],
      },
      today: moment(),
    }),
    // async created() {
    //   await this.fetchData(true);
    // },
    mounted() {
      this.setChannelId(this.$route.params.id);
    },
    watch: {
      $route() {
        this.setChannelId(this.$route.params.id);
      },
    },
    computed: {
      ...mapGetters('channel', ['loaded', 'channel', 'ctypes', 'cplans', 'mapped', 'rooms', 'plans']),
      ...mapState('channel', ['error', 'pending', 'updatePending', 'updateError']),
      ...mapGetters('user', ['hotelID']),

      tabs() {
        if (this.channel.type === 'push') {
          return [
            { id: 'pending', title: this.$t('pages.channels.connect.tab-pending') },
            { id: 'connected', title: this.$t('pages.channels.connect.tab-connected') },
          ];
        }
        const tabs = [
          { id: 'mapping', title: this.$t('pages.channels.headers.mapping') },
        ];
        if (this.channel.default) {
          tabs.push(
            { id: 'urls', title: this.$t('pages.channels.tabs.urls') },
            { id: 'promos', title: this.$t('pages.channels.tabs.promos') },
            { id: 'contracts', title: this.$t('pages.channels.tabs.contracts') },
          );
        }
        return tabs;
      },

      channelName() {
        if (!this.loaded) return '...';
        return this.channel.name;
      },
      channelIsValid() {
        return this.loaded && !this.channel.invalid;
      },
      channelIsInvalid() {
        return this.loaded && this.channel.invalid;
      },
      periodTitle() {
        return this.$t(`pages.channels.connect.transmission-${this.channel.period.type ? 'end-date' : 'period'}`);
      },
      periodValue() {
        const { period } = this.channel;
        if (period.type) {
          // end date
          return moment(period.until).format(this.$t('pages.channels.connect.transmission-format'));
        }
        // period
        return this.$tc(`pages.channels.connect.time-units-values.${period.unit}`, period.number);
      },
      fieldsValues() {
        const { values = {}, fields = [] } = this.channel;
        return fields.map(({ key, title, subtype }) => ({
          key,
          title,
          subtype,
          value: values[key],
        })).filter(({ value, subtype }) => (value != null && subtype !== 'password'));
      },
      updateTypes: () => channelUpdateTypes,
      pendingCTypes() {
        const { ctypes } = this;
        return Object.keys(ctypes)
          .map((typeid) => ({
            typeid,
            name: ctypes[typeid],
            plans: this.pendingRatesOfType(typeid),
          }))
          .filter(({ plans }) => plans.length);
      },
      pendingRooms() {
        const { rooms } = this;
        return rooms
          .map((room) => ({
            ...room,
            rates: this.pendingPlansOfRoom(room.pid),
          }))
          .filter(({ rates }) => rates.length);
      },
      hasPlans() {
        return this.plans.length > 0;
      },
      hasMappedPlans() {
        return Object.keys(this.mapped).length > 0;
      },
      mappedPlans() {
        if (this.isPullChannel) return [];
        return Object.keys(this.mapped).map((id) => {
          const { uniq, mode } = this.mapped[id];
          const cplan = this.cplans.find((plan) => plan.uniq === uniq);
          const ctype = this.ctypes[cplan.typeid];
          const plan = this.plans.find((rate) => rate.id === id);
          const room = this.rooms.find(({ pid }) => pid === plan.room);
          return {
            uniq, cplan, plan, ctype, room, mode,
          };
        });
      },
      mappedMyPlans() {
        if (!this.isPullChannel) return [];
        return this.plans.map((plan) => {
          const room = this.rooms.find(({ pid }) => pid === plan.room);
          return {
            plan, room,
          };
        });
      },
      mappedMyPlansForSelector() {
        const { mapped } = this;
        if (!this.isPullChannel || !this.channel.default || !mapped.length) return [];
        return this.plans.filter(({ id }) => mapped.includes(id));
      },
      isAllMySelected() {
        return this.plans.length && this.cmap.pselected.length === this.plans.length;
      },
      isPullChannel() {
        return this.channel.type === 'pull';
      },
      hasPullChanges() {
        return this.pullChangesCount > 0;
      },
      pullChangesCount() {
        const add = this.cmap.pselected.filter((id) => !this.mapped.includes(id));
        const del = this.mapped.filter((id) => !this.cmap.pselected.includes(id));
        return add.length + del.length;
      },
      urlMain() {
        return `${externalEngineURL}${this.hotelID}`;
      },
      urlCustom() {
        const { plans, rooms } = this.umap;
        if (!plans.length && !rooms.length) return '';
        const pc = plans.length ? `&pc=${plans.join(',')}` : '';
        const rc = rooms.length ? `&rc=${rooms.join(',')}` : '';
        return `${externalEngineURL}${this.hotelID}${pc}${rc}`;
      },
      promoCreatePlans() {
        return this.plans.filter(({ promo }) => promo == null);
      },
      promoModalTitle() {
        const {
          id, code, outdated, mode,
        } = this.promo;
        if (id == null) {
          return this.$t(`pages.channels.promo.modal.title-add-${mode}`);
        }
        if (outdated) {
          return this.$t(`pages.channels.promo.modal.title-view-${mode}`, { code });
        }
        return this.$t(`pages.channels.promo.modal.title-edit-${mode}`, { code });
      },
      promoOkTitle() {
        const { id, outdated } = this.promo;
        if (id == null) {
          return this.$t('buttons.save');
        }
        if (outdated) {
          return this.$t('buttons.close');
        }
        return this.$t('buttons.update');
      },
      promoFormValid() {
        if (this.promo.outdated) return true;
        const { promoForm } = this.$refs;
        if (promoForm == null) return false;
        const { from, until, plans } = this.promo;
        return promoForm.flags.valid && from != null && until != null
          && (this.promoIsEdit || (plans != null && plans.length > 0));
      },
      promoIsEdit() {
        return this.promo.id != null;
      },
    },
    methods: {
      ...mapActions('channel', [
        'fetchData', 'connectRatePlans', 'disconnectRatePlan', 'channelMappings',
        'updateChannelData', 'updatePlanConnection',
        'createContract', 'updateContract', 'deleteContract',
      ]),
      ...mapMutations('channel', ['clearErrors']),

      toggleMyAll(val) {
        if (!val) {
          this.cmap.pselected = [];
        } else {
          this.cmap.pselected = this.plans.map(({ id }) => id);
        }
      },
      async setChannelId(id) {
        this.id = id;
        try {
          await this.fetchData({ id, force: true });
        } catch (e) {
          if (e instanceof HttpError && e.errorCode === 400) {
            await this.$router.replace({ name: 'channels' });
            return;
          }
        }
        this.freshData();
      },
      freshData() {
        if (this.channel.type === 'push') {
          this.tab = 'pending';
        } else {
          this.cmap.pselected = [...this.mapped];
          this.tab = 'mapping';
        }
      },
      copyMainUrl() {
        this.$refs.urlMain.select();
        document.execCommand('copy');
        navigator.clipboard.writeText(this.urlMain);
        const tip = this.$refs.urlMainTip;
        tip.classList.add('visible');
        setTimeout(() => tip.classList.remove('visible'), 1000);
      },
      copyCustomUrl() {
        this.$refs.urlCustom.select();
        document.execCommand('copy');
        navigator.clipboard.writeText(this.urlCustom);
        const tip = this.$refs.urlCustomTip;
        tip.classList.add('visible');
        setTimeout(() => tip.classList.remove('visible'), 1000);
      },
      isActiveCRate(uniq) {
        return this.seluniq === uniq;
      },
      setCRate(uniq) {
        if (this.updatePending) return;
        if (this.isActiveCRate(uniq)) {
          this.seluniq = null;
        } else {
          this.seluniq = uniq;
        }
      },
      pendingRatesOfType(typeid) {
        const { mapped } = this;
        const selected = this.cmap.selected.map(({ uniq }) => uniq);
        const del = Object.keys(mapped).map((id) => mapped[id].uniq);
        return this.cplans.filter((plan) => (
          plan.typeid === typeid
          && !del.includes(plan.uniq)
          && !selected.includes(plan.uniq)
        ));
      },
      pendingPlansOfRoom(pid) {
        const { mapped } = this;
        const selected = this.cmap.selected.map(({ plan: { id } }) => id);
        const del = Object.keys(mapped);
        return this.plans.filter((plan) => (
          plan.room === pid
          && !del.includes(plan.id)
          && !selected.includes(plan.id)
        ));
      },
      linkPlan(plan) {
        if (this.updatePending) return;
        const uniq = this.seluniq;
        const cplan = this.cplans.find((rate) => rate.uniq === uniq);
        const ctype = this.ctypes[cplan.typeid];
        const ptype = this.rooms.find(({ pid }) => pid === plan.room);
        this.cmap.selected.push({
          plan,
          ptype,
          cplan,
          ctype,
          uniq,
          mode: 0,
        });
        this.seluniq = null;
      },
      unlinkPlan(uniq) {
        const idx = this.cmap.selected.findIndex((plan) => plan.uniq === uniq);
        if (idx !== -1) {
          this.cmap.selected.splice(idx, 1);
        }
      },
      updateTypeText(mode) {
        return this.$t(`channel-update-types.${mode}`);
      },
      changeUpdateType(uniq, mode) {
        const sel = this.cmap.selected.find((s) => s.uniq === uniq);
        if (sel != null) sel.mode = mode;
      },
      formatDate(row, field = 'dt', emptyField = 'enabled') {
        return row[field] && (!emptyField || row[emptyField]) ? moment(row[field]).format('D MMM YYYY') : '';
      },
      promoItems(mode) {
        const { contractor } = this.channel;
        let codes = contractor == null || contractor.codes == null ? false : contractor.codes;
        if (codes === false) return [];
        codes = codes.filter((i) => i.mode === mode);
        const filter = this.promoFilter[mode].trim().toLowerCase();
        if (!filter && this.promoAll[mode]) return codes;
        if (filter) {
          codes = codes.filter(({ code, name }) => (
            code.toLowerCase().includes(filter) || name.toLowerCase().includes(filter)
          ));
        }
        if (!this.promoAll[mode]) {
          codes = codes.filter(({ outdated }) => !outdated);
        }
        return codes;
      },
      promoDiscount(contract, addsign = false) {
        const rate = this.plans.find(({ promo }) => promo === contract.code);
        if (rate == null) return addsign ? '—' : 0;
        const d = rate.price.stdcalc.reduction.value;
        return addsign ? `${d}%` : d;
      },
      promoRates(contract) {
        return this.plans
          .filter(({ promo }) => promo === contract.code)
          .map(({ id, text, room }) => {
            const { id: rid, text: rtext } = this.rooms.find(({ pid }) => pid === room);
            return {
              id, text, rid, rtext,
            };
          });
      },
      promoFromChanged(dt) {
        if (dt.isAfter(this.promo.until, 'date')) {
          this.promo.until = moment(dt);
        }
        this.$nextTick(() => {
          this.$refs.promoUntil.$el.focus();
        });
      },
      promoUntilChanged(dt) {
        if (dt.isBefore(this.promo.from, 'date')) {
          this.promo.from = moment(dt);
        }
      },
      modalScroll(ev) {
        const modal = ev.target;
        this.$nextTick(() => {
          if (modal != null) modal.scrollTop = 0;
        });
        this.clearErrors();
      },
      resetPromoForm(mode, reset = true) {
        this.$set(this.promo, 'plans', []);
        this.promo = {
          mode,
          name: '',
          code: '',
          discount: '',
          from: moment(),
          until: moment().add(1, 'month'),
          plans: [],
        };
        if (reset && this.$refs.promoForm != null) {
          this.$refs.promoForm.reset();
        }
      },
      openCreatePromo(mode) {
        this.resetPromoForm(mode);
        this.$nextTick(this.$refs.promoModal.show);
      },
      editPromo(contract) {
        this.resetPromoForm(contract.mode, false);
        const discount = this.promoDiscount(contract);
        this.promo = {
          ...this.promo,
          ...JSON.parse(JSON.stringify(contract)),
          discount,
        };
        this.$nextTick(() => {
          this.$refs.promoForm.reset();
          this.$refs.promoModal.show();
        });
      },
      async deletePromo(promo) {
        const { id } = this.channel;
        try {
          await this.deleteContract({ id, promo });
        } catch (error) {
          this.$toastr.e(error.message, this.$t('error'));
        }
      },
      async processPromoForm() {
        this.$refs.promoForm.reset();
        const { promo } = this;
        if (promo.outdated) {
          this.$nextTick(this.$refs.promoModal.hide);
          return;
        }
        ['from', 'until'].forEach((k) => {
          const v = promo[k];
          if (moment.isMoment(v)) {
            promo[k] = v.format('YYYY-MM-DD');
          }
        });

        const { id } = this.channel;
        try {
          if (promo.id != null) {
            await this.updateContract({ id, promo });
          } else {
            await this.createContract({ id, promo });
          }
          this.$refs.promoModal.hide();
        } catch (error) {
          if (!(error instanceof ValidationError)) {
            this.$toastr.e(error.message, this.$t('error'));
          }
        }
      },
      isOpen(id) {
        return this.cmap.open.indexOf(id) === -1;
      },
      toggleOpen(id) {
        const idx = this.cmap.open.indexOf(id);
        if (idx !== -1) {
          this.cmap.open.splice(idx, 1);
        } else {
          this.cmap.open.push(id);
        }
      },
      openUpdateModal() {
        const {
          id, fields, values, period,
        } = this.channel;
        this.$refs.updateModal.show(id, fields, { ...values, period: { ...period } });
      },
      async updateChannel({ id, values }) {
        try {
          await this.updateChannelData({ id, payload: values });
          this.$toastr.s({
            title: this.$t('pages.masterdata.alert-saved'),
            msg: '',
            timeout: 3000,
            progressbar: false,
          });
          if (!this.invalid) {
            this.freshData();
          }
          this.$refs.updateModal.hide();
        } catch (err) {
          if (err instanceof PMSError) {
            this.$toastr.e(err.message, this.$t('error'));
          }
        }
      },
      async connectPlans() {
        try {
          await this.connectRatePlans({ id: this.id, list: this.cmap.selected });
          this.cmap.selected = [];
          this.$toastr.s({
            title: this.$t('pages.channels.connect.alert-connected'),
            msg: '',
            timeout: 3000,
            progressbar: false,
          });
        } catch (e) {
          if (e instanceof PMSError) {
            this.$toastr.e(e.message, this.$t('error'));
          }
          // eslint-disable-next-line no-console
          console.error(e.message);
        }
      },
      async disconnectPlan(item) {
        try {
          await this.disconnectRatePlan({ id: this.id, list: [item] });
          this.$toastr.s({
            title: this.$t('pages.channels.connect.alert-disconnected'),
            msg: '',
            timeout: 3000,
            progressbar: false,
          });
        } catch (e) {
          if (e instanceof PMSError) {
            this.$toastr.e(e.message, this.$t('error'));
          }
          // eslint-disable-next-line no-console
          console.error(e.message);
        }
      },
      async updateConnection(item, updates) {
        try {
          await this.updatePlanConnection({ id: this.id, room: item, updates });
          this.$toastr.s({
            title: this.$t('pages.channels.connect.alert-updated'),
            msg: '',
            timeout: 3000,
            progressbar: false,
          });
        } catch (e) {
          if (e instanceof PMSError) {
            this.$toastr.e(e.message, this.$t('error'));
          }
          // eslint-disable-next-line no-console
          console.error(e.message);
        }
      },
      async updatePullMappings() {
        const { pselected } = this.cmap;
        const rooms = this.plans.map(({ id }) => ({ id, inv: pselected.includes(id) }));
        const { id } = this;
        try {
          await this.channelMappings({ id, rooms });
          this.$toastr.s({
            title: this.$t('pages.channels.connect.alert-updated'),
            msg: '',
            timeout: 3000,
            progressbar: false,
          });
          this.cmap.pselected = [...this.mapped];
        } catch (e) {
          if (e instanceof PMSError) {
            this.$toastr.e(e.message, this.$t('error'));
          }
          // eslint-disable-next-line no-console
          console.error(e.message);
        }
      },
    },
  };
</script>

<template>
  <div class="page-channels" id="page-channels">
    <div>
      <div class="panel-title position-relative w-100 title">
        <p>{{ $t('pages.channels.title') }}</p>
      </div>

      <push-channel-info-modal :pending="updatePending" ref="registerModal" @ok="registerChannel" />

      <tabs :items="tabs" v-model="tab" with-content v-if="loaded">
        <template #tab(available)>
          <div class="list">
            <div class="d-flex justify-content-end">
              <search-filter v-model="filter" :disabled="!loaded || updatePending"
                             :placeholder="$t('pages.channels.filter-tip')"/>
            </div>
            <div class="tablist-none" v-if="!availableChannels.length">
              {{ $t(`pages.channels.${filter ? 'filter-none' : 'no-available'}`) }}
            </div>
            <div v-else class="plans-table">
              <table class="w-100">
                <thead>
                  <tr>
                    <th class="w-name">{{ $t('pages.channels.headers.name') }}</th>
                    <th class="w-actions text-right">{{ $t('actions') }}</th>
                  </tr>
                </thead>

                <tbody v-for="row in availableChannels" :key="row.id">
                  <tr class="separator before"></tr>
                  <tr>
                    <td class="cell-name">
                      <template v-if="row.url">
                        <a :href="row.url" target="_blank">{{ row.name }}</a>
                        <icon type="new-window-alt" w="12" h="13" />
                      </template>
                      <span v-else>{{ row.name }}</span>
                    </td>
                    <td class="actions available text-right">
                      <div class="d-inline-block">
                        <b-btn variant="primary"
                               :disabled="updatePending || !row.enabled"
                               @click="changeState(row, row.dt ? 'activate' : 'start')"
                        >{{ $t('pages.channels.button-activate') }}</b-btn>
                        <span v-if="0" class="status-date">{{ formatDate(row) }}</span>
                      </div>
                    </td>
                  </tr>
                  <tr class="separator after"></tr>
                </tbody>
              </table>
            </div>
          </div>
        </template>
        <template #tab(connected)>
          <div class="list">
            <div class="d-flex justify-content-end">
              <search-filter v-model="filter" :disabled="!loaded || updatePending"
                             :placeholder="$t('pages.channels.filter-tip')"/>
            </div>
            <div class="tablist-none" v-if="!connectedChannels.length">
              {{ $t(`pages.channels.${filter ? 'filter-none' : 'no-connected'}`) }}
            </div>
            <div v-else class="plans-table">
              <table class="w-100">
                <thead>
                  <tr>
                    <th class="w-name">{{ $t('pages.channels.headers.name') }}</th>
                    <th class="w-mapping text-center">{{ $t('pages.channels.headers.mapping') }}</th>
                    <th class="w-property-ids text-center">{{ $t('pages.channels.headers.ids') }}</th>
                    <!-- <th class="w-date">Date</th> -->
                    <th class="w-actions text-right">{{ $t('actions') }}</th>
                  </tr>
                </thead>

                <tbody v-for="row in connectedChannels" :key="row.id">
                  <tr class="separator before"></tr>
                  <tr>
                    <td class="cell-name">
                      <template v-if="row.url">
                        <a :href="row.url" target="_blank">{{ row.name }}</a>
                        <icon type="new-window-alt" w="12" h="13" />
                      </template>
                      <span v-else>{{ row.name }}</span>
                    </td>
                    <td class="cell-mapping text-center">
                      <b-btn v-if="row.status !== 'inactive'"
                             :variant="connectButtonVariant(row)"
                             :disabled="updatePending || !row.enabled"
                             @click="connectButtonClicked(row)"
                      >{{ connectButtonText(row) }}</b-btn>
                    </td>
                    <td class="cell-ids text-center">
                      <icon width="16" height="16" type="info"
                            v-b-tooltip:page-channels.bottom="propertyIds(row.id)"
                            v-if="row.status !== 'inactive'"
                      />
                    </td>
                    <!--
                    <td>
                      {{ formatDate(row.dt) }}
                    </td>
                    -->
                    <td class="actions text-right">
                      <div class="d-inline-block text-left">
                        <spinner v-if="updatePending && row.enabled" />
                        <fragment v-else>
                          <span class="status" :class="`status-${row.status}`">
                            <b-dropdown size="sm" toggle-tag="span" variant="link" no-caret right
                                        :disabled="!row.enabled">
                              <template #button-content>
                                {{ statusText(row.status) }}
                                <icon v-if="row.enabled" width="18" height="18" type="expand"/>
                              </template>
                              <b-dropdown-item
                                v-if="row.status !== 'active'" class="do-activate"
                                @click="changeState(row, row.dt || row.status === 'blocked' ? 'activate' : 'start')"
                                :disabled="updatePending"
                              >{{ $t('pages.channels.button-activate') }}</b-dropdown-item>
                              <b-dropdown-item
                                v-if="row.status !== 'inactive'"
                                class="do-deactivate" @click="changeState(row, 'deactivate')"
                                :disabled="updatePending || hasMappings(row.id)"
                              >{{ $t('pages.channels.button-deactivate') }}</b-dropdown-item>
                              <b-dropdown-item
                                v-if="row.status === 'active'"
                                class="do-block" @click="changeState(row, 'block')"
                                :disabled="updatePending"
                              >{{ $t('pages.channels.button-block') }}</b-dropdown-item>
                            </b-dropdown>
                          </span>
                          <span class="status-date">{{ formatDate(row) }}</span>
                        </fragment>
                      </div>
                    </td>
                  </tr>
                  <tr class="separator after"></tr>
                </tbody>
              </table>
            </div>
          </div>
        </template>
      </tabs>

      <div class="panel position-relative panel-content" v-if="0">
        <div class="list d-none d-md-block">
          <p class="head-line justify-content-between">
            <span>{{ $t('pages.channels.heading') }}</span><spinner v-if="!loaded" />
            <search-filter v-model="filter" :disabled="!loaded || updatePending" v-if="0"
                           :placeholder="$t('pages.channels.filter-tip')"/>
          </p>
          <div class="plans-table" v-if="loaded">
            <table class="w-100">
              <thead>
                <tr>
                  <th class="w-name">{{ $t('pages.channels.headers.name') }}</th>
                  <th class="w-mapping text-center">{{ $t('pages.channels.headers.mapping') }}</th>
                  <th class="w-property-ids text-center">{{ $t('pages.channels.headers.ids') }}</th>
                  <!-- <th class="w-date">Date</th> -->
                  <th class="w-actions text-right">{{ $t('actions') }}</th>
                </tr>
              </thead>

              <tbody v-if="filter && !filteredChannels.length">
                <tr>
                  <td colspan="4" class="w-empty">{{ $t('pages.channels.filter-none') }}</td>
                </tr>
              </tbody>
              <tbody v-for="row in filteredChannels" :key="row.id">
                <tr class="separator before"></tr>
                <tr>
                  <td>
                    {{ row.name }}
                  </td>
                  <td class="cell-mapping text-center">
                    <b-btn v-if="row.status !== 'inactive'"
                           :variant="connectButtonVariant(row)"
                           :disabled="updatePending || !row.enabled"
                           @click="connectButtonClicked(row)"
                    >{{ connectButtonText(row) }}</b-btn>
                  </td>
                  <td class="cell-ids text-center">
                    <icon width="16" height="16" type="info"
                          v-b-tooltip:page-channels.bottom="propertyIds(row.id)"
                          v-if="row.status !== 'inactive'"
                    />
                  </td>
                  <!--
                  <td>
                    {{ formatDate(row.dt) }}
                  </td>
                  -->
                  <td class="actions text-right">
                    <div class="d-inline-block text-left">
                      <spinner v-if="updatePending && row.enabled" />
                      <fragment v-else>
                        <span class="status" :class="`status-${row.status}`">
                          <b-dropdown size="sm" toggle-tag="span" variant="link" no-caret right
                                      :disabled="!row.enabled">
                            <template #button-content>
                              {{ statusText(row.status) }}
                              <icon v-if="row.enabled" width="18" height="18" type="expand"/>
                            </template>
                            <b-dropdown-item
                              class="do-activate" @click="changeState(row, row.dt ? 'activate' : 'start')"
                              :disabled="updatePending || row.status === 'active'"
                            >{{ $t('pages.channels.button-activate') }}</b-dropdown-item>
                            <b-dropdown-item
                              class="do-deactivate" @click="changeState(row, 'deactivate')"
                              :disabled="updatePending || row.status === 'inactive' || hasMappings(row.id)"
                            >{{ $t('pages.channels.button-deactivate') }}</b-dropdown-item>
                            <b-dropdown-item
                              class="do-block" @click="changeState(row, 'block')"
                              :disabled="updatePending || row.status !== 'active'"
                            >{{ $t('pages.channels.button-block') }}</b-dropdown-item>
                          </b-dropdown>
                        </span>
                        <span class="status-date">{{ formatDate(row) }}</span>
                      </fragment>
                    </div>
                  </td>
                </tr>
                <tr class="separator after"></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import moment from 'moment';
  import {
    mapState, mapGetters, mapActions, mapMutations,
  } from 'vuex';
  import { PMSError } from '@/errors';
  import PushChannelInfoModal from '@/components/PushChannelInfoModal.vue';

  export default {
    name: 'Channels',
    components: { PushChannelInfoModal },
    data: (vm) => ({
      fromChannel: false,
      tabs: [
        { id: 'connected', title: vm.$t('pages.channels.tabs.connected') },
        { id: 'available', title: vm.$t('pages.channels.tabs.available') },
      ],
      filter: '',
      cmap: {
        selected: [],
        open: [],
      },
      register: {
        id: null,
        chid: '',
        period: {
          type: -1,
          number: '',
          unit: 'd',
          until: null,
        },
      },
    }),
    created() {
      this.$nextTick(() => {
        this.clearPageData(!this.fromChannel);
        this.fetchData(!this.fromChannel);
      });
    },
    beforeRouteEnter(to, from, next) {
      const fromChannel = from.name === 'channel';
      return next((vm) => {
        // eslint-disable-next-line no-param-reassign
        if (fromChannel) vm.fromChannel = true;
      });
    },
    computed: {
      ...mapGetters('channels', ['loaded', 'channels', 'types', 'plans', 'activeTab']),
      ...mapState('channels', ['error', 'updatePending']),
      tab: {
        get() { return this.activeTab; },
        set(tab) { this.setActiveTab(tab); },
      },
      availableChannels() {
        return this.filteredChannels.filter(({ status }) => status === 'inactive');
      },
      connectedChannels() {
        return this.filteredChannels.filter(({ status }) => status !== 'inactive');
      },
      filteredChannels() {
        const filter = this.filter.trim().toLowerCase();
        if (!filter) return this.channels;
        return this.channels
          .filter(({ id, name }) => (`${id}`.includes(filter) || name.toLowerCase().includes(filter)));
      },
    },
    methods: {
      ...mapActions('channels', ['fetchData', 'channelState', 'channelActivate']),
      ...mapMutations('channels', ['clearPageData', 'setActiveTab']),

      openRegisterModal(channel) {
        this.$refs.registerModal.show(channel.id, channel.fields);
      },
      formatDate(row) {
        return row.dt && row.enabled ? moment(row.dt).format('D MMM YYYY') : '';
      },
      propertyIds(cid) {
        const ids = this.plans
          .filter(({ marketcodes }) => marketcodes.includes(cid))
          .map(({ id }) => id);
        return ids.length ? ids.join(', ') : null;
      },
      hasMappings(cid) {
        return this.propertyIds(cid) != null;
      },
      statusText(status) {
        return this.$t(`pages.channels.status.${status}`);
      },
      isAllConnected(channel) {
        const total = this.plans.length;
        const count = parseInt(channel.count, 10);
        return !!total && total === count;
      },
      connectButtonVariant(channel) {
        return this.isAllConnected(channel) ? 'outline-primary' : 'primary';
      },
      connectButtonText(channel) {
        const total = this.plans.length;
        const count = parseInt(channel.count, 10);
        // eslint-disable-next-line no-nested-ternary
        const p = `pages.channels.connected-${!count ? 'none' : (count < total ? 'some' : 'all')}`;
        return this.$t(p, { count, total });
      },
      connectButtonClicked(channel) {
        if (!channel.enabled) return;
        this.$router.push({ name: 'channel', params: { id: channel.id } });
      },
      async changeState(channel, newMode) {
        const { id, type } = channel;
        let mode = newMode;
        if (type === 'push' && channel.status === 'inactive') {
          mode = 'start';
        }
        if (type === 'pull' || mode !== 'start') {
          if (type === 'push' && mode === 'deactivate') {
            mode = 'disconnect';
          }
          try {
            await this.channelState({ id, mode });
          } catch (e) {
            // eslint-disable-next-line no-console
            console.error(e.message);
          }
        } else {
          // open push channel register modal
          this.openRegisterModal(channel);
        }
      },
      async registerChannel({ id, values }) {
        const payload = {
          id,
          mode: 'start',
          ...JSON.parse(JSON.stringify(values)),
        };
        try {
          await this.channelActivate(payload);
          this.$refs.registerModal.hide();
        } catch (err) {
          if (err instanceof PMSError) {
            this.$toastr.e(err.message, this.$t('error'));
          }
        }
      },
    },
  };
</script>

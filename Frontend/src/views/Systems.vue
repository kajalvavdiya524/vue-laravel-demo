<template>
  <div class="page-systems">
    <div>
      <div class="panel-title position-relative w-100 title">
        <p>{{ $t('pages.systems.title-full') }}</p>
      </div>
      <div class="panel-subtitle position-relative-w-100 title">
        <p>{{ $t('pages.systems.title-sub') }}</p>
      </div>

      <tabs :items="tabs" v-model="activeTab" with-content v-if="loaded">
        <template #tab(connected-pms)>
          <div class="list connected-pms">
            <div class="tablist-none" v-if="!connectedSystems.length">
              {{  $t('pages.systems.no-connected') }}
            </div>
            <div class="plans-table" v-else>
              <table class="w-100">
                <thead>
                  <tr>
                    <th class="w-name">
                      {{ $t('pages.systems.headers.name') }}
                    </th>
                    <th class="w-version">
                      {{ $t('pages.systems.headers.version') }}
                    </th>
                    <th class="w-actions">
                      <span v-b-tooltip.hover.topleft="{ customClass: 'info-connection-tooltip' }"
                            :title="$t('pages.systems.connection-tooltip')">
                        <icon width="20" height="20" type="alert-info" color="#F7981C"/>
                      </span>
                      {{ $t('pages.systems.headers.actions') }}
                    </th>
                  </tr>
                </thead>

                <tbody v-for="(row, idx) in filteredConnectedSystems" :key="row.id">
                  <tr class="separator before"></tr>
                  <tr>
                    <td class="cell-name">
                      <a v-if="row.url" :href="row.url" target="_blank" class="pms-link">
                        <span :title="row.description">{{ row.name }}</span>
                        <icon type="new-window" width="12" height="14" color="#5A5C6C"/>
                      </a>
                      <span v-else>{{ row.name }}</span>
                    </td>
                    <td class="cell-versions">
                      <div class="d-inline-block">
                        <spinner v-if="updatePending" />
                        <fragment v-else>
                          <b-dropdown size="sm" toggle-tag="span" variant="link" no-caret left
                                      :disabled="!row.enabled">
                            <template #button-content>
                              {{ composeVersion(row.active, idx) }}
                              <icon width="18" height="18" type="expand"/>
                            </template>
                            <b-dropdown-item v-for="s in row.software" :key="s.id"
                                             :active="row.active === s.id">
                              <radio reversed
                                     :value="row.active"
                                     name="version-radios"
                                     :val="s.id"
                                     @click.native.capture.stop="changeState(row, s.id)"
                              >{{ composeVersion(s.id, idx) }}
                              </radio>
                            </b-dropdown-item>
                          </b-dropdown>
                        </fragment>
                      </div>
                    </td>
                    <td class="cell-actions">
                      <div class="d-inline-block">
                        <spinner v-if="updatePending" />
                        <fragment v-else>
                          <span class="status" :class="`status-${!!row.active ? 'active' : 'inactive'}`">
                            <b-dropdown size="sm" toggle-tag="span" variant="link" no-caret right
                                        :disabled="!row.enabled">
                              <template #button-content>
                                {{ statusText(!!row.active ? 'active' : 'inactive') }}
                                <icon v-if="row.enabled" width="18" height="18" type="expand"/>
                              </template>
                              <b-dropdown-item
                                v-if="!row.active"
                                class="do-activate" @click="changeState(row)"
                                :disabled="updatePending"
                              >{{ $t('pages.systems.button-activate') }}</b-dropdown-item>
                              <b-dropdown-item
                                v-if="!!row.active"
                                class="do-deactivate" @click="changeState()"
                                :disabled="updatePending"
                              >{{ $t('pages.systems.button-deactivate') }}</b-dropdown-item>
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
        <template #tab(available-pms)>
          <div class="list available-pms">
            <div class="d-flex justify-content-end">
              <search-filter v-model="filterAvailable" :disabled="!loaded || updatePending"
                             :placeholder="$t('pages.systems.filter-tip')"/>
            </div>
            <div class="tablist-none" v-if="!availableSystems.length">
              {{  $t('pages.systems.no-available') }}
            </div>
            <div class="plans-table" v-else>
              <table class="w-100">
                <thead>
                  <tr>
                    <th class="w-name">
                      {{ $t('pages.systems.headers.name') }}
                      <sort-indicator v-if="availableSystems.length > 1" v-model="sort" field="name" />
                    </th>
                    <th class="w-actions">
                      {{ $t('pages.systems.headers.actions') }}
                    </th>
                  </tr>
                </thead>

                <tbody v-if="filterAvailable && !availableSystems.length">
                  <tr>
                    <td colspan="2" class="w-empty">{{ $t('pages.systems.filter-none') }}</td>
                  </tr>
                </tbody>

                <tbody v-for="row in filteredAvailableSystems" :key="row.id">
                  <tr class="separator before"></tr>
                  <tr>
                    <td class="cell-name">
                      <a v-if="row.url" :href="row.url" target="_blank" class="pms-link">
                        <span>{{ row.name }}</span>
                        <icon type="new-window" width="12" height="14" color="#5A5C6C"/>
                      </a>
                      <span v-else>{{ row.name }}</span>
                    </td>
                    <td class="cell-actions available">
                      <div class="d-inline-block text-left">
                        <b-btn variant="primary"
                               pill block
                               :disabled="updatePending || !row.enabled"
                               @click="changeState(row)"
                        >
                          <spinner v-if="updatePending"/>
                          <span v-else>
                            {{ $t('pages.systems.button-activate') }}
                          </span>
                        </b-btn>
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
    </div>
  </div>
</template>

<script>
  import {
    mapState, mapGetters, mapActions,
  } from 'vuex';
  import { PMSError, ValidationError } from '@/errors';
  import moment from 'moment';
  import { pick } from '@/helpers';

  export default {
    name: 'Systems',
    data: (vm) => ({
      sort: '+id',
      tabs: [
        { id: 'connected-pms', title: vm.$t('pages.systems.tabs.connected-pms') },
        { id: 'available-pms', title: vm.$t('pages.systems.tabs.available-pms') },
      ],
      activeTab: 'connected-pms',
      filterAvailable: '',
      filterConnected: '',
    }),
    created() {
      this.fetchData();
    },
    computed: {
      ...mapGetters('systems', ['loaded', 'systems']),
      ...mapState('systems', ['error', 'updatePending']),


      connectedSystems() {
        return this.systems.filter((sys) => sys && sys?.active) || [];
      },
      availableSystems() {
        return this.systems.filter((sys) => sys && !sys?.active && sys?.enabled) || [];
      },
      filteredConnectedSystems() {
        return this.filterSystems(this.connectedSystems, this.filterConnected);
      },
      filteredAvailableSystems() {
        return this.filterSystems(this.availableSystems, this.filterAvailable);
      },
    },
    methods: {
      ...mapActions('systems', ['fetchData', 'systemState']),

      formatDate(row) {
        return row.dt && row.enabled ? moment(row.dt).format('D MMM YYYY') : '';
      },
      composeVersion(id, idx) {
        const selectedSoftware = this.connectedSystems[idx].software.find((s) => s.id === id);
        return `
          ${selectedSoftware.name}
          (${this.getCertification(selectedSoftware.certificate)})
          ID: ${id}
        `;
      },
      filterSystems(systems, filterStr) {
        const filter = filterStr.trim().toLowerCase();
        let ret = [...systems];
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
      getCertification(code) {
        let certification = null;
        switch (code) {
          case 1:
            certification = this.$t('pages.systems.certificate.availability');
            break;
          case 2:
            certification = this.$t('pages.systems.certificate.availability-rates');
            break;
          case 3:
            certification = this.$t('pages.systems.certificate.availability-rates-conditions');
            break;
          default:
            certification = '';
        }
        return certification;
      },
      statusText(status) {
        return this.$t(`pages.systems.status.${status}`);
      },
      changeState(row = null, softwareID = null) {
        let software = softwareID;

        if (!softwareID && row) {
          software = row.software[0].id;
        }

        try {
          this.systemState({ system: row?.id, software });
        } catch (err) {
          if (err instanceof ValidationError || err instanceof PMSError) {
            this.$toastr.e(err.message, this.$t('error'));
          }
        }
      },
    },
  };
</script>

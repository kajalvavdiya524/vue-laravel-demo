<template>
  <div class="page-invoices" id="page-invoices">
    <div ref="title" class="panel-title position-relative w-100 title">
      <p>{{ $t('pages.invoices.title') }}</p>
    </div>
    <Tabs
      v-model="activeTab"
      :items="tabs"
      withContent
    >
      <template #tab(unpaid)>
        <div class="list d-none d-md-block">
          <div class="no-invoices" v-if="!pending && !unpaidInvoices.length">
            {{ $t('pages.invoices.no-unpaid-invoices') }}
          </div>
          <div v-else>
            <p class="head-line justify-content-between">
              <span>{{ $t('pages.invoices.heading') }}<spinner v-if="pending" /></span>
              <search-filter
                v-model="filterUnpaid"
                :disabled="pending"
                :placeholder="$t('pages.invoices.filter-tip')"
              />
            </p>
            <div class="invoices-table">
              <b-table
                id="invoice-table-unpaid"
                ref="invoicesTableUnpaid"
                :items="filteredUnpaidInvoices"
                :fields="fieldsUnpaid"
                responsive="sm"
                v-if="!pending"
              >
                <template #head(beneficiary)>
                  {{ $t('pages.invoices.headers.beneficiary') }}
                  <sort-indicator v-model="sort" field="beneficiary" />
                </template>
                <template #head(invoice)>
                  {{ $t('pages.invoices.headers.invoice') }}
                  <sort-indicator v-model="sort" field="invoice" />
                </template>
                <template #head(produced)>
                  {{ $t('pages.invoices.headers.produced') }}
                  <sort-indicator v-model="sort" field="produced" />
                </template>
                <template #head(period_from)>
                  {{ $t('pages.invoices.headers.period-from') }}
                  <sort-indicator v-model="sort" field="period_from" />
                </template>
                <template #head(period_to)>
                  {{ $t('pages.invoices.headers.period-to') }}
                  <sort-indicator v-model="sort" field="period_to" />
                </template>
                <template #head(sum)>
                  {{ $t('pages.invoices.headers.sum') }}
                  <sort-indicator v-model="sort" field="sum" />
                </template>
                <template #cell(download)="data">
                  <a :href="data.item.download.pdf" class="download" v-if="data.item.download.pdf !== null">
                    <icon width="56" height="24" type="pdf"/>
                  </a>
                  <a :href="data.item.download.csv" class="download" v-if="data.item.download.csv !== null">
                    <icon width="56" height="24" type="csv"/>
                  </a>
                </template>
              </b-table>
            </div>
            <Pagination :total-rows="totalRows"
                        :per-page.sync="perPage"
                        :current-page.sync="currentPage"
                        v-if="!pending && totalRows >= 10"
            />
          </div>
        </div>
      </template>
      <template #tab(paid)>
        <div class="list d-none d-md-block">
          <div class="no-invoices" v-if="!pending && !paidInvoices.length">
            {{ $t('pages.invoices.no-paid-invoices') }}
          </div>
          <div v-else>
            <p class="head-line justify-content-between">
              <span>{{ $t('pages.invoices.heading') }}<spinner v-if="pending" /></span>
              <search-filter
                v-model="filterPaid"
                :disabled="pending"
                :placeholder="$t('pages.invoices.filter_tip')"
              />
            </p>
            <div class="invoices-table">
              <b-table
                id="invoice-table-paid"
                ref="invoicesTablePaid"
                :items="filteredPaidInvoices"
                :fields="fieldsPaid"
                responsive="sm"
                v-if="!pending"
              >
                <template #head(beneficiary)>
                  {{ $t('pages.invoices.headers.beneficiary') }}
                  <sort-indicator v-model="sort" field="beneficiary" />
                </template>
                <template #head(invoice)>
                  {{ $t('pages.invoices.headers.invoice') }}
                  <sort-indicator v-model="sort" field="invoice" />
                </template>
                <template #head(produced)>
                  {{ $t('pages.invoices.headers.produced') }}
                  <sort-indicator v-model="sort" field="produced" />
                </template>
                <template #head(period_from)>
                  {{ $t('pages.invoices.headers.period-from') }}
                  <sort-indicator v-model="sort" field="period_from" />
                </template>
                <template #head(period_to)>
                  {{ $t('pages.invoices.headers.period-to') }}
                  <sort-indicator v-model="sort" field="period_to" />
                </template>
                <template #head(sum)>
                  {{ $t('pages.invoices.headers.sum') }}
                  <sort-indicator v-model="sort" field="sum" />
                </template>
                <template #head(paid)>
                  {{ $t('pages.invoices.headers.paid') }}
                  <sort-indicator v-model="sort" field="paid" />
                </template>
                <template #cell(download)="data">
                  <a :href="data.item.download.pdf" class="download" v-if="data.item.download.pdf !== null">
                    <icon width="56" height="24" type="pdf"/>
                  </a>
                  <a :href="data.item.download.csv" class="download" v-if="data.item.download.csv !== null">
                    <icon width="56" height="24" type="csv"/>
                  </a>
                </template>
              </b-table>
            </div>
            <Pagination :total-rows="totalRows"
                        :per-page.sync="perPage"
                        :current-page.sync="currentPage"
                        v-if="!pending && totalRows >= 10"
            />
          </div>
        </div>
      </template>
    </Tabs>
  </div>
</template>
<script>
  import moment from 'moment';
  import { mapActions, mapState } from 'vuex';
  import { pick } from '@/helpers';

  export default {
    name: 'InvoicesData',
    data() {
      return {
        sort: '+id',
        activeTab: 'unpaid',
        filterUnpaid: '',
        filterPaid: '',
        currentPage: 1,
        perPage: 10,
        tabs: [
          { id: 'unpaid', title: this.$t('pages.invoices.tabs.unpaid') },
          { id: 'paid', title: this.$t('pages.invoices.tabs.paid') },
        ],
        fieldsUnpaid: [
          'beneficiary',
          'invoice',
          'produced',
          'period_from',
          'period_to',
          'sum',
          'download',
        ],
        fieldsPaid: [
          'beneficiary',
          'invoice',
          'produced',
          'period_from',
          'period_to',
          'sum',
          'paid',
          'download',
        ],
        currencyMap: {
          EUR: '€',
          USD: '$',
        },
      };
    },
    created() {
      const { currentPage: page, perPage: limit } = this;
      this.updateInvoices(page, limit);
    },
    watch: {
      perPage(val) {
        const page = this.currentPage <= Math.ceil(this.totalRows / val)
          ? this.currentPage
          : Math.ceil(this.totalRows / val);
        this.updateInvoices(page, val);
      },
      currentPage(val) {
        const limit = this.perPage;
        this.updateInvoices(val, limit);
      },
    },
    methods: {
      ...mapActions('invoices', ['fetchData']),

      async updateInvoices(page, limit) {
        await this.fetchData({ page, limit });
      },

      filterInvoices(invoices, filterStr) {
        const filter = filterStr.trim().toLowerCase();
        let ret = [...invoices];
        if (filter) {
          ret = ret.filter(({ invoice, beneficiary }) => (`${invoice}`?.includes(filter) || beneficiary?.toLowerCase().includes(filter)));
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
    computed: {
      ...mapState('invoices', ['error', 'pending', 'data']),

      totalRows() {
        return this.data.totalRecords;
      },

      unpaidInvoices() {
        return this.invoices.filter(({ paymentDate }) => paymentDate == null);
      },

      paidInvoices() {
        return this.invoices.filter(({ paymentDate }) => paymentDate != null);
      },

      filteredUnpaidInvoices() {
        return this.filterInvoices(this.unpaidInvoices, this.filterUnpaid);
      },

      filteredPaidInvoices() {
        return this.filterInvoices(this.paidInvoices, this.filterPaid);
      },

      invoices() {
        if (this.data) {
          return this.data.data.map((item) => ({
            beneficiary: item?.provider?.name,
            invoice: item?.invoiceName,
            produced: moment(item?.provider?.generatedDate).format(this.$t('pages.invoices.format')),
            period_from: moment(item?.periodFrom).format(this.$t('pages.invoices.format')),
            period_to: moment(item?.periodTo).format(this.$t('pages.invoices.format')),
            sum: this.$t('pages.invoices.sum-format', { currency: this.currencyMap[item?.currency], amount: item?.revenueAmount.toFixed(2) }),
            paid: item?.paymentDate,
            download: { pdf: item?.pdf, csv: item?.xlsx },
          }));
        }
        return [];
      },
    },
  };
</script>

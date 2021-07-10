<template>
  <div class="page-booking-status">
    <div class="panel-title position-relative w-100 title">
      <p>{{ $t('pages.booking.title') }}</p>
    </div>

    <booking-status-confirm-modal ref="confirmModal" @ok="changeStatus" :pending="hotelsPending" />

    <i18n path="pages.booking.heading" tag="p" class="status">
      <template #all><b>{{ $t('pages.booking.all') }}</b></template>
      <template #mode>
        <b :class="`text-${hotel.active ? 'success' : 'danger'}`">
          {{ $t(`pages.booking.${hotel.active ? 'bookable' : 'unbookable'}`) }}
        </b>
      </template>
    </i18n>
    <p class="status">
      {{ $t(`pages.booking.tip-${hotel.active ? 'on' : 'off'}`) }}
    </p>
    <switcher :checked="hotel.active" colored lazy
              :on-label="$t('pages.booking.on')" :off-label="$t('pages.booking.off')"
              @willChange="showConfirm" :disabled="hotelsPending" />

    <p class="logs-title">
      {{ $t('pages.booking.title-logs') }}
    </p>
    <div class="panel panel-content" v-if="logsLoaded">
      <div class="logs-table">
        <table class="w-100">
          <thead>
            <tr>
              <th class="w-id">{{ $t('id') }}</th>
              <th class="w-name">{{ $t('pages.booking.headers.username') }}</th>
              <th class="w-date">{{ $t('pages.booking.headers.date') }}</th>
              <th class="w-status">{{ $t('pages.booking.headers.changed-to') }}</th>
            </tr>
          </thead>
          <tbody v-if="!booking.length">
            <tr>
              <td colspan="4" class="w-empty">{{ $t('pages.booking.no-logs') }}</td>
            </tr>
          </tbody>
          <tbody v-for="log in booking" :key="`log-${log.id}`">
            <tr class="separator before"></tr>
            <tr>
              <td>{{ log.user ? log.user.id : '' }}</td>
              <td><p>{{ log.user ? log.user.profile.name : '' }}</p></td>
              <td><p>{{ createDate(log) }}</p></td>
              <td>
                <switcher :checked="log.status" disabled
                          :on-label="$t('pages.booking.on')" :off-label="$t('pages.booking.off')"/>
              </td>
            </tr>
            <tr class="separator after"></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
  import { mapActions, mapGetters, mapState } from 'vuex';
  import moment from 'moment';
  import BookingStatusConfirmModal from '@/components/BookingStatusConfirmModal.vue';

  export default {
    name: 'BookingStatus',
    components: { BookingStatusConfirmModal },
    data: () => ({
      confirm: false,
    }),
    async created() {
      this.fetchLogs({ key: 'booking', id: this.hotel.id, forced: true });
      await this.getHotel();
    },
    computed: {
      ...mapState('user', ['hotelsPending']),
      ...mapGetters('user', ['hotel']),
      ...mapGetters('logs', ['booking']),
      logsLoaded() {
        return this.booking != null;
      },
    },
    methods: {
      ...mapActions('user', ['getHotel', 'toggleHotelStatus']),
      ...mapActions('logs', ['fetchLogs']),
      createDate(row) {
        return moment(row.created_at).format(this.$t('pages.booking.headers.date-format'));
      },
      showConfirm(futureStatus) {
        this.$refs.confirmModal.show(futureStatus);
      },
      async changeStatus({ status }) {
        const { id } = this.hotel;
        try {
          await this.toggleHotelStatus({ id, active: status });
          this.$refs.confirmModal.hide();
        } catch (error) {
          this.$toastr.e(error.message, this.$t('error'));
        }
      },
    },
  };
</script>

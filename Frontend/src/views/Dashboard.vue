<template>
  <div class="page-dashboard">
    <div class="panel">
      <div class="panel-title position-relative w-100 title">
        <p>{{ $t('pages.dashboard.title') }}</p>
      </div>
      <div class="d-flex pb-4 justify-content-center justify-content-md-end">
        <b-btn variant="primary" pill class="download-btn"
               target="_blank" :href="pdfUrl" download
               :disabled="report == null">
          {{ $t('pages.dashboard.button-download') }}
          <icon width="24" height="24" type="download-icon"/>
        </b-btn>
      </div>
    </div>
  </div>
</template>

<script>
  import ApiService from '@/services/api.service';
  import { apiEndpoint } from '@/shared';

  export default {
    name: 'Dashboard',
    data: () => ({
      report: null,
    }),
    async created() {
      try {
        const { data: report } = await ApiService.get(`${apiEndpoint}/reports/recent`);
        this.report = report || null;
      } catch (err) {
        // do nothing
      }
    },
    computed: {
      pdfUrl() {
        if (this.report == null) return '';
        return this.report.pdf;
      },
    },
  };
</script>

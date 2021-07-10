<template>
  <div class="pagination">
    <div class="per-page-buttons mt-3">
      <span class="per-page">{{  $t('pages.invoices.show-per-page') }}</span>
      <b-button-group size="sm">
        <b-button @click="updatePerPage(10)" :class="{ active: perPage === 10 }">10</b-button>
        <b-button @click="updatePerPage(20)" :class="{ active: perPage === 20 }">20</b-button>
        <b-button @click="updatePerPage(50)" :class="{ active: perPage === 50 }">50</b-button>
      </b-button-group>
    </div>
    <div class="overflow-auto">
      <b-pagination
        :value="currentPage"
        :total-rows="totalRows"
        :per-page="perPage"
        aria-controls="invoice-table-unpaid"
        align="right"
        @input="updateCurrentPage"
      ></b-pagination>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'Pagination',
    props: {
      perPage: {
        type: Number,
        default: 10,
      },
      currentPage: {
        type: Number,
        default: 1,
      },
      totalRows: {
        type: Number,
        default: 0,
      },
    },
    methods: {
      updatePerPage(val) {
        this.$emit('update:per-page', val);
      },
      updateCurrentPage(val) {
        this.$emit('update:current-page', val);
      },
    },
  };
</script>

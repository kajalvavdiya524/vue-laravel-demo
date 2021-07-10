<template>
  <b-modal
    id="pushChannelInfoModal"
    ref="modal"
    no-fade
    centered
    static
    size="lg"
    modal-class="form-modal"
    :ok-title="okTitle"
    ok-variant="primary"
    :cancel-title="$t('buttons.cancel')"
    cancel-variant="outline-primary"
    :ok-disabled="pending || !formValid"
    :cancel-disabled="pending"
    :no-close-on-esc="pending"
    :no-close-on-backdrop="pending"
    :hide-header-close="pending"
    @ok.prevent="emit"
  >
    <template #modal-header-close>
      <icon width="20" height="20" class="d-none d-md-block" type="times"/>
      <icon width="10" height="18" class="d-md-none" type="arrow-left"/>
    </template>
    <template #modal-title>
      {{ modalTitle }}
    </template>
    <ValidationObserver ref="form" slim>
      <div class="edge" :class="{ active: edgeTransmission }">
        <h3 :content="$t('pages.channels.connect.period')"></h3>
        <div class="row row-edit">
          <div class="col cell-edit-label double">
            <radio v-model="values.period.type" :disabled="pending" name="trans_period" :val="0">
              {{ $t('pages.channels.connect.transmission-period') }}
            </radio>
          </div>
          <div class="col cell-edit-value cell-trans-number">
            <ValidatedField type="number" integer purenumber no-icon no-tooltip
                            name="tp-time" class="cp-time" min="0" max="99" group-class="mb-0"
                            v-model="values.period.number"
                            :disabled="pending || values.period.type !== 0"
                            :rules="rulesFor('periodNumber')"/>
          </div>
          <div class="col cell-edit-value cell-trans-unit">
            <drop-down
              id="dd-period-unit"
              v-model="values.period.unit"
              :items="timeUnits"
              rules="required"
              :disabled="pending || isZeroUnit || values.period.type !== 0"
            />
          </div>
        </div>
        <div class="row row-edit">
          <div class="col cell-edit-label double">
            <radio v-model="values.period.type" :disabled="pending" name="trans_end" :val="1">
              {{ $t('pages.channels.connect.transmission-end-date') }}
            </radio>
          </div>
          <div class="col cell-edit-field cell-trans-until">
            <date-picker id="period-end" v-model="values.period.until" min-today placeholder=" "
                         :disabled="pending || values.period.type !== 1"/>
          </div>
        </div>
      </div>
      <div class="edge" :class="{ active: edgeData }">
        <form>
          <h3 :content="$t('pages.channels.connect.property-data')"></h3>
          <div class="row row-edit" v-for="field in fields" :key="field.key">
            <div class="col cell-edit-label double">
              {{ field.title }}
            </div>
            <div class="col cell-edit-value cell-channel-field">
              <ValidatedField :type="typeFor(field.subtype)" no-icon no-tooltip
                              :name="field.key" group-class="mb-0"
                              v-model="values[field.key]"
                              :disabled="pending"
                              :rules="rulesFor(field.subtype)"/>
            </div>
          </div>
        </form>
      </div>
    </ValidationObserver>
  </b-modal>
</template>

<script>
  import moment from 'moment';

  export default {
    name: 'PushChannelInfoModal',
    props: {
      pending: Boolean,
    },
    data: () => ({
      id: null,
      edit: false,
      values: {
        period: {},
      },
      fields: null,
    }),
    computed: {
      okTitle() {
        return this.$t(`buttons.${this.edit ? 'update' : 'save'}`);
      },
      modalTitle() {
        return this.$t(`pages.channels.title-${this.edit ? 'update' : 'activate'}`);
      },
      timeUnits() {
        return ['d', 'w', 'm', 'y'].map((id) => ({
          id,
          text: this.$tc(`pages.channels.connect.time-units.${id}`, this.values.period.number),
        }));
      },
      isZeroUnit() {
        const n = parseInt(this.values.period.number, 10);
        // eslint-disable-next-line no-restricted-globals
        return n === 0 || isNaN(n);
      },
      edgeTransmission() {
        if (this.values == null) return false;
        const { values: { period: { type, number, until } } } = this;
        if (type === 0) {
          return parseInt(number, 10) > 0;
        }
        if (type === 1) {
          return until != null;
        }
        return false;
      },
      edgeData() {
        if (this.values == null || this.fields == null) return false;
        return this.fields.every(({ key }) => (this.values[key] != null && this.values[key] !== ''));
      },
      formValid() {
        return this.edgeData && this.edgeTransmission && this.$refs.form.flags.valid;
      },
    },
    methods: {
      show(id, fields, values = null) {
        this.edit = values != null;
        this.id = id;
        this.fields = fields;
        const v = { ...values };
        if (v.period == null) {
          v.period = {
            type: -1,
            number: '',
            unit: 'd',
            until: null,
          };
        }
        this.values = v;
        this.$nextTick(this.$refs.modal.show);
      },
      hide() {
        this.$refs.modal.hide();
      },
      rulesFor(field) {
        const rules = {
          // required: true,
        };
        switch (field) {
          case 'periodNumber':
            rules.between = { min: 0, max: 99 };
            rules.numeric = true;
            rules.required = this.values.period.type === 0;
            break;
          default:
            rules.required = true;
            break;
        }
        return rules;
      },
      typeFor(subtype) {
        switch (subtype) {
          case 'text':
          case 'password':
            return subtype;
          default:
            break;
        }
        return '';
      },
      emit() {
        const { id, values } = this;
        if (moment.isMoment(values.period.until)) {
          values.period.until = values.period.until.format('YYYY-MM-DD');
        }
        this.$emit('ok', { id, values });
      },
    },
  };
</script>

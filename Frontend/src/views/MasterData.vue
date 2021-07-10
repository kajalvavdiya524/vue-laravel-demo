<template>
  <div class="page-masterdata">
    <div class="">
      <div ref="title" class="panel-title position-relative w-100 title">
        <p>{{ $t('pages.masterdata.title') }}</p>
      </div>
      <div id="tabs">
        <div class="tabs" @click="initProperty">
          <a v-for="(tab, idx) in tabs" :key="`tab-${idx}`" @click="setTab(idx)"
             :class="{ active: activetab === idx }">
            {{ $t(tab) }}
          </a>
        </div>
        <div class="">
          <div v-show="activetab === 0" class="tab-content">
            <ValidationObserver ref="form0">
              <form @submit.prevent="onSubmit">
                <div class="row">
                  <div class="col-12">
                    <ImageSelector v-model="logo" />
                  </div>
                </div>
                <div class="row">
                  <p class="col-12 head-line">
                    {{ $t('pages.masterdata.property-name') }}<span class="required">*</span>
                  </p>
                </div>
                <div class="row">
                  <div class="col-md-6 col-sm">
                    <ValidatedField rules="required_string|max:100" :disabled="pending"
                                    type="text" name="name" :placeholder="$t('pages.masterdata.property-name')"
                                    v-model="property.name"
                    />
                  </div>
                </div>
                <div class="row">
                  <p class="col-12 head-line">{{ $t('pages.masterdata.capacity') }}<span class="required">*</span></p>
                </div>
                <div class="row">
                  <div class="col-md-2 col-sm  input-padding-right capacity">
                    <ValidatedField rules="required|between:1,999" :disabled="pending" autocomplete="no"
                                    type="number" name="capacity" min="1" max="999"
                                    v-model="property.capacity"
                    />
                  </div>

                  <div class="col cell-qu-edit-fields">
                    <radio v-model="property.capacity_mode" :val="0" :disabled="pending"
                           name="capacity_mode">{{ $t('pages.masterdata.capacity-rooms') }}</radio>
                    <radio v-model="property.capacity_mode" :val="1" :disabled="pending"
                           name="capacity_mode">{{ $t('pages.masterdata.capacity-beds') }}</radio>
                  </div>
                </div>
                <div class="row ">
                  <p class="col-12 head-line">
                    {{ $t('pages.masterdata.property-tel') }}<span class="required">*</span>
                  </p>
                </div>
                <div class="row">
                  <div class="col-md-3 col-sm">
                    <ValidatedField group-class="" name="tel" type="tel" national-mode
                                    autocomplete="tel" v-model.trim="property.tel" :disabled="pending"
                                    rules="required|max:20" :error-bag="validationError"/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-5 col-sm input-padding-right">
                    <p class="head-line ">{{ $t('pages.masterdata.primary-email') }}<span class="required">*</span></p>
                    <ValidatedField rules="required|email" placeholder="name@company.com"
                                    type="email" name="email" v-model.trim="property.email"
                                    autocomplete="email" class="mb-2" :error-bag="validationError" :disabled="pending"/>
                    <p class="forMail">{{ $t('pages.masterdata.primary-email-tip') }}</p>
                  </div>
                  <!-- <div class="col-md-3 col-sm input-padding-left">
                    <p class="head-line">{{ $t('pages.masterdata.alt-email') }}</p>
                    <ValidatedField rules="required|email" placeholder="name@company.com"
                                    type="email" name="alternaitve_email"
                                    v-model.trim="property.alternative_email" autocomplete="email"
                                    class="mb-2" :error-bag="validationError" :disabled="pending"/>
                    <p class="forMail">{{ $t('pages.masterdata.alt-email-tip') }}</p>
                  </div> -->
                </div>
                <div class="row">
                  <p class="col-12 head-line">{{ $t('pages.masterdata.website') }}<span class="required">*</span></p>
                </div>
                <div class="row">
                  <div class="col-md-6 col-sm">
                    <ValidatedField rules="required_string|url" :disabled="pending"
                                    type="text" name="website_name" :placeholder="$t('pages.masterdata.website')"
                                    v-model="property.website"
                    />
                  </div>
                </div>
                <div class="row">
                  <div class="col-12 ">
                    <p class="head-line">{{ $t('pages.masterdata.choose-currency') }}</p>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-3 col-12 ">
                    <drop-down
                      id="currency"
                      :items="allCurrencies"
                      :disabled="pending"
                      v-model="property.currency_id"
                    />
                  </div>
                </div>
              </form>
            </ValidationObserver>
          </div>
          <div v-show="activetab === 1" class="tab-content">
            <ValidationObserver ref="form1">
              <form @submit.prevent="onSubmit">
                <div class="row">
                  <p class="col-12 head-line">{{ $t('addr.street') }}<span class="required">*</span></p>
                </div>
                <div class="row">
                  <div class="col-md-6 col-sm">
                    <ValidatedField rules="required_string|max:100" :disabled="pending"
                                    type="text" name="street_no" :placeholder="$t('addr.street')"
                                    v-model="address.street"
                    />
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 col-sm">
                    <ValidatedField rules="max:100" :disabled="pending"
                                    type="text" name="second_address" :placeholder="$t('addr.street')"
                                    v-model="address.street_optional"
                    />
                  </div>
                </div>
                <div class="row">
                  <div class="col-12">
                    <div class="row">
                      <div class="col-md-3 col-sm  input-padding-right">
                        <p class="head-line ">{{ $t('addr.zip') }}<span class="required">*</span></p>
                        <ValidatedField rules="required|numeric" :disabled="pending"
                                        type="number" purenumber integer
                                        name="post_code" :placeholder="$t('addr.zip')"
                                        v-model="address.zip"
                        />
                      </div>
                      <div class="col-md-3 col-sm input-padding-left">
                        <p class="head-line">{{ $t('addr.city') }}<span class="required">*</span></p>
                        <ValidatedField rules="required_string|max:100" :disabled="pending"
                                        type="text" name="city" :placeholder="$t('addr.city')"
                                        v-model="address.city"
                        />
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-12">
                    <div class="row">
                      <!-- <div class="col-md-3 col-sm input-padding-right">
                        <p class="head-line ">{{ $t('addr.state') }}</p>
                        <ValidatedField rules="required_string|max:100" :disabled="pending"
                                        type="text" name="federal_state" no-icon no-tooltip
                                        :placeholder="$t('addr.state')"
                                        v-model="address.federal_state"
                        />
                      </div> -->
                      <div class="col-md-3 col-sm input-padding-left">
                        <p class="head-line">{{ $t('addr.country') }}<span class="required">*</span></p>
                        <ValidatedField name="country" v-model="address.country" type="select" label-by="name"
                                        track-by="code" :placeholder="$t('addr.country')" autocomplete="country-name"
                                        :options="countries" searchable :disabled="pending || countries==null"
                                        rules="required" :error-bag="validationError"/>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <p class="col-12 head-line">{{ $t('pages.masterdata.geodata') }}</p>
                </div>
                <div class="row">
                  <div class="col-md-3 col-sm input-padding-right">
                    <ValidatedField rules="between:-90,90" placeholder="-"
                                    type="text" name="latitude" no-icon no-tooltip class="mb-2"
                                    v-model.trim="address.latitude" :disabled="pending"
                    />
                    <p class="forMail">{{ $t('pages.masterdata.latitude') }}</p>
                  </div>
                  <div class="col-md-3 col-sm input-padding-left">
                    <ValidatedField rules="between:-180,180"
                                    type="text" name="longitude" no-icon no-tooltip class="mb-2"
                                    placeholder="-" :disabled="pending"
                                    v-model.trim="address.longitude"
                    />
                    <p class="forMail">{{ $t('pages.masterdata.longitude') }}</p>
                  </div>
                  <div class="col-md-2 col-sm">
                    <b-button link block variant="primary"
                              :disabled="invalidGeoData"
                              target="_blank"
                              :href="locationUrl">
                      {{ $t('pages.masterdata.button-show-location') }}
                    </b-button>
                  </div>
                </div>
                <div class="row">
                  <span class=" col-12 forMail">{{ $t('pages.masterdata.geodata-tip') }}
                    <a href="https://www.revilodesign.de/tools/google-maps-latitude-longitude-finder/"
                       target="_blank">revilodesign.de</a></span>
                </div>
              </form>
            </ValidationObserver>
          </div>
          <div v-show="activetab === 2" class="tab-content">
            <ValidationObserver ref="form2">
              <form @submit.prevent="onSubmit">
                <div class="row">
                  <p class="col-12 head-line">Booking.com</p>
                </div>
                <div class="row">
                  <div class="col-md-3 col-sm">
                    <ValidatedField rules="max:100"
                                    type="number" name="booking.com"  no-icon no-tooltip
                                    v-model="bookingNo" :disabled="pending"
                    />
                  </div>
                </div>
                <div class="row">
                  <p class="col-12 head-line">Expedia</p>
                </div>
                <div class="row">
                  <div class="col-md-3 col-sm">
                    <ValidatedField rules="max:100"
                                    type="number" name="expedia" no-icon no-tooltip
                                    v-model="expedia" :disabled="pending"
                    />
                  </div>
                </div>
                <div class="row">
                  <p class="col-12 head-line">HRS</p>
                </div>
                <div class="row">
                  <div class="col-md-3 col-sm">
                    <ValidatedField rules="max:100"
                                    type="number" name="hrs" no-icon no-tooltip
                                    v-model="hrs" class="mb-0"
                    />
                  </div>
                </div>
              </form>
            </ValidationObserver>
          </div>
          <div v-show="activetab === 3" class="tab-content">
            <ValidationObserver ref="form3">
              <form @submit.prevent="onSubmit">
                <div class="position-relative panel-content">
                  <div class="d-none d-md-block">
                    <div class="room-table">
                      <table class="w-100">
                        <thead>
                          <tr>
                            <th class="w-name">{{ $t('pages.masterdata.certs.name') }}</th>
                            <th class="w-upload-date">{{ $t('pages.masterdata.certs.upload-date') }}</th>
                            <th class="w-stars">{{ $t('pages.masterdata.certs.stars') }}</th>
                            <th class="text-right">{{ $t('pages.masterdata.certs.issued-by') }}</th>
                          </tr>
                        </thead>

                        <tbody v-for="(row, idx) in certificates" :key="idx">
                          <tr class="separator before"></tr>
                          <tr>
                            <td>
                              <p>{{row.name}}</p>
                            </td>
                            <td>
                              <p>{{row.uploadDate}}</p>
                            </td>
                            <td>
                              <b-form-rating :id="`rating-inline-${idx}`" :value='row.value'></b-form-rating>
                            </td>
                            <td class="text-right nr">
                              <p>{{row.issuedBy}}</p>
                            </td>
                          </tr>
                          <tr class="separator after"></tr>
                        </tbody>
                      </table>
                    </div>
                    <div class="d-xl-flex">
                      <b-btn pill variant="outline-primary" class="add-certificate">
                        <icon width="10" height="10" type="plus"/>
                        {{ $t('pages.masterdata.button-add-certificate') }}
                      </b-btn>
                    </div>
                  </div>

                  <div class="d-md-none" v-for="(row, idx) in certificates" :key="idx">
                    <div class="d-flex dots-wrap">
                      <div class="dots">
                        <b-dropdown size="sm" toggle-tag="span" variant="link" no-caret right :disabled="pending">
                          <template #button-content>
                            <icon width="20" height="19" class="label" type="dots-h"/>
                          </template>
                        </b-dropdown>
                      </div>
                    </div>
                    <div class="d-flex">
                      <table class="w-100">
                        <tbody>
                          <tr class="tr-wrap">
                            <td class="tb-title"><p>{{ $t('pages.masterdata.certs.name') }}</p></td>
                            <td class="tb-content"><p>{{row.id}}</p></td>
                          </tr>
                          <tr class="tr-wrap">
                            <td class="tb-title"><p>{{ $t('pages.masterdata.certs.upload-date') }}</p></td>
                            <td class="tb-content"><p>12 Jan 2020</p></td>
                          </tr>
                          <tr class="tr-wrap">
                            <td class="tb-title"><p>{{ $t('pages.masterdata.certs.stars') }}</p></td>
                            <td class="tb-content">
                              <b-form-rating :id="`rating-inline-sm-${idx}`" :value="row.value"></b-form-rating>
                            </td>
                          </tr>
                          <tr class="tr-wrap">
                            <td class="tb-title"><p>{{ $t('pages.masterdata.certs.issued-by') }}</p></td>
                            <td class="tb-content"><p>12 Jan 2020</p></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="add-certificate-sm d-md-none">
                    <b-btn pill variant="outline-primary" class="add-certificate col-12">
                      <icon width="10" height="10" type="plus"/>
                      {{ $t('pages.masterdata.button-add-certificate') }}
                    </b-btn>
                  </div>
                </div>
              </form>
            </ValidationObserver>
          </div>
        </div>
      </div>
      <div class="panel-footer" v-if="activetab !== 3">
        <div class="col-md-3 col-12 align-self-end cell-button">
          <SubmitButton type="button" :disabled="formInvalid" v-if="activetab >= 0"
                        :loading="pending" @click="onSubmit"
          >{{ $t('buttons.save') }}</SubmitButton>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  import { mapActions, mapGetters, mapState } from 'vuex';

  export default {
    name: 'MasterData',
    data() {
      return {
        activetab: -1,
        tabs: [
          'pages.masterdata.tabs.property',
          'pages.masterdata.tabs.address',
          'pages.masterdata.tabs.ids',
          'pages.masterdata.tabs.certs',
        ],
        property: {},
        address: {},
        hotelData: null,
        logo: null,
        bookingNo: '',
        expedia: '',
        hrs: '',
        certificates: {
          0: {
            name: '3 days before arrival',
            uploadDate: '12 Jan 2020',
            value: 4,
            issuedBy: '2 Jan 2020',
          },
          1: {
            name: '3 days before arrival',
            uploadDate: '12 Jan 2020',
            value: 5,
            issuedBy: '2 Jan 2020',
          },
        },
      };
    },
    watch: {
      user: function watchUser(userData) {
        if (userData) {
          this.updateUserData();
        }
      },
    },
    async created() {
      // await this.getUser();
      await Promise.allSettled([
        this.fetchCountries(),
        this.fetchCurrencies(),
        this.getDescription(),
        this.getHotel().then((data) => {
          this.hotelData = data;
          return true;
        }),
      ]);
      this.updateUserData();
      this.activetab = 0;
    },
    computed: {
      ...mapGetters('data', ['countries', 'currencies']),
      ...mapGetters('user', ['user', 'pending', 'validationError', 'hotel']),
      ...mapState('user', ['hotelsPending']),
      ...mapState('description', ['descriptions']),
      allCurrencies() {
        return this.currencies.map(({ id, name, code }) => ({ id, text: `${name} (${code})` }));
      },
      invalidGeoData() {
        const { latitude, longitude } = this.address;
        if (latitude == null || longitude == null) return true;
        return !latitude.length || !longitude.length
          || !(Math.abs(longitude) <= 180)
          || !(Math.abs(latitude) <= 90);
      },
      locationUrl() {
        if (this.invalidGeoData) return '';
        // return `https://www.google.com/maps/@${this.address.latitude},${this.address.longitude},15z`;
        return `https://www.google.com/maps/place/${this.address.latitude},${this.address.longitude}`;
      },
      activeForm() {
        return this.$refs[`form${this.activetab}`];
      },
      formInvalid() {
        const form = this.activeForm;
        return form != null ? form.flags.invalid : true;
      },
    },
    methods: {
      ...mapActions('data', ['fetchCountries', 'fetchCurrencies']),
      ...mapActions('user', ['getUser', 'getHotel', 'updateHotel']),
      ...mapActions('description', ['getDescription']),
      setTab(tab) {
        switch (this.activetab) {
          case 0:
            this.resetPropertyForm();
            break;
          case 1:
            this.resetAddressForm();
            break;
          default:
            break;
        }
        this.activetab = tab;
      },
      async onSubmit() {
        if (this.activeForm.flags.invalid) return;
        try {
          if (this.activetab === 0) {
            const { upload, remove } = this.logo;
            await this.updateHotel({
              partial: true,
              ...this.property,
              descriptions: this.descriptions,
              logo: { upload, remove },
            });
          }
          if (this.activetab === 1) {
            await this.updateHotel({
              partial: true,
              descriptions: this.descriptions,
              ...this.address,
            });
          }
          this.activeForm.reset();
          this.$toastr.s({
            title: this.$t('pages.masterdata.alert-saved'),
            msg: '',
            timeout: 3000,
            progressbar: false,
          });
        } catch (error) {
          this.$toastr.e(error.message, this.$t('error'));
        }
        // this.$refs.title.scrollIntoView();
      },
      resetPropertyForm(resetForm = true) {
        const {
          // eslint-disable-next-line camelcase
          email, name, tel, alternative_email, website, capacity, capacity_mode: cmode, currency_id,
        } = JSON.parse(JSON.stringify(this.hotelData));
        // eslint-disable-next-line camelcase
        const capacity_mode = cmode != null ? cmode : 0;
        this.property = {
          email, name, tel, alternative_email, website, capacity, capacity_mode, currency_id,
        };
        this.logo = {
          original: this.hotel.logo || null,
          upload: null,
          remove: false,
        };
        if (resetForm) {
          this.$nextTick(() => {
            this.$refs.form0.reset();
          });
        }
      },
      resetAddressForm(resetForm = true) {
        const {
          // eslint-disable-next-line camelcase
          street, street_optional, zip, city, federal_state, country, latitude, longitude,
        } = JSON.parse(JSON.stringify(this.hotelData));
        this.address = {
          street, street_optional, zip, city, federal_state, country, latitude, longitude,
        };
        if (resetForm) {
          this.$nextTick(() => {
            this.$refs.form1.reset();
          });
        }
      },
      updateUserData() {
        this.resetPropertyForm();
        this.resetAddressForm();
      },

      initProperty() {
        // this.property = {};
        // this.address = {};
        // this.profile = {};
      },
    },
  };
</script>

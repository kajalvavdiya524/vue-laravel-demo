<template>
  <div class="page-descriptions">
    <div class="page-cxl-pol">
      <div ref="title" class="panel-title position-relative w-100 title">
        <p>{{ $t('pages.description.title') }}</p>
      </div>
    </div>
    <ValidationObserver ref="form">
      <div class="component-descriptions">
        <div class="descriptions-wrapper d-none d-md-block mb-10"
             v-for="(item,index) in listOfDescription" :key="index">
          <div class="panel position-relative panel-content">
            <!--Description Page (desktop)-->
            <div class="list d-none d-md-block left-edge">
              <div :class="{'active-text':item.isOpen}"
                   class="cursor" @click="item.isOpen =!item.isOpen">
                {{item.title}}
                <icon width="12" height="7" :class="{active:item.isOpen}"
                      class="icon-angle" stroke-width="2" type="arrow-down"/>
              </div>
              <div class="field-wrapper mt-20" v-show="item.isOpen">
                <lang-selector v-model="lang" :valid="langValid" />
                <div class="lang-choice" v-for="code in langCodes" :key="code" v-show="lang === code">
                  <ValidatedField
                    type="textarea"
                    v-model="item.lang[code]"
                    :rows="rowLength"
                    :name="`description_${index}_${code}`"
                    :id="`description-${index}-${code}`"
                    :placeholder="$t('pages.description.title')"
                    @input="toggleLangValid($event, code)"
                    rules="max:1000"
                    :disabled="pending"
                  />
                </div>
                <SubmitButton
                  @click="onSubmit"
                  variant="secondary"
                  class="btn-save"
                  :disabled="pending && !loaded"
                  :loading="pending"
                  inline
                >
                  {{ $t('buttons.save') }}
                </SubmitButton>
              </div>
            </div>
          <!--./end of Descriptions (desktop)-->
          </div>
        </div>
        <!-- Descriptions (mobile) -->
        <div class="descriptions-wrapper" v-for="(item, index) in listOfDescription" :key="'text' + index">
          <div :class="[{'reset-shadow' : index === 0}, 'list-item head-line d-md-none page-description' ]">
            <div :class="{'active-text':item.isOpen}" class="cursor" @click="item.isOpen =!item.isOpen">
              {{item.title}}
              <icon width="12" height="7" :class="{active:item.isOpen}"
                    class="icon-angle" stroke-width="2" type="arrow-down"/>
            </div>
            <div class="field-wrapper mt-20" v-show="item.isOpen">
              <lang-selector v-model="lang" :valid="langValid" />
              <div class="lang-choice" v-for="code in langCodes" :key="code" v-show="lang === code">
                <ValidatedField
                  type="textarea"
                  v-model="item.lang[code]"
                  :rows="rowLength"
                  :name="`description-${index}-${code}`"
                  :id="`description-${index}-${code}`"
                  :placeholder="$t('pages.description.title')"
                  @input="toggleLangValid($event, code)"
                  rules="max:1000"
                  :disabled="pending"
                />
              </div>
              <SubmitButton
                @click="onSubmit"
                variant="secondary"
                class="btn-save"
                :disabled="pending && !loaded"
                :loading="pending"
                inline
              >
                {{ $t('buttons.save') }}
              </SubmitButton>
            </div>
          </div>
        </div>
      </div>
    </ValidationObserver>
  </div>
</template>

<script>
  import { langCodes } from '@/shared';
  import { mapState, mapActions, mapGetters } from 'vuex';

  export default {
    name: 'Descriptions',
    data: (vm) => ({
      langValid: [],
      lang: 'en',
      rowLength: 15,
      listOfDescription: {
        description_long: {
          title: vm.$t('pages.description.long-version'),
          lang: {},
          isOpen: false,
        },
        description_short: {
          title: vm.$t('pages.description.short-version'),
          lang: {},
          isOpen: false,
        },
        liability: {
          title: vm.$t('pages.description.liability-notice'),
          lang: {},
          isOpen: false,
        },
        location: {
          title: vm.$t('pages.description.location'),
          lang: {},
          isOpen: false,
        },
        directions: {
          title: vm.$t('pages.description.directions'),
          lang: {},
          isOpen: false,
        },
        insider_tips: {
          title: vm.$t('pages.description.insider-tips'),
          lang: {},
          isOpen: false,
        },
      },
    }),

    async created() {
      await Promise.allSettled([
        this.getDescription().then((data) => {
          this.listOfDescription.description_long.lang = data.description_long.lang || {};
          this.listOfDescription.description_short.lang = data.description_short.lang || {};
          this.listOfDescription.liability.lang = data.liability.lang || {};
          this.listOfDescription.location.lang = data.location.lang || {};
          this.listOfDescription.directions.lang = data.directions.lang || {};
          this.listOfDescription.insider_tips.lang = data.insider_tips.lang || {};
          return true;
        }),
      ]);
    },

    computed: {
      ...mapState('description', ['pending']),
      ...mapGetters('description', ['loaded']),
      langCodes: () => langCodes,
    },

    methods: {
      ...mapActions('description', ['updateDescription', 'getDescription']),
      toggleLangValid(val, code) {
        const idx = this.langValid.indexOf(code);
        if (`${val}`.trim()) {
          if (idx === -1) {
            this.langValid.push(code);
          }
        } else if (idx !== -1) {
          this.langValid.splice(idx, 1);
        }
      },
      async onSubmit() {
        this.$refs.form.validate();
        if (this.$refs.form.flags.invalid) return;
        try {
          await this.updateDescription({ descriptions: this.listOfDescription });
          this.$toastr.s({
            title: this.$t('pages.masterdata.alert-saved'),
            msg: '',
            timeout: 3000,
            progressbar: false,
          });
        } catch (error) {
          this.$toastr.e(error.message, this.$t('error'));
        }
      },
    },
  };

</script>

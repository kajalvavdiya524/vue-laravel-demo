<template>
  <div class="component-rateplans">
    <b-modal
      id="planModal"
      ref="planModal"
      no-fade
      centered
      static
      size="lg"
      modal-class="form-modal"
      :ok-title="$t(`buttons.${plan.id != null ? 'update' : 'save'}`)"
      ok-variant="primary"
      :cancel-title="$t('buttons.cancel')"
      cancel-variant="outline-primary"
      :ok-disabled="updatePending || !$refs.planForm || !formValid || !$refs.planForm.flags.valid"
      :cancel-disabled="updatePending"
      :no-close-on-esc="updatePending"
      :no-close-on-backdrop="updatePending"
      :hide-header-close="updatePending"
      @show="modalScroll"
      @hidden="modalScroll"
      @ok.prevent="processForm"
    >
      <template #modal-header-close>
        <icon width="20" height="20" class="d-none d-md-block" type="times"/>
        <icon width="10" height="18" class="d-md-none" type="arrow-left"/>
      </template>
      <template #modal-title>
        {{ modalTitle }}
      </template>
      <ValidationObserver ref="planForm" slim>
        <div class="edge" :class="{ active: edgeRoomLang }">
          <h3 :content="$t('pages.rateplans.modal.section-connect')"></h3>
          <lang-selector v-model="lang" :valid="langsValid" ref="langSel" />
          <div class="link-figure">
            <icon width="22" height="22" class="link-figure-icon" type="link"/>
            <h5>{{ $t('pages.rateplans.modal.section-plan-name') }}</h5>
            <div class="lang-choice" v-for="code in langCodes" :key="code" v-show="lang === code">
              <ValidatedField type="text" :name="`lang-${code}`" class="plan-name" no-icon
                              v-model="plan.langs[code].name" :disabled="updatePending"
                              @input="toggleLangValid($event, code)"
                              :rules="rulesForLang(code, 'name')"/>
              <ValidatedField type="textarea" class="plan-desc" :placeholder="$t('pages.rateplans.modal.description')"
                              no-icon :rules="rulesForLang(code, 'desc')" :name="`lang-${code}-desc`"
                              v-model="plan.langs[code].desc" :disabled="updatePending"/>
            </div>
            <h5>{{ $t('pages.rateplans.modal.section-room-type') }}</h5>
          </div>
          <drop-down
            id="dd-rooms"
            v-model="plan.room"
            :items="rooms"
            id-key="pid"
            @input="updateRoomData(false)"
            :disabled="updatePending || plan.protected"
          />
        </div>
        <div class="edge edge-settings">
          <h4 :class="{ opened: settingsOpened }" @click="settingsOpened=!settingsOpened">
            {{ $t('pages.rateplans.modal.section-settings') }}
            <icon width="12" height="7" stroke-width="2" type="arrow-down"/>
          </h4>
          <div>
            <h3 :content="$t('pages.rateplans.modal.travel-days')"></h3>
            <div class="row row-travel d-none d-md-flex">
              <div class="col cell-travel">
                <div class="cell-travel-title">
                  <p v-for="key in travelOpts" :key="`tt-${key}`"
                     v-text="key?$t(`pages.rateplans.modal.travel-opts.${key}`):''"></p>
                </div>
                <div class="cell-travel-alls">
                  <p v-for="key in travelOpts" :key="`ta-${key}`">
                    <template v-if="!key">{{ $t('pages.rateplans.modal.days-all') }}</template>
                    <check-box v-else empty :disabled="updatePending"
                               :value="isTravelAll(key)" @input="toggleTravelAll(key, $event)" />
                  </p>
                </div>
                <div class="cell-travel-wd" v-for="(wd, idx) in weekdays" :key="`twd-${wd}`">
                  <p v-for="key in travelOpts" :key="`td-${key}`">
                    <template v-if="!key">{{ $t('weekdays')[idx] }}</template>
                    <check-box v-else empty :disabled="updatePending"
                               :value="isTravelDay(key, wd)" @input="toggleTravelDay(key, wd, $event)" />
                  </p>
                </div>
              </div>
            </div>
            <div class="row row-travel d-md-none" v-for="key in travelOptsFiltered" :key="`tt-sm-${key}`">
              <div class="col-12 cell-travel-title">
                <h5>{{ $t(`pages.rateplans.modal.travel-opts.${key}`) }}</h5>
              </div>
              <div class="col">
                <div class="d-flex cell-travel">
                  <div class="wd-item wd-all">
                    <check-box :disabled="updatePending" :value="isTravelAll(key)"
                               @input="toggleTravelAll(key, $event)"
                    >{{ $t('pages.rateplans.modal.days-all') }}</check-box>
                  </div>
                  <div class="wd-item" v-for="(wd, idx) in weekdays" :key="`ttwd-sm-${key}-${wd}`">
                    <check-box :disabled="updatePending" :value="isTravelDay(key, wd)"
                               @input="toggleTravelDay(key, wd, $event)"
                    >{{ $t('weekdays')[idx] }}</check-box>
                  </div>
                </div>
              </div>
            </div>
            <h3 :content="$t('pages.rateplans.modal.los')"></h3>
            <div class="row row-settings">
              <div class="col cell-minlos">
                <label class="text-xs" for="minlos">{{ $t('pages.rateplans.modal.minimum') }}</label>
                <ValidatedField
                  type="number" id="minlos" name="minlos" min="1" max="999" class="mb-0" no-icon no-tooltip
                  v-model="plan.minlos" :disabled="updatePending"
                  :rules="rulesFor('minlos')"
                />
              </div>
              <div class="col cell-maxlos">
                <label class="text-xs" for="maxlos">{{ $t('pages.rateplans.modal.maximum') }}</label>
                <ValidatedField
                  type="number" id="maxlos" name="maxlos" min="1" max="999" class="mb-0" no-icon no-tooltip
                  v-model="plan.maxlos" :disabled="updatePending"
                  :rules="rulesFor('maxlos')"
                />
              </div>
            </div>
            <h3 :content="$t('pages.rateplans.modal.meal-type')"></h3>
            <div class="row row-settings">
              <div class="col cell-meals">
                <drop-down
                  id="dd-meals"
                  v-model="plan.meals"
                  :items="meals"
                  :disabled="updatePending"
                />
              </div>
            </div>
            <h3 :content="$t('pages.rateplans.modal.cancel-policies')"></h3>
            <div class="row row-settings">
              <div class="col cell-cancels">
                <drop-down
                  id="dd-cancels"
                  select-all
                  multiple
                  title-all="pages.rateplans.modal.cancel-all"
                  title="pages.rateplans.modal.cancel-some"
                  v-model="plan.cancels"
                  :items="cancelsFlat"
                  :disabled="updatePending"
                />
                <p class="data-desc oneline" v-for="{ id, langs } in selectedCancels" :key="`cpd-${id}`">
                  <button class="btn-icon btn-tiny" @click="removeCancelPolicy(id)">
                    <icon width="7" height="7" type="times"/>
                  </button>
                  {{ cancelPolicyName(langs.en) }}
                </p>
              </div>
            </div>
            <h3 :content="$t('bgarant')"></h3>
            <div class="row row-settings">
              <div class="col cell-bgarants">
                <drop-down
                  id="dd-bgarants"
                  v-model="plan.bgarant"
                  label-key="title"
                  :items="bgarantsLocalized"
                  :disabled="updatePending"
                />
                <p class="data-desc">
                  {{ bgarantDesc }}
                </p>
              </div>
            </div>
            <h3 :content="$t('pages.rateplans.modal.payment-policy')"></h3>
            <div class="row row-settings">
              <div class="col cell-policies">
                <drop-down
                  id="dd-policies"
                  v-model="plan.policy"
                  label-key="name"
                  :items="policiesLocalized"
                  :disabled="updatePending"
                />
                <p class="data-desc">
                  {{ policyDesc }}
                </p>
              </div>
            </div>
            <h3 :content="$t('pages.rateplans.modal.section-bookable')"></h3>
            <div class="row row-settings">
              <div class="col validity-unlim-wrapper">
                <radio v-model="plan.bookable.mode" :val="bookable.anytime" name="bookable" :disabled="updatePending">
                  {{ $t('pages.rateplans.modal.bookable.anytime') }}
                </radio>
              </div>
            </div>
            <div class="row row-settings">
              <div class="col col-auto bookable-wrapper">
                <radio v-model="plan.bookable.mode" :val="bookable.periods" name="bookable" :disabled="updatePending">
                  {{ $t('pages.rateplans.modal.bookable.periods') }}
                </radio>
              </div>
              <div class="col">
                <div class="row row-bookable-period" v-for="(period, idx) in plan.bookable.periods"
                     :key="`bookable-${idx}`">
                  <div class="col bookable-wrapper">
                    <i18n :tag="false" path="pages.rateplans.modal.bookable.periods-dates">
                      <template #from>
                        <date-picker
                          :id="`date-period-from-${idx}`" v-model="period.from" :min-date="today"
                          class="bookable-from"
                          :disabled="updatePending || plan.bookable.mode !== bookable.periods"
                          :placeholder="$t('pages.rateplans.modal.bookable.placeholder-start-date')"
                          @input="periodFromChanged($event, idx)" />
                      </template>
                      <template #to>
                        <date-picker
                          :id="`date-period-until-${idx}`" v-model="period.until" :min-date="today"
                          class="bookable-until"
                          grow="down" ref="periodUntil" position="left-md-right"
                          :disabled="updatePending || plan.bookable.mode !== bookable.periods"
                          :placeholder="$t('pages.rateplans.modal.bookable.placeholder-end-date')"
                          @input="periodUntilChanged($event, idx)" />
                      </template>
                    </i18n>
                    <b-btn v-if="idx > 0" class="btn-icon btn-tiny" @click="removePeriod(idx)"
                           :disabled="updatePending || plan.bookable.mode !== bookable.periods">
                      <icon width="18" height="18" type="delete"/>
                    </b-btn>
                  </div>
                </div>
                <div class="row bookable-button-wrapper">
                  <div class="col">
                    <b-btn
                      pill variant="outline-primary" class="bookable-button add-new-blockout" @click="addPeriod"
                      :disabled="updatePending || plan.bookable.mode !== bookable.periods">
                      <icon width="10" height="11" type="plus"/>
                      {{ $t('pages.rateplans.modal.bookable.button-add-period') }}
                    </b-btn>
                  </div>
                </div>
              </div>
            </div>
            <div class="row row-settings bookable-settings">
              <div class="col bookable-wrapper">
                <radio v-model="plan.bookable.mode" :val="bookable.fromto" id="bookableFromTo"
                       name="bookable" :disabled="updatePending"/>
                <i18n path="pages.rateplans.modal.bookable.fromto" :tag="false">
                  <template #from>
                    <AmountPercent
                      simple :symbol="$tc('pages.rateplans.modal.bookable.placeholder-days', plan.bookable.from)"
                      type="number" id="bookFrom" integer name="bookFrom" min="1" max="999"
                      class="bookable-field"
                      v-model="plan.bookable.from"
                      :rules="rulesFor('bookable-from')"
                      placeholder="0"
                      :disabled="updatePending || plan.bookable.mode !== bookable.fromto"
                    />
                  </template>
                  <template #to>
                    <AmountPercent
                      simple :symbol="$tc('pages.rateplans.modal.bookable.placeholder-days', plan.bookable.to)"
                      type="number" id="bookBefore" integer name="bookBefore" min="1" max="999"
                      class="bookable-field"
                      v-model="plan.bookable.to"
                      :rules="rulesFor('bookable-to')"
                      placeholder="0"
                      :disabled="updatePending || plan.bookable.mode !== bookable.fromto"
                    />
                  </template>
                </i18n>
              </div>

              <!--second row-->
              <div class="row row-settings bookable-settings">
                <div class="col bookable-wrapper">
                  <radio v-model="plan.bookable.mode" :val="bookable.until"
                         name="bookable" :disabled="updatePending">
                  </radio>
                  <i18n path="pages.rateplans.modal.bookable.until" :tag="false">
                    <template #days>
                      <AmountPercent
                        simple :symbol="$tc('pages.rateplans.modal.bookable.placeholder-days', plan.bookable.until)"
                        type="number" integer id="bookUntil" name="bookUntil" min="1" max="999"
                        class="bookable-field"
                        no-icon no-tooltip
                        v-model="plan.bookable.until"
                        :rules="rulesFor('bookable-until')"
                        placeholder="0"
                        :disabled="updatePending || plan.bookable.mode !== bookable.until"
                      />
                    </template>
                  </i18n>
                </div>
              </div>
              <!--third row-->
              <div class="row row-settings">
                <div class="col bookable-wrapper">
                  <radio v-model="plan.bookable.mode" :val="bookable.within" name="bookable"
                         :disabled="updatePending">
                  </radio>
                  <i18n path="pages.rateplans.modal.bookable.within" :tag="false">
                    <template #days>
                      <AmountPercent
                        simple :symbol="$tc('pages.rateplans.modal.bookable.placeholder-days', plan.bookable.within)"
                        type="number" integer id="bookWithin" name="bookWithin" min="1" max="999"
                        class="bookable-field"
                        no-icon no-tooltip
                        v-model="plan.bookable.within"
                        :rules="rulesFor('bookable-within')"
                        placeholder="0"
                        :disabled="updatePending || plan.bookable.mode !== bookable.within"
                      />
                    </template>
                  </i18n>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="edge edge-price">
          <h4 :class="{ opened: priceOpened }" @click="priceOpened=!priceOpened">
            {{ $t('pages.rateplans.modal.section-price') }}
            <icon width="12" height="7" stroke-width="2" type="arrow-down"/>
          </h4>
          <div>
            <h3 :content="priceHeader"></h3>
            <div class="row row-edit">
              <div class="col cell-edit-label double">
                <radio v-model="plan.price.mode" :disabled="disabledForPromo(true) || updatePending || !roomSelected"
                       name="price_mode"
                       val="standard">{{ $t('pages.rateplans.modal.standard-price') }}</radio>
              </div>
              <div class="col cell-edit-tip">
                {{ $t('pages.rateplans.modal.standard-price-tip') }}
              </div>
            </div>
            <div class="row row-edit">
              <div class="col cell-edit-label smaller">
                <radio v-model="plan.price.mode" :disabled="disabledForPromo(true) || updatePending || !roomSelected"
                       name="price_mode"
                       val="fixed" class="text-nowrap">{{ $t('pages.rateplans.modal.fixed-price') }}</radio>
              </div>
              <div class="col cell-edit-field single bigger">
                <amount-percent simple v-model="plan.price.fixed"
                                :required="plan.price.mode==='fixed'"
                                :disabled="disabledForPromo(true) || updatePending
                                  || !roomSelected || plan.price.mode!=='fixed'"
                />
              </div>
              <div class="col cell-edit-tip">
                {{ $t('pages.rateplans.modal.fixed-price-tip') }}
              </div>
            </div>
            <h3 class="stdcalc-title" :content="$t('pages.rateplans.modal.standard-price-more')"></h3>
            <div class="row row-edit no-tip">
              <div class="col cell-edit-label smaller">
                <radio
                  v-model="plan.price.stdcalc.mode"
                  :disabled="disabledForPromo(true) || updatePending || !roomSelected || plan.price.mode!=='standard'"
                  name="price_std_fix"
                  val="surcharge"
                >{{ $t('pages.rateplans.modal.surcharge') }}</radio>
              </div>
              <div class="col cell-edit-field bigger">
                <amount-percent
                  positive :price="plan.price.std" :result.sync="plan.price.stdres" accept-zero
                  v-model="plan.price.stdcalc.surcharge"
                  :disabled="disabledForPromo(true) || updatePending || !roomSelected
                    || plan.price.mode!=='standard' || plan.price.stdcalc.mode!=='surcharge'
                  "
                />
                <!-- :required="plan.price.mode==='standard' && plan.price.stdfix.mode==='surcharge'"-->
              </div>
            </div>
            <div class="row row-edit no-tip">
              <div class="col cell-edit-label smaller">
                <radio
                  v-model="plan.price.stdcalc.mode"
                  :disabled="disabledForPromo(true) || updatePending || !roomSelected || plan.price.mode!=='standard'"
                  name="price_std_fix"
                  val="reduction"
                >{{ $t('pages.rateplans.modal.reduction') }}</radio>
              </div>
              <div class="col cell-edit-field bigger">
                <amount-percent
                  negative :price="plan.price.std" :result.sync="plan.price.stdres" accept-zero
                  v-model="plan.price.stdcalc.reduction"
                  :disabled="disabledForPromo(true) || updatePending || !roomSelected
                    || plan.price.mode!=='standard' || plan.price.stdcalc.mode!=='reduction'
                  "
                />
                <!-- :required="plan.price.mode==='standard' && plan.price.stdfix.mode==='reduction'"-->
              </div>
            </div>
            <h3 :content="$t('pages.rateplans.modal.occupancy-more')"></h3>
            <div class="row row-edit row-occupancy no-tip">
              <div class="col cell-edit-field single">
                <label class="text-xs" for="occ-min">{{ $t('pages.rateplans.modal.minimum') }}</label>
                <ValidatedField
                  type="number" integer purenumber no-icon no-tooltip
                  id="occ-min" name="occ-min" min="1" max="999" class="mb-0"
                  v-model="plan.occupancy.min" :disabled="updatePending || !roomSelected"
                  :rules="rulesFor('minocc')"
                />
              </div>
              <div class="col cell-edit-field single inverted">
                <label class="text-xs" for="occ-std">{{ $t('pages.rateplans.modal.standard') }}</label>
                <ValidatedField
                  type="number" integer purenumber no-icon no-tooltip
                  id="occ-std" name="occ-std" min="1" max="999" class="mb-0"
                  v-model="plan.occupancy.std" :disabled="updatePending || !roomSelected"
                  :rules="rulesFor('stdocc')"
                />
              </div>
              <div class="col cell-edit-field single">
                <label class="text-xs" for="occ-max">{{ $t('pages.rateplans.modal.maximum') }}</label>
                <ValidatedField
                  type="number" integer purenumber no-icon no-tooltip
                  id="occ-max" name="occ-min" min="1" max="999" class="mb-0"
                  v-model="plan.occupancy.max" :disabled="updatePending || !roomSelected"
                  :rules="rulesFor('maxocc')"
                />
              </div>
              <div class="col cell-edit-fields cell-guests-price d-none d-md-flex">
                <guests-price :std="plan.price.mode==='standard'" :currency="currency.code"
                              :guests="plan.occupancy.std" :price="planFinalPrice"/>
              </div>
            </div>
            <div class="inner" v-if="notStandardOccupancy">
              <h3 class="line" :content="$t('pages.rateplans.modal.occupancy-pricing')"></h3>
              <div class="row row-edit row-surcharge d-none d-md-flex">
                <div class="col cell-edit-field">
                  <div class="row row-edit">
                    <div class="col cell-edit-label">
                      {{ $t('pages.rateplans.modal.surcharge') }}
                    </div>
                    <div class="col cell-edit-field">
                      <amount-percent
                        positive :price="planFinalPrice" accept-zero
                        v-model="plan.price.calc.surcharge"
                        :disabled="updatePending"
                      />
                    </div>
                  </div>
                  <div class="w-100"></div>
                  <div class="row row-edit">
                    <div class="col cell-edit-label">
                      {{ $t('pages.rateplans.modal.reduction') }}
                    </div>
                    <div class="col cell-edit-field">
                      <amount-percent
                        negative :price="planFinalPrice" accept-zero
                        v-model="plan.price.calc.reduction"
                        :disabled="updatePending"
                      />
                    </div>
                  </div>
                </div>
                <div class="col cell-edit-fields cell-guests-price">
                  <guests-price-multiple
                    :currency="currency.code" :occupancy="plan.occupancy" :price="planFinalPrice"
                    :surcharge="plan.price.calc.surcharge" :reduction="plan.price.calc.reduction"/>
                </div>
              </div>
              <div class="row row-edit d-md-none no-tip">
                <div class="col cell-edit-label smaller">
                  {{ $t('pages.rateplans.modal.surcharge') }}
                </div>
                <div class="col cell-edit-field bigger">
                  <amount-percent
                    positive :price="planFinalPrice" accept-zero
                    v-model="plan.price.calc.surcharge"
                    :disabled="updatePending"
                  />
                </div>
              </div>
              <div class="row row-edit d-md-none no-tip">
                <div class="col cell-edit-label smaller">
                  {{ $t('pages.rateplans.modal.reduction') }}
                </div>
                <div class="col cell-edit-field bigger">
                  <amount-percent
                    negative :price="planFinalPrice" accept-zero
                    v-model="plan.price.calc.reduction"
                    :disabled="updatePending"
                  />
                </div>
              </div>
              <h3 class="line" :content="$t('pages.rateplans.modal.children-bed')"></h3>
              <div class="row row-edit no-tip" v-for="cs in plan.price.calc.children" :key="`cbed-${cs.id}`">
                <div class="col cell-edit-field single smaller">
                  <ValidatedField type="number" purenumber integer min="1" max="18" no-icon no-tooltip
                                  :placeholder="$t('pages.rateplans.modal.age')" :rules="rulesFor('child-age')"
                                  v-model="cs.age" :name="`cha-${cs.id}`" class="mb-0"
                  />
                </div>
                <div class="col cell-edit-field bigger">
                  <amount-percent
                    positive accept-zero v-model="cs.surcharge"
                    :disabled="updatePending"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="edge">
          <h3 :content="$t('date.validity-period')"></h3>
          <div class="row row-validity">
            <div class="col-12 cell-val-from">
              <label class="text-xs" for="date-val-from">{{ $t('date.from') }}</label>
              <date-picker id="date-val-from" v-model="plan.validity.from" :min-date="today" grow="md-up"
                           :disabled="disabledForPromo() || updatePending" @input="validityFromChanged" />
            </div>
            <div class="col-12 cell-val-until">
              <label class="text-xs" for="date-val-until">{{ $t('date.until') }}</label>
              <div class="d-flex align-center">
                <radio v-model="plan.validity.unlim" :val="false" :disabled="disabledForPromo() || updatePending" />
                <date-picker id="date-val-until" v-model="plan.validity.until" :min-date="today" grow="md-up"
                             ref="validityUntil" position="left-md-right"
                             :disabled="disabledForPromo() || plan.validity.unlim || updatePending"
                             @input="validityUntilChanged" />
              </div>
              <div class="validity-unlim-wrapper">
                <radio v-model="plan.validity.unlim" :val="true"
                       :disabled="disabledForPromo() || updatePending">{{ $t('date.unlim') }}</radio>
              </div>
            </div>
          </div>
          <h6 :class="{ opened: blockoutsOpened }" @click="blockoutsOpened=!blockoutsOpened">
            {{ $t('date.blockout-periods') }}
            <icon width="12" height="7" stroke-width="2" type="arrow-down"/>
          </h6>
          <div class="danger">
            <div class="row row-blockout" v-for="(blockout, idx) in plan.blockouts" :key="`bl-${idx}`">
              <div class="col-12 cell-block-from">
                <label class="text-xs" :for="`date-block-from-${idx}`">{{ $t('date.from') }}</label>
                <date-picker :id="`date-block-from-${idx}`" v-model="blockout.from" :min-date="today"
                             grow="up" :disabled="updatePending" @input="blockFromChanged($event, idx)" />
              </div>
              <div class="col-12 cell-block-until">
                <label class="text-xs" :for="`date-block-until-${idx}`">{{ $t('date.until') }}</label>
                <date-picker :id="`date-block-until-${idx}`" v-model="blockout.until" :min-date="today"
                             grow="up" ref="blockUntil" position="left-md-right"
                             :disabled="updatePending" @input="blockUntilChanged($event, idx)" />
              </div>
              <div class="col-12 cell-block-delete">
                <b-btn class="btn-icon btn-tiny" @click="removeBlockout(idx)">
                  <icon width="18" height="18" type="delete"/>
                </b-btn>
              </div>
            </div>
            <div class="row row-blockout">
              <div class="col">
                <b-btn pill variant="outline-danger" class="add-new-blockout" @click="addBlockout">
                  <icon width="10" height="11" type="plus"/>
                  {{ $t('date.button-add-blockout') }}
                </b-btn>
              </div>
            </div>
          </div>
        </div>
      </ValidationObserver>
    </b-modal>

    <div class="panel position-relative panel-content">
      <div class="list d-none d-md-block left-edge">
        <p class="head-line"><span>{{ $t('pages.rateplans.heading') }}</span><spinner v-if="!loaded" /></p>
        <div class="plans-table" v-if="loaded">
          <table class="w-100">
            <thead>
              <tr>
                <th class="w-id">{{ $t('id') }}</th>
                <th class="w-name">{{ $t('pages.rateplans.headers.name') }}</th>
                <th class="w-meal">{{ $t('pages.rateplans.headers.meal-plan') }}</th>
                <th class="w-minlos">{{ $t('pages.rateplans.headers.min-stay') }}</th>
                <th class="w-cancels">{{ $t('pages.rateplans.headers.cancel-policy') }}</th>
                <th class="w-actions">{{ $t('actions') }}</th>
              </tr>
            </thead>

            <tbody v-if="noPlans">
              <tr>
                <td colspan="6" class="w-empty">{{ $t('pages.rateplans.no-plans') }}</td>
              </tr>
            </tbody>
            <tbody v-for="row in plans" :key="row.id">
              <tr class="separator before"></tr>
              <tr>
                <td>
                  {{ row.id }}
                </td>
                <td class="text-break">
                  {{ row.langs.en.name }}
                  <b-badge v-if="row.promo" class="ml-1"
                           :variant="row.promomode === 'promo' ? 'primary' : 'success'">{{ row.promo }}</b-badge>
                </td>
                <td>
                  <p>{{ mealNameById(row.meals) }}</p>
                </td>
                <td>
                  {{ row.minlos }}
                </td>
                <td>
                  {{ cancelNamesByIds(row.cancels) }}
                </td>
                <td class="actions">
                  <b-btn class="btn-icon btn-tiny" @click="editPlan(row)" :disabled="updatePending">
                    <icon width="17" height="17" type="edit"/>
                  </b-btn>
                  <b-btn class="btn-icon btn-tiny" @click="duplicatePlan(row.id)" :disabled="updatePending">
                    <icon width="18" height="18" type="copy"/>
                  </b-btn>
                  <b-btn class="btn-icon btn-tiny" @click="deletePlan(row.id)"
                         :disabled="updatePending || row.protected">
                    <icon width="19" height="19" type="delete"/>
                  </b-btn>
                </td>
              </tr>
              <tr class="separator after"></tr>
            </tbody>
          </table>
        </div>
        <div v-if="loaded">
          <b-btn pill variant="outline-primary" class="add-new-plan"
                 @click="openCreateForm">
            <icon width="10" height="11" type="plus"/>
            {{ $t('pages.rateplans.button-add') }}
          </b-btn>
        </div>
      </div>

      <p class="head-line d-md-none"><span>{{ $t('pages.rateplans.heading') }}</span><spinner v-if="!loaded" /></p>
      <div class="d-md-none list-item" v-if="noPlans">
        <div class="w-empty">{{ $t('pages.rateplans.no-plans') }}</div>
      </div>
      <div class="d-md-none list-item" v-for="row in plans" :key="row.id">
        <div class="d-flex">
          <div class="c-1">
            <p class="label">{{ $t('pages.rateplans.headers.name') }}</p>
            <p class="text-break">
              {{ row.langs.en.name }}
              <b-badge v-if="row.promo" class="ml-1"
                       :variant="row.promomode === 'promo' ? 'primary' : 'success'">{{ row.promo }}</b-badge>
            </p>
          </div>
          <div class="dots">
            <b-dropdown size="sm" toggle-tag="span" variant="link" no-caret right :disabled="updatePending">
              <template #button-content>
                <icon width="20" height="19" class="label" type="dots-h"/>
              </template>
              <b-dropdown-item @click="duplicatePlan(row.id)">{{ $t('buttons.duplicate') }}</b-dropdown-item>
              <b-dropdown-item @click="editPlan(row)">{{ $t('buttons.edit') }}</b-dropdown-item>
              <b-dropdown-item @click="deletePlan(row.id, true)"
                               :disabled="row.protected">{{ $t('buttons.delete') }}</b-dropdown-item>
            </b-dropdown>
          </div>
        </div>
        <div class="d-flex line">
          <div class="w-50">
            <p class="label">{{ $t('id') }}</p>
            <p>{{ row.id }}</p>
          </div>
          <div class="w-50">
            <p class="label">{{ $t('pages.rateplans.headers.min-stay') }}</p>
            <p>{{ row.minlos }}</p>
          </div>
        </div>
        <div class="d-flex line">
          <div class="c-1">
            <p class="label">{{ $t('pages.rateplans.headers.meal-plan') }}</p>
            <p>{{ mealNameById(row.meals) }}</p>
          </div>
        </div>
        <div class="d-flex line">
          <div class="c-1">
            <p class="label">{{ $t('pages.rateplans.headers.cancel-policy') }}</p>
            <p>{{ cancelNamesByIds(row.cancels) }}</p>
          </div>
        </div>
      </div>
    </div>
    <div class="d-md-none add-new-plan-block" v-if="loaded">
      <b-btn pill variant="outline-primary" class="add-new-plan" @click="openCreateForm">
        <icon width="10" height="10" type="plus"/>
        {{ $t('pages.rateplans.button-add') }}
      </b-btn>
    </div>
  </div>
</template>

<script>
  import moment from 'moment';
  import { mapState, mapGetters, mapActions } from 'vuex';
  import { langCodes, weekdays, bookable } from '@/shared';

  const travelOpts = ['', 'bdays', 'adays', 'ddays'];

  export default {
    name: 'RatePlans',
    data: () => ({
      plan: {},
      lang: 'en',
      langsValid: [],
      settingsOpened: false,
      priceOpened: false,
      blockoutsOpened: false,
      today: moment(),
    }),
    async created() {
      this.resetPlanModal();
      await this.fetchData(true);
      // document.querySelector('.add-new-plan').click();
    },
    computed: {
      ...mapGetters('user', ['currency']),
      ...mapGetters('rateplans', ['loaded', 'noPlans', 'plans', 'rooms', 'meals', 'cancels', 'bgarants', 'policies']),
      ...mapState('rateplans', [
        'error', 'pending',
        'updatePending',
      ]),
      langCodes: () => langCodes,
      weekdays: () => weekdays,
      travelOpts: () => travelOpts,
      travelOptsFiltered: () => travelOpts.filter((key) => !!key),
      bookable: () => bookable,
      edgeRoomLang() {
        return this.plan.room != null && this.langsValid.includes('en');
      },
      formValid() {
        const { mode, periods } = this.plan.bookable;
        const bookableValid = mode !== bookable.periods // other modes are covered by validator
          || periods.every(({ from, until }) => (from != null && until != null));
        return this.edgeRoomLang && bookableValid;
      },
      modalTitle() {
        if (this.plan.id == null) {
          return this.$t('pages.rateplans.modal.title-add');
        }
        const { id, langs: { en: { name } } } = this.plan;
        return this.$t('pages.rateplans.modal.title-edit', { id, name });
      },
      planFinalPrice() {
        return this.plan.price.mode === 'standard'
          ? this.plan.price.stdres : this.plan.price.fixed;
      },
      roomSelected() {
        return !!this.plan.room;
      },
      planRoom() {
        const val = this.plan.room;
        if (!val) return null;
        return this.rooms.find((room) => room.pid === val);
      },
      notStandardOccupancy() {
        const { min, std, max } = this.plan.occupancy;
        return min < std || max > std;
      },
      cancelsFlat() {
        return this.cancels.map(({ id, langs: { en: { name: text } } }) => ({ id, text }));
      },
      selectedCancels() {
        if (this.plan == null || this.plan.cancels == null || !this.plan.cancels) return [];
        return this.cancels.filter((c) => this.plan.cancels.includes(c.id));
      },
      bgarantsLocalized() {
        return this.bgarants.map(({ id }) => {
          const { text, title, desc } = this.$t(`bgarants.${id}`);
          return {
            id, text, title, desc,
          };
        });
      },
      bgarantDesc() {
        if (this.plan == null || this.plan.bgarant == null) return '';
        const k = `bgarants.${this.plan.bgarant}.desc`;
        return this.$t(k);
      },
      policiesLocalized() {
        return [
          {
            id: '0',
            ...this.$t('pages.rateplans.modal.no-payment-policy'),
          },
          ...this.policies,
        ];
      },
      policyDesc() {
        if (this.plan == null || this.plan.policy == null) return '';
        const policy = this.policiesLocalized.find((bg) => (bg.id - this.plan.policy) === 0);
        return policy != null ? policy.desc : '';
      },
      priceHeader() {
        if (!this.roomSelected) return this.$t('pages.rateplans.modal.price');
        const occupancy = this.$tc('pages.rateplans.modal.price-occupancy', this.plan.occupancy.std);
        return this.$t('pages.rateplans.modal.price-for', { occupancy });
      },
    },
    // {{ plan.id != null ? `[${plan.id}] ${plan.langs.en.name}` : 'New rate plan' }}
    // watch: {
    //   'plan.room': function watchRoom(room) {
    //     if (room) {
    //       this.updateRoomData();
    //     }
    //   },
    // },
    methods: {
      ...mapActions('rateplans', ['fetchData', 'createPlan', 'updatePlan', 'duplicatePlan', 'deletePlan']),

      disabledForPromo(price = false) {
        return this.plan.promo != null && (!price || this.plan.promomode === 'promo');
      },
      modalScroll(ev) {
        const modal = ev.target;
        this.$nextTick(() => {
          if (modal != null) modal.scrollTop = 0;
        });
      },
      resetPlanModal() {
        this.lang = 'en';
        this.langsValid = [];
        this.settingsOpened = false;
        this.priceOpened = false;
        this.blockoutsOpened = false;
        this.plan = {
          room: false,
          langs: Object.fromEntries(langCodes.map((c) => [c, {}])),
          bdays: [...weekdays],
          adays: [...weekdays],
          ddays: [...weekdays],
          minlos: 1,
          maxlos: 999,
          bookable: {
            mode: bookable.anytime,
            periods: [],
            from: '',
            to: '',
            until: '',
            within: '',
          },
          price: {
            mode: 'standard',
            std: 0,
            fixed: 0,
            stdres: 0,
            stdcalc: {
              mode: 'surcharge',
              surcharge: [],
              reduction: [],
            },
            calc: {
              children: [
                {
                  id: 0,
                  age: '',
                  surcharge: {
                    value: '',
                    mode: 'amount',
                  },
                },
                {
                  id: 1,
                  age: '',
                  surcharge: {
                    value: '',
                    mode: 'amount',
                  },
                },
                {
                  id: 2,
                  age: '',
                  surcharge: {
                    value: '',
                    mode: 'amount',
                  },
                },
              ],
              surcharge: {
                value: '',
                mode: 'amount',
              },
              reduction: {
                value: '',
                mode: 'amount',
              },
            },
          },
          occupancy: {
            std: '',
            min: '',
            max: '',
          },
          meals: '0',
          bgarant: 1,
          policy: '0',
          cancels: [],
          validity: {
            from: moment(),
            until: moment().add(1, 'year'),
            unlim: true,
          },
          blockouts: [],
        };
        this.checkBookablePeriods();
        this.$nextTick(() => {
          // this.updateMinOcc();
          // this.updateMaxOcc();
        });
        this.$nextTick(() => {
          this.$refs.langSel.resetScroller();
        });
        if (this.$refs.planForm != null) {
          this.$refs.planForm.reset();
        }
      },
      openCreateForm() {
        this.resetPlanModal();
        this.$nextTick(this.$refs.planModal.show);
      },
      editPlan(plan) {
        this.plan = {};
        this.resetPlanModal();
        this.plan = {
          ...this.plan,
          ...JSON.parse(JSON.stringify(plan)), // deep clone without bindings
          ...{
            std: 0,
            fixed: 0,
            stdres: 0,
          },
        };
        this.blockoutsOpened = this.plan.blockouts.length > 0;
        Object.keys(this.plan.langs).forEach((code) => {
          this.toggleLangValid(this.plan.langs[code].name, code);
        });
        this.updateRoomData();
        this.checkBookablePeriods();
        this.$nextTick(() => {
          this.$refs.planForm.reset();
          this.$refs.planModal.show();
        });
      },
      async processForm() {
        const plan = { ...this.plan };
        // fix dates
        ['from', 'until'].forEach((k) => {
          const v = plan.validity[k];
          if (moment.isMoment(v)) {
            plan.validity[k] = v.format('YYYY-MM-DD');
          }
        });
        (plan.blockouts || []).forEach((b) => {
          ['from', 'until'].forEach((k) => {
            const v = b[k];
            if (moment.isMoment(v)) {
              // eslint-disable-next-line no-param-reassign
              b[k] = v.format('YYYY-MM-DD');
            }
          });
        });

        (plan.bookable.periods || []).forEach((b) => {
          ['from', 'until'].forEach((k) => {
            const v = b[k];
            if (moment.isMoment(v)) {
              // eslint-disable-next-line no-param-reassign
              b[k] = v.format('YYYY-MM-DD');
            }
          });
        });
        try {
          if (plan.id != null) {
            await this.updatePlan(plan);
          } else {
            await this.createPlan(plan);
          }
          this.$refs.planModal.hide();
        } catch (error) {
          // eslint-disable-next-line no-console
          // console.error(error.message);
        }
      },
      checkBookablePeriods() {
        if (!this.plan.bookable.periods.length) {
          this.plan.bookable.periods.push({
            from: null, until: null,
          });
        }
      },
      updateRoomData(initial = true) {
        const room = this.planRoom;
        if (room == null) return;
        this.plan.price.std = 0;
        this.$nextTick(() => {
          this.plan.price.std = parseFloat(room.price);
        });
        this.checkBookablePeriods();

        if (initial) return;
        const std = parseInt(room.occupancy.std, 10);
        const min = parseInt(room.occupancy.min, 10);
        const max = parseInt(room.occupancy.max, 10);
        this.plan.occupancy = { min, std, max };

        if (this.plan.id != null) return;
        this.plan.price.calc = {
          children: [
            {
              id: 0,
              age: '',
              surcharge: {
                value: '',
                mode: 'amount',
              },
            },
            {
              id: 1,
              age: '',
              surcharge: {
                value: '',
                mode: 'amount',
              },
            },
            {
              id: 2,
              age: '',
              surcharge: {
                value: '',
                mode: 'amount',
              },
            },
          ],
          surcharge: {
            value: '',
            mode: 'amount',
          },
          reduction: {
            value: '',
            mode: 'amount',
          },
        };
      },
      toggleLangValid(val, code) {
        const idx = this.langsValid.indexOf(code);
        if (`${val}`.trim()) {
          if (idx === -1) {
            this.langsValid.push(code);
          }
        } else if (idx !== -1) {
          this.langsValid.splice(idx, 1);
        }
      },
      validityFromChanged(dt) {
        if (dt.isAfter(this.plan.validity.until, 'date')) {
          this.plan.validity.until = moment(dt);
        }
        this.$nextTick(() => {
          this.$refs.validityUntil.$el.focus();
        });
      },
      validityUntilChanged(dt) {
        if (dt.isBefore(this.plan.validity.from, 'date')) {
          this.plan.validity.from = moment(dt);
        }
      },
      addBlockout() {
        this.plan.blockouts.push({
          from: moment(),
          until: moment(),
        });
      },
      addPeriod() {
        this.plan.bookable.periods.push({
          from: null,
          until: null,
        });
      },
      removeBlockout(idx) {
        this.plan.blockouts.splice(idx, 1);
      },
      removePeriod(idx) {
        this.plan.bookable.periods.splice(idx, 1);
      },
      periodFromChanged(dt, idx) {
        if (dt.isAfter(this.plan.bookable.periods[idx].until, 'date')) {
          this.plan.bookable.periods[idx].until = moment(dt);
        }
        this.$nextTick(() => {
          this.$refs.periodUntil[idx].$el.focus();
        });
      },
      blockFromChanged(dt, idx) {
        if (dt.isAfter(this.plan.blockouts[idx].until, 'date')) {
          this.plan.blockouts[idx].until = moment(dt);
        }
        this.$nextTick(() => {
          this.$refs.blockUntil[idx].$el.focus();
        });
      },
      blockUntilChanged(dt, idx) {
        if (dt.isBefore(this.plan.blockouts[idx].from, 'date')) {
          this.plan.blockouts[idx].from = moment(dt);
        }
      },
      periodUntilChanged(dt, idx) {
        if (dt.isBefore(this.plan.bookable.periods[idx].from, 'date')) {
          this.plan.bookable.periods[idx].from = moment(dt);
        }
      },
      rulesFor(field) {
        const rules = {
          required: true,
        };
        const { min, max } = (this.planRoom || { occupancy: { min: 0, max: 0 } }).occupancy;
        switch (field) {
          case 'avail':
            rules.between = { min: 0, max: 999 };
            rules.numeric = true;
            break;
          case 'price':
            rules.between = { min: 0.01, max: 999999999.99 };
            break;
          case 'minlos':
            rules.between = { min: 1, max: 999 };
            rules.numeric = true;
            rules.maxvalue = this.plan.maxlos;
            break;
          case 'maxlos':
            rules.between = { min: 1, max: 999 };
            rules.numeric = true;
            rules.minvalue = this.plan.minlos;
            break;
          case 'minocc':
            rules.between = { min, max: this.plan.occupancy.std };
            rules.numeric = true;
            break;
          case 'stdocc':
            rules.between = { min: this.plan.occupancy.min, max: this.plan.occupancy.max };
            rules.numeric = true;
            break;
          case 'maxocc':
            rules.between = { min: this.plan.occupancy.std, max };
            rules.numeric = true;
            break;
          case 'child-age':
            rules.between = { min: 1, max: 18 };
            rules.numeric = true;
            rules.required = false;
            break;
          case 'bookable-from':
            rules.numeric = true;
            if (this.plan.bookable.mode === bookable.fromto) {
              rules.between = { min: 0, max: 999 };
              rules.is_not = this.plan.bookable.to;
              rules.required = true;
            } else {
              rules.required = false;
            }
            break;
          case 'bookable-to':
            rules.numeric = true;
            if (this.plan.bookable.mode === bookable.fromto) {
              rules.between = { min: 0, max: 999 };
              rules.is_not = this.plan.bookable.from;
              rules.required = true;
            } else {
              rules.required = false;
            }
            break;
          case 'bookable-until':
            rules.numeric = true;
            if (this.plan.bookable.mode === bookable.until) {
              rules.between = { min: 0, max: 999 };
              rules.required = true;
            } else {
              rules.required = false;
            }
            break;
          case 'bookable-within':
            rules.numeric = true;
            if (this.plan.bookable.mode === bookable.within) {
              rules.between = { min: 0, max: 999 };
              rules.required = true;
            } else {
              rules.required = false;
            }
            break;
          default:
            break;
        }
        return rules;
      },
      rulesForLang(lang, type) {
        const rules = {};
        if (lang === 'en' && type === 'name') {
          rules.required = true;
        }
        rules.max = type === 'name' ? 200 : 2000;
        return rules;
      },
      isTravelAll(key) {
        return this.plan[key].length === weekdays.length;
      },
      toggleTravelAll(key, on) {
        this.plan[key] = on ? [...weekdays] : [];
      },
      isTravelDay(key, wd) {
        return this.plan[key].includes(wd);
      },
      toggleTravelDay(key, wd, on) {
        const idx = this.plan[key].indexOf(wd);
        if (on) {
          if (idx < 0) {
            this.plan[key].push(wd);
          }
        } else if (idx >= 0) {
          this.plan[key].splice(idx, 1);
        }
      },
      cancelPolicyName(lang) {
        return lang.desc && lang.desc !== 'txt:name' ? lang.desc : lang.name;
      },
      removeCancelPolicy(id) {
        const idx = this.plan.cancels.findIndex((cid) => cid === id);
        if (idx !== -1) {
          this.plan.cancels.splice(idx, 1);
        }
      },
      mealNameById(id) {
        return this.meals.find((meal) => meal.id === id).text;
      },
      cancelNamesByIds(ids) {
        return this.cancels
          .filter(({ id }) => ids.includes(id))
          .map(({ langs }) => langs.en.name)
          .join(', ');
      },
    },
  };
</script>

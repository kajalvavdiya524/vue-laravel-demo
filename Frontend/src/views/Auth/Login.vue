<template>
  <div class="panel panel-form">
    <div class="panel-body">
      <img class="panel-image" src="/assets/images/cloud.svg" alt="Cloud image">
      <h1>{{ $t('login.heading') }}</h1>
      <p>{{ $t('login.tip') }}</p>
      <ValidationObserver ref="form" slim v-slot="{ handleSubmit }">
        <b-form @submit.prevent.stop="handleSubmit(submitForm)" novalidate>
          <b-form-group class="text-left mb-0">
            <label for="email" class="text-sm">{{ $t('labels.email') }}</label>
            <ValidatedField rules="required|email" :placeholder="$t('placeholder.email')" type="email" name="email"
                            v-model.trim="email" autocomplete="email" autofocus
                            :disabled="authenticating" :error-bag="validationError"/>
          </b-form-group>
          <b-form-group class="text-left mb-0">
            <label for="password" class="text-sm">{{ $t('labels.password') }}</label>
            <ValidatedField rules="required" :placeholder="$t('placeholder.password')" name="password"
                            type="password" v-model="password" autocomplete="current-password" local
                            :disabled="authenticating" :error-bag="validationError"/>
          </b-form-group>

          <b-form-invalid-feedback class="mt-0 mb-1" :class="{'d-block':authError}">
            {{ authError }}
          </b-form-invalid-feedback>
          <submit-button :loading="authenticating">{{ $t('login.button-login') }}</submit-button>
          <div class="alert alert-danger" role="alert" v-if="rateLimitError">
            {{ rateLimitError.message }}
          </div>
        </b-form>
      </ValidationObserver>
      <router-link :to="{ name:'password.email' }" :disabled="authenticating">
        {{ $t('login.link-reset-password') }}
      </router-link>
    </div>
    <div class="panel-footer">
      <i18n tag="p" class="m-0" path="signup.signup">
        <template #signup>
          <router-link :to="{ name:'register' }"
                       :disabled="authenticating">{{ $t('signup.button-signup') }}</router-link>
        </template>
      </i18n>
    </div>
  </div>
</template>

<script>
  import { mapGetters, mapActions } from 'vuex';

  export default {
    name: 'Login',
    data() {
      return {
        email: '',
        password: '',
      };
    },
    computed: {
      ...mapGetters('auth', ['authenticating', 'authError', 'validationError', 'rateLimitError']),
    },
    methods: {
      ...mapActions('auth', ['login']),

      submitForm() {
        const { email, password } = this;
        this.$nextTick(() => this.$refs.form.reset());
        this.login({
          email,
          password,
        });
      },
    },
  };
</script>

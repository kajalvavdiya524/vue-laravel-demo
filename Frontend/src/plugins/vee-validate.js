import Vue from 'vue';
import {
  ValidationProvider, ValidationObserver, extend, configure,
} from 'vee-validate';
import {
  required, email, min, max, confirmed, between, numeric,
  min_value as minValue, max_value as maxValue,
  is_not as isNot,
} from 'vee-validate/dist/rules';
import i18n from '@/i18n';

Vue.component('ValidationProdiver', ValidationProvider);
Vue.component('ValidationObserver', ValidationObserver);

configure({
  defaultMessage(field, values) {
    if (values.target != null && i18n.te(`fields.${values.target}`)) {
      // eslint-disable-next-line no-param-reassign,no-underscore-dangle
      values.target = i18n.t(`fields.${values.target}`);
    }
    // eslint-disable-next-line no-underscore-dangle
    return i18n.t(`validation.${values._rule_}`, values);
  },
});

extend('minvalue', minValue);
extend('maxvalue', maxValue);
extend('email', email);
extend('required', required);
extend('is_not', isNot);
extend('accepted', {
  validate(value) {
    return required.validate(value, { allowFalse: false });
  },
});
extend('sameAs', {
  validate(value, { target }) {
    return String(value) === String(target);
  },
  params: ['target'],
});
extend('required_string', {
  computesRequired: true,
  validate(value) {
    const result = {
      valid: false,
      required: true,
    };
    if (value == null) {
      return result;
    }
    result.valid = !!String(value).length;
    return result;
  },
});
extend('min', min);
extend('max', max);
extend('confirmed', confirmed);
extend('password', {
  validate(value) {
    if (value.length < 8) {
      return i18n.t('validation.min', { length: 8 });
    }
    if (!/[A-Z]+/.test(value)) {
      return i18n.t('validation.password-uppercase-letters');
    }
    if (!/[a-z]+/.test(value)) {
      return i18n.t('validation.password-lowercase-letters');
    }
    if (!/[0-9]+/.test(value)) {
      return i18n.t('validation.password-numbers');
    }
    if (!/[-!$%^&*()_+/|~='"`{}[:;<>?,.@#\]]+/.test(value)) {
      return i18n.t('validation.password-special-chars');
    }
    if (/\s/.test(value)) {
      return i18n.t('validation.password-no-spaces');
    }
    return true;
  },
});
extend('between', between);
extend('numeric', numeric);
extend('fail', {
  validate: (value) => value === true,
});
extend('url', {
  validate(value) {
    const schema = /^https?:\/\//.test(value);
    if (!/^(https?:\/\/)?.*\..*[a-z]/.test(value)) return false;
    const check = `${schema ? '' : 'http://'}${value}`;
    let valid;
    try {
      valid = new URL(check);
    } catch (e) {
      return false;
    }
    return valid != null;
  }
  ,
});
Vue.mixin({
  methods: {
    VState(errors, valid) {
      // eslint-disable-next-line no-nested-ternary
      return errors[0] ? false : (valid ? true : null);
    },
    VVState(errors, valid, field, dirty, errorBag) {
      const validationError = errorBag || this.validationError;
      const isInvalid = validationError && !dirty
        ? validationError.hasErrorsFor(field)
        : false;
      // eslint-disable-next-line no-nested-ternary
      return (isInvalid || errors[0]) ? false : (valid ? true : null);
    },
    VVError(errors, field, dirty, errorBag) {
      const validationError = errorBag || this.validationError;
      const error = validationError && !dirty
        ? validationError.firstErrorFor(field)
        : null;
      return error || errors[0];
    },
  },
});

/* eslint-disable no-shadow */

import StorageService from '@/services/storage.service';

import Vue from 'vue';
import { router } from '@/router';
import UserService from '@/services/user.service';
import {
  AuthError, TooManyAttemptsError, ValidationError, PMSError, HttpError,
} from '@/errors';
import {
  locales, apiEndpoint, defaultTextColors, defaultBackgroundColor,
} from '@/shared';
import ApiService from '@/services/api.service';
import moment from 'moment';
import i18n from '@/i18n';
import { localize } from 'vee-validate';

const endpoint = `${apiEndpoint}/user`;

const initialLang = StorageService.getLang();
ApiService.setLang(initialLang);
moment.locale(initialLang);
i18n.locale = initialLang;
localize(initialLang);
const initialHotel = StorageService.getHotel();
ApiService.setHotel(initialHotel);

const state = {
  user: StorageService.getUser(),
  emailResent: false,
  validationError: null,
  rateLimitError: null,
  pending: false,
  sessionExpired: false,
  lang: initialLang,
  hotel: initialHotel,
  hotelsPending: false,
  hotelsLoaded: false,
  pages: null,
  groupUpdatePending: false,
};

const getters = {
  loggedIn: (state) => state.user != null,
  user: (state) => state.user,
  userID: (state, getters) => (getters.loggedIn ? state.user.id : null),
  sessionExpired: (state) => state.sessionExpired,
  emailResent: (state) => state.emailResent,
  emailVerified: (state, getters) => getters.loggedIn && state.user.email_verified,
  pdFilled: (state, getters) => getters.loggedIn && state.user.pd_filled,
  cdFilled: (state, getters) => getters.loggedIn && state.user.cd_filled,
  setupComplete: (state, getters) => getters.loggedIn && state.user.setup_complete,
  setupStep: (state, getters) => (getters.loggedIn ? state.user.setup_step : 0),
  requiredFilled: (state, getters) => getters.pdFilled && getters.cdFilled,
  isAdmin: (state, getters) => getters.loggedIn && state.user.admin,
  hasProfile: (state, getters) => getters.loggedIn && state.user.profile != null,
  hasHotels: (state, getters) => getters.loggedIn && state.user.hotels.length > 0,
  multipleHotels: (state, getters) => getters.hasHotels && state.user.hotels.length > 1,
  hotels: (state, getters) => (getters.hasHotels ? state.user.hotels : []),
  hotelName: (state, getters) => (getters.hasHotels ? getters.hotel.name : ''),
  hotelID: (state, getters) => (getters.hasHotels ? getters.hotel.id : ''),
  hotelPages: (state, getters) => (getters.hasHotels ? getters.hotel.pages : false),
  currency: (state, getters) => (getters.hasHotels ? getters.hotel.currency : {}),
  pending: (state) => state.pending,
  validationError: (state) => state.validationError,
  rateLimitError: (state) => state.rateLimitError,
  lang: (state) => {
    let l = locales.find(({ code }) => code === state.lang);
    if (l == null) {
      l = locales.find(({ code }) => code === 'en');
    }
    return l;
  },
  hotel: (state, getters) => {
    if (!getters.loggedIn) return null;
    if (state.hotel == null) return {};
    return getters.hotels.find(({ id }) => id === state.hotel);
  },
  group: (state, getters) => (getters.loggedIn ? state.user.group : null),
  pageAllowed: (state, getters) => (...pages) => {
    const current = getters.hotel;
    if (current == null || current.perms == null) return false;
    // if (current.pages === true) return true; // user is hotel owner
    return Array.isArray(current.perms) && pages.some((page) => current.perms.includes(page));
  },
  userPageAllowed: (state, getters) => (...pages) => {
    const current = getters.user;
    if (current == null || current.perms == null) return false;
    // if (current.pages === true) return true; // user is hotel owner
    return Array.isArray(current.perms) && pages.some((page) => current.perms.includes(page));
  },
  allowedPages: (state, getters) => {
    const current = getters.hotel;
    if (current == null || current.perms == null) return [];
    return [...current.perms];
  },
  allowedUserPages: (state, getters) => {
    if (!getters.loggedIn) return [];
    return [...(getters.user.perms || [])];
  },
  currentLogo: (state, getters) => {
    const { group } = getters;
    return group == null || group.id == null ? null : group.logo;
  },
  currentColorSchema: (state, getters) => {
    const { group } = getters;
    return group == null || group.id == null ? defaultBackgroundColor : group.style.color_schema;
  },
  currentColorFont: (state, getters) => {
    const { group } = getters;
    return group == null || group.id == null ? defaultTextColors.white : group.style.color_font;
  },
};

const mutations = {
  clearErrors(state) {
    state.validationError = null;
    state.rateLimitError = null;
  },
  user(state, user) {
    // TODO: set/update user in storage?
    state.user = user;
    state.sessionExpired = false;
    state.pending = false;
  },
  sessionExpired(state) {
    if (state.user != null) {
      state.sessionExpired = true;
    }
  },
  emailResent(state, ok) {
    state.emailResent = !!ok;
  },
  beforeRequest(state) {
    state.validationError = null;
    state.rateLimitError = null;
    state.pending = true;
  },
  requestComplete(state) {
    state.pending = false;
  },
  validationError(state, error) {
    state.validationError = error;
    state.pending = false;
  },
  rateLimitError(state, error) {
    state.pending = false;
    state.rateLimitError = error;
  },
  currency(state, currency) {
    state.user.profile.currency = currency;
  },
  setup(state, data) {
    Object.keys(data).forEach((k) => {
      Vue.set(state.user, k, data[k]);
    });
    state.pending = false;
  },
  setLang(state, locale) {
    if (state.lang === locale) return;
    const l = locales.find(({ code }) => code === locale);
    if (l !== null) {
      StorageService.setLang(locale);
      state.lang = locale;
      ApiService.setLang(locale);
      moment.locale(locale);
      i18n.locale = locale;
      localize(locale);
    }
  },
  setHotel(state, hotel = null) {
    if (state.user == null) {
      state.hotel = null;
      ApiService.setHotel();
      return;
    }
    let h;
    if (hotel != null) {
      h = state.user.hotels.find(({ id }) => id === hotel) || null;
    }
    if (hotel == null || h == null) {
      h = state.user.hotels[0] || null;
    }
    const id = h != null ? h.id : null;
    StorageService.setHotel(id);
    state.hotel = id;
    ApiService.setHotel(id);
  },
  // emailVerified(state) {
  //   state.user.email_verified = true;
  // },
  // pdFilled(state) {
  //   state.user.pd_filled = true;
  // },
  // cdFilled(state) {
  //   state.user.cd_filled = true;
  // },
  beforeHotelsRequest(state, initial = false) {
    state.hotelsPending = true;
    if (initial) {
      state.hotelsLoaded = false;
    }
  },
  afterHotelsRequest(state) {
    state.hotelsPending = false;
  },
  hotels(state, hotels) {
    state.user.hotels = hotels;
    state.hotelsPending = false;
    state.hotelsLoaded = true;
  },
  addHotel(state, hotel) {
    if (state.user.hotels == null) {
      state.user.hotels = [];
    }
    state.user.hotels.push(hotel);
    state.hotelsPending = false;
  },
  updateHotel(state, hotel) {
    const idx = state.user.hotels.findIndex(({ id }) => id === hotel.id);
    if (idx !== -1) {
      Vue.set(state.user.hotels, idx, hotel);
    }
    state.hotelsPending = false;
  },
  patchHotel(state, hotel) {
    const h = state.user.hotels.find(({ id }) => id === hotel.id);
    if (h != null) {
      const data = { ...hotel };
      delete data.id;
      Object.keys(data).forEach((k) => {
        h[k] = data[k];
      });
    }
    state.hotelsPending = false;
  },
  deleteHotel(state, hotelId) {
    const idx = state.user.hotels.findIndex(({ id }) => id === hotelId);
    if (idx !== -1) {
      state.user.hotels.splice(idx, 1);
    }
    state.hotelsPending = false;
  },
  updateHotelsGroup(state, group) {
    const obj = JSON.parse(JSON.stringify(group));
    state.user.hotels.forEach((hotel) => {
      if (hotel.group == null || hotel.group.id !== group.id) return;
      Vue.set(hotel, 'group', obj);
    });
  },
  beforeGroupRequest(state) {
    state.groupUpdatePending = true;
  },
  afterGroupRequest(state, { group = null } = {}) {
    if (group != null) {
      state.user.group = group;
    }
    state.groupUpdatePending = false;
  },
};

function handleError(commit, error) {
  if (error instanceof AuthError) {
    commit('loginAuthError', {
      code: error.errorCode,
      message: error.message,
    });
  } else if (error instanceof ValidationError) {
    commit('validationError', error);
  } else if (error instanceof TooManyAttemptsError) {
    commit('rateLimitError', error);
  } else if (error instanceof PMSError) {
    throw error;
  } else {
    throw error;
  }
}

function handleApiError(commit, error) {
  const { status, data } = error.response;
  let err = error;
  switch (status) {
    case 400: // BadRequest
      err = new HttpError(status, data.message);
      break;
    case 409: // PMSError
      err = new PMSError(status, data.message);
      break;
    case 422:
      err = new ValidationError(status, data.message, data.errors);
      break;
    case 500:
      err = new Error(data.message);
      break;
    default:
      break;
  }
  // commit('update', err);
  throw err;
}

const actions = {
  async getUser({ state, commit }, { redirect = true, masterdata = false } = {}) {
    const wasLoggedIn = state.user != null;
    try {
      const user = await UserService.getUser(masterdata);
      await commit('user', user);
      const hotel = StorageService.getHotel();
      await commit('setHotel', hotel);
      if (!wasLoggedIn && redirect) {
        router.push(router.history.current.query.redirect || '/');
      }
    } catch (e) {
      if (wasLoggedIn) {
        commit('user', null);
        if (redirect) {
          router.push({ name: 'login' });
        }
      }
    }
  },
  async resendEmail({ commit }) {
    commit('beforeRequest');
    try {
      const ok = await UserService.resendEmail();
      commit('emailResent', ok);
      commit('requestComplete');
    } catch (e) {
      commit('emailResent', false);
      handleError(commit, e);
    }
  },
  async verifyEmail({ commit }, { id, hash, query }) {
    commit('beforeRequest');
    try {
      const user = await UserService.verifyEmail(id, hash, query);
      commit('requestComplete');
      if (user != null) {
        commit('user', user);
        router.pushInitial();
      }
    } catch (e) {
      handleError(commit, e);
    }
  },
  async updateProfile({ commit, getters }, data) {
    commit('beforeRequest');
    const needRedirect = !getters.requiredFilled;
    try {
      const user = await UserService.updateProfile(data);
      commit('user', user);
      if (user != null && user.hotels != null && user.hotels.length) {
        commit('setHotel');
      }
      commit('requestComplete');
      if (needRedirect && getters.requiredFilled) {
        router.pushInitial();
      }
    } catch (e) {
      handleError(commit, e);
    }
  },
  async updateProfileData({ commit }, data) {
    commit('beforeRequest');
    try {
      const user = await UserService.updateProfileData(data);
      commit('user', user);
      commit('requestComplete');
    } catch (e) {
      handleError(commit, e);
    }
  },
  async updateSetup({ commit, getters }, payload) {
    commit('beforeRequest');
    const needRedirect = !getters.setupComplete;
    try {
      const data = await UserService.setupStep(payload);
      commit('setup', data);
      if (needRedirect && getters.setupComplete) {
        router.pushInitial();
      }
    } catch (error) {
      handleError(commit, error);
    }
  },
  async loadHotels({ commit }) {
    commit('beforeHotelsRequest', true);
    try {
      const { data: hotels } = await ApiService.get(`${endpoint}/hotels`);
      commit('hotels', hotels);
      commit('setHotel', StorageService.getHotel());
    } catch (error) {
      commit('afterHotelsRequest');
      handleApiError(commit, error);
    }
  },
  async getHotel({ commit, getters }, id) {
    commit('beforeHotelsRequest');
    try {
      const { data: { hotel, data } } = await ApiService.get(`${endpoint}/hotels/${id || getters.hotel.id}`);
      commit('updateHotel', hotel);
      return data;
    } catch (error) {
      commit('afterHotelsRequest');
      handleApiError(commit, error);
      return null;
    }
  },
  async createHotel({ commit }, payload) {
    commit('beforeHotelsRequest');
    try {
      const { data: hotel } = await ApiService.post(`${endpoint}/hotels`, payload);
      commit('addHotel', hotel);
    } catch (error) {
      commit('afterHotelsRequest');
      handleApiError(commit, error);
    }
  },
  async updateHotel({ commit, getters }, data) {
    commit('beforeHotelsRequest');
    try {
      const { id } = data;
      let payload;
      if (data.logo == null || data.logo.upload == null) {
        payload = { ...data };
      } else {
        payload = new FormData();
        payload.appendFromObject(data);
      }
      delete payload.id;
      const { data: hotel } = await ApiService.post(`${endpoint}/hotels/${id || getters.hotel.id}`, payload);
      commit('updateHotel', hotel);
    } catch (error) {
      commit('afterHotelsRequest');
      handleApiError(commit, error);
    }
  },
  async toggleHotelStatus({ commit, getters }, data) {
    commit('beforeHotelsRequest');
    try {
      const { id } = data;
      const payload = { ...data };
      delete payload.id;
      const { data: log } = await ApiService.patch(`${endpoint}/hotels/${id || getters.hotel.id}`, payload);
      commit('patchHotel', data);
      commit('logs/insert', { key: 'booking', data: log }, { root: true });
    } catch (error) {
      commit('afterHotelsRequest');
      handleApiError(commit, error);
    }
  },
  async refreshPagesForHotel({ commit, getters }, hotelId = null) {
    const id = hotelId != null ? hotelId : getters.hotelID;
    try {
      commit('beforeHotelsRequest');
      const { data: pages } = await ApiService.get(`${endpoint}/hotels/${id}/pages`);
      if (pages === false) {
        commit('deleteHotel', id);
        commit('setHotel');
        return false;
      }
      commit('patchHotel', { id, pages });
      return true;
    } catch (error) {
      handleApiError(commit, error);
      return false;
    }
  },
  async refreshGroup({ commit }) {
    try {
      commit('beforeGroupRequest');
      const { data: group } = await ApiService.get(`${endpoint}/group`);
      commit('afterGroupRequest', { group });
      return true;
    } catch (error) {
      commit('afterGroupRequest');
      handleApiError(commit, error);
      return false;
    }
  },
  async updateGroup({ commit, getters }, data) {
    commit('beforeGroupRequest');
    try {
      let payload;
      if (data.logo == null || data.logo.upload == null) {
        payload = { ...data, _method: 'put' };
      } else {
        payload = new FormData();
        payload.append('_method', 'put');
        payload.appendFromObject(data);
      }
      const { data: group } = await ApiService.post(`${endpoint}/group/${getters.group.id}`, payload);
      commit('afterGroupRequest', { group });
      return true;
    } catch (error) {
      commit('afterGroupRequest');
      handleApiError(commit, error);
      return false;
    }
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};

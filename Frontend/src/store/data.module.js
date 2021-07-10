/* eslint-disable no-shadow */

import ApiService from '@/services/api.service';
import { apiEndpoint } from '@/shared';

const state = {
  countries: null,
  currencies: null,
  pages: null,
};

const getters = {
  countries: (state) => (state.countries != null ? state.countries : []),
  currencies: (state) => (state.currencies != null ? state.currencies : []),
  pages: (state) => (state.pages != null ? state.pages : []),
};

const mutations = {
  clearErrors() {
    // do nothing
  },
  countries(state, countries) {
    state.countries = countries;
  },
  currencies(state, currencies) {
    state.currencies = currencies;
  },
  pages(state, pages) {
    state.pages = pages;
  },
};

const actions = {
  async fetchCountries({ commit, getters }) {
    if (getters.countries.length) return;

    try {
      const response = await ApiService.get(`${apiEndpoint}/data/countries`);
      commit('countries', response.data);
    } catch (error) {
      // do nothing
    }
  },
  async fetchCurrencies({ commit, getters }) {
    if (getters.currencies.length) return;

    try {
      const response = await ApiService.get(`${apiEndpoint}/data/currencies`);
      commit('currencies', response.data);
    } catch (error) {
      // do nothing
    }
  },
  async fetchPages({ commit, getters }) {
    if (getters.pages.length) return;

    try {
      const response = await ApiService.get(`${apiEndpoint}/data/pages`);
      commit('pages', response.data);
    } catch (error) {
      // do nothing
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

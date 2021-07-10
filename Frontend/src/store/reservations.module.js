/* eslint-disable no-shadow */

import ApiService from '@/services/api.service';
import { apiEndpoint } from '@/shared';

const endpoint = `${apiEndpoint}/reservations`;

const state = {
  data: null,
  pending: false,
  error: null,
};

const getters = {
  loaded: (state) => state.data != null,
  empty: (state) => state.data != null && !state.data.length,
};

const mutations = {
  clearErrors(state) {
    state.error = null;
  },
  beforeLoading(state) {
    state.error = null;
    state.pending = true;
  },
  error(state, error) {
    state.pending = false;
    state.error = error;
  },
  data(state, data) {
    state.pending = false;
    state.data = data;
  },
  update(state, booking) {
    const record = state.data.find((b) => b.id === booking.id);
    if (record != null) {
      Object.keys(booking).forEach((k) => {
        record[k] = booking[k];
      });
    }
    state.pending = false;
  },
};

const actions = {
  async fetchData({ commit }, payload) {
    commit('beforeLoading');
    try {
      const response = await ApiService.post(`${endpoint}`, payload);
      commit('data', response.data);
    } catch (error) {
      commit('error', error);
    }
  },
  async cancelReservation({ commit }, payload) {
    commit('beforeLoading');
    try {
      const response = await ApiService.patch(`${endpoint}/cancel`, payload);
      commit('update', response.data);
    } catch (error) {
      commit('error', error);
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

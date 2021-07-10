/* eslint-disable no-shadow */

import Vue from 'vue';
import ApiService from '@/services/api.service';
import { apiEndpoint } from '@/shared';
import { PMSError, ValidationError } from '@/errors';

const endpoint = `${apiEndpoint}/systems`;

const state = {
  data: null,
  pending: false,
  error: null,
  updatePending: false,
  updateError: null,
};

const getters = {
  loaded: (state) => !state.pending && state.data != null,
  systems: (state, getters) => getters.loaded && state.data,
};

const mutations = {
  clearErrors(state) {
    state.error = null;
    state.updateError = null;
  },
  beforeLoading(state, initial = false) {
    if (initial) {
      state.data = null;
    }
    state.error = null;
    state.pending = true;
  },
  beforeUpdate(state) {
    state.updateError = null;
    state.updatePending = true;
  },
  data(state, { data = null, error = null } = {}) {
    state.pending = false;
    state.data = data;
    state.error = error;
  },
  modified(state, { system = null, software, error = null } = {}) {
    const o = state.data.find((p) => p.active != null);
    const same = o != null && o.id === system;
    if (o != null && !same) {
      Vue.delete(o, 'active');
    }
    if (system != null) {
      const n = same ? o : state.data.find((p) => p.id === system);
      if (n != null) {
        Vue.set(n, 'active', software);
      }
    }
    state.updatePending = false;
    state.updateError = error;
  },
};

function handleApiError(commit, error) {
  const { status, data } = error.response;
  let err = error;
  switch (status) {
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
  return err;
}

const actions = {
  async fetchData({ commit, state }, forced = false) {
    if (!forced && state.data != null) return;
    commit('beforeLoading');
    try {
      const { data } = await ApiService.get(`${endpoint}`);
      commit('data', { data });
    } catch (error) {
      commit('data', { error });
    }
  },
  async systemState({ commit }, { system = null, software = null } = {}) {
    commit('beforeUpdate');
    try {
      if (system && software) {
        await ApiService.post(`${endpoint}`, { system, software });
      } else {
        await ApiService.post(`${endpoint}`);
      }
      commit('modified', { system, software });
    } catch (error) {
      throw handleApiError(commit, error);
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

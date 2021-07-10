/* eslint-disable no-shadow */

import Vue from 'vue';
import ApiService from '@/services/api.service';
import { apiEndpoint } from '@/shared';
import { ValidationError } from '@/errors';

const endpoint = `${apiEndpoint}/plans`;

const state = {
  data: null,
  pending: false,
  error: null,
  updatePending: false,
  updateError: null,
};

const getters = {
  loaded: (state) => !state.pending && state.data != null,
  plans: (state, getters) => (getters.loaded ? state.data.plans : []),
  rooms: (state, getters) => (getters.loaded ? state.data.rooms : []),
  meals: (state, getters) => (getters.loaded ? state.data.meals : []),
  cancels: (state, getters) => (getters.loaded ? state.data.cancels : []),
  bgarants: (state, getters) => (getters.loaded ? state.data.bgarants : []),
  policies: (state, getters) => (getters.loaded ? state.data.policies : []),
  noPlans: (state, getters) => getters.loaded && !getters.plans.length,
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
  error(state, error) {
    state.pending = false;
    state.error = error;
  },
  data(state, plans) {
    state.pending = false;
    state.data = plans;
  },
  update(state, error = null) {
    state.updatePending = false;
    state.updateError = error;
  },
  created(state, plan) {
    state.data.plans.push(JSON.parse(JSON.stringify(plan)));
    state.updatePending = false;
    state.updateError = null;
  },
  modified(state, plan) {
    const idx = state.data.plans.findIndex((p) => p.id === plan.id);
    if (idx !== -1) {
      Vue.set(state.data.plans, idx, JSON.parse(JSON.stringify(plan)));
    }
    state.updatePending = false;
    state.updateError = null;
  },
  deleted(state, id) {
    const idx = state.data.plans.findIndex((plan) => plan.id === id);
    if (idx !== -1) {
      state.data.plans.splice(idx, 1);
    }
    state.updatePending = false;
    state.updateError = null;
  },
};

function handleApiError(commit, error) {
  const { status, data } = error.response;
  let err = error;
  switch (status) {
    case 422:
      err = new ValidationError(status, data.message, data.errors);
      break;
    default:
      break;
  }
  commit('update', err);
  throw err;
}

const actions = {
  async fetchData({ commit }, forced = false) {
    commit('beforeLoading', forced);
    try {
      const response = await ApiService.get(`${endpoint}`);
      commit('data', response.data);
    } catch (error) {
      commit('error', error);
    }
  },
  async createPlan({ commit }, plan) {
    commit('beforeUpdate');
    try {
      const { data } = await ApiService.post(`${endpoint}`, { ...plan });
      commit('created', data);
    } catch (error) {
      commit('update', error);
      throw handleApiError(commit, error);
    }
  },
  async updatePlan({ commit }, plan) {
    commit('beforeUpdate');
    try {
      await ApiService.put(`${endpoint}/${plan.id}`, { ...plan });
      commit('modified', plan);
    } catch (error) {
      commit('update', error);
      throw handleApiError(commit, error);
    }
  },
  async duplicatePlan({ commit }, id) {
    commit('beforeUpdate');
    try {
      const { data: plan } = await ApiService.post(`${endpoint}/${id}/duplicate`);
      commit('created', plan);
    } catch (error) {
      commit('update', error);
      throw handleApiError(commit, error);
    }
  },
  async deletePlan({ commit }, id) {
    commit('beforeUpdate');
    try {
      await ApiService.delete(`${endpoint}/${id}`);
      commit('deleted', id);
    } catch (error) {
      commit('update', error);
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

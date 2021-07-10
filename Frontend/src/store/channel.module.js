/* eslint-disable no-shadow */

import Vue from 'vue';
import ApiService from '@/services/api.service';
import { apiEndpoint } from '@/shared';
import { HttpError, PMSError, ValidationError } from '@/errors';

const endpoint = `${apiEndpoint}/channels`;

const state = {
  data: null,
  pending: false,
  error: null,
  updatePending: false,
  updateError: null,
};

const getters = {
  loaded: (state) => !state.pending && state.data != null,
  channel: (state, getters) => (getters.loaded ? state.data.channel : {}),
  ctypes: (state, getters) => (getters.loaded ? state.data.ctypes : []),
  cplans: (state, getters) => (getters.loaded ? state.data.cplans : []),
  rooms: (state, getters) => (getters.loaded ? state.data.rooms : []),
  plans: (state, getters) => (getters.loaded ? state.data.plans : []),
  mapped: (state, getters) => (getters.loaded ? state.data.mapped : []),
};

function updateMappedCount(state) {
  state.data.channel.count = Array.isArray(state.data.mapped)
    ? state.data.mapped.length
    : Object.keys(state.data.mapped).length;
}

function updateChannelInList(commit, channel) {
  commit('channels/modified', { channel }, { root: true });
}

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
    state.invalid = false;
  },
  beforeUpdate(state) {
    state.updateError = null;
    state.updatePending = true;
  },
  afterUpdate(state, error = null) {
    state.updatePending = false;
    state.updateError = error;
  },
  data(state, { data = null, error = null, update = false } = {}) {
    if (!update) {
      state.pending = false;
    } else {
      state.updatePending = false;
    }
    state.data = data;
    state.error = error;
  },
  connect(state, list = []) {
    if (Array.isArray(state.data.mapped)) Vue.set(state.data, 'mapped', {});
    list.forEach(({
      uniq, plan, mode, cplan: { id, typeid },
    }) => {
      Vue.set(state.data.mapped, plan.id, {
        id, typeid, uniq, mode,
      });
    });
    updateMappedCount(state);
    state.updatePending = false;
  },
  disconnect(state, list = []) {
    list.forEach(({ plan: { id } }) => {
      Vue.delete(state.data.mapped, id);
    });
    updateMappedCount(state);
    state.updatePending = false;
  },
  connectionUpdate(state, list) {
    list.forEach((m) => {
      Vue.set(state.data.mapped, m.plan.id, m);
    });
    updateMappedCount(state);
    state.updatePending = false;
  },
  mappingsUpdated(state, { rooms = null, error = null } = {}) {
    state.updatePending = false;
    state.updateError = error;
    if (rooms != null) {
      state.data.mapped = rooms.filter(({ inv }) => inv).map(({ id }) => id);
    }
    updateMappedCount(state);
  },
  promoCreated(state, { contract, plans }) {
    state.updatePending = false;
    state.data.channel.contractor.codes.push(contract);
    state.data.plans.push(...plans);
  },
  promoUpdated(state, { contract, discount }) {
    state.updatePending = false;
    const idx = state.data.channel.contractor.codes.findIndex(({ id }) => id === contract.id);
    if (idx >= 0) {
      Vue.set(state.data.channel.contractor.codes, idx, contract);
    }
    state.data.plans.forEach((plan) => {
      if (plan.promo !== contract.code) return;
      /* eslint-disable no-param-reassign */
      if (contract.mode === 'promo') {
        plan.price.stdcalc.reduction.value = discount;
      }
      plan.validity.from = contract.from;
      plan.validity.until = contract.until;
      /* eslint-enable no-param-reassign */
    });
  },
  promoDeleted(state, { promoId, ids }) {
    state.updatePending = false;
    const idx = state.data.channel.contractor.codes.findIndex(({ id }) => id === promoId);
    if (idx >= 0) {
      state.data.channel.contractor.codes.splice(idx, 1);
    }
    state.data.plans = state.data.plans.filter(({ id }) => !ids.includes(id));
  },
};

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
  return err;
}

const actions = {
  async fetchData({ commit }, { id, forced = false } = {}) {
    commit('beforeLoading', forced);
    try {
      const { data } = await ApiService.get(`${endpoint}/${id}`);
      commit('data', { data });
    } catch (error) {
      commit('data', { error });
      throw handleApiError(commit, error);
    }
  },
  async connectRatePlans({ commit, getters }, { id, list, update = false }) {
    commit('beforeUpdate');
    const rooms = list.map(({ cplan: { id, typeid }, plan: { id: rid }, mode }) => ({
      rid,
      id,
      typeid,
      mode,
      inv: true,
    }));
    try {
      await ApiService.put(`${endpoint}/${id}`, { rooms });
      if (!update) {
        commit('connect', list);
      } else {
        commit('connectionUpdate', list);
      }
      updateChannelInList(commit, getters.channel);
    } catch (error) {
      commit('afterUpdate');
      throw handleApiError(commit, error);
    }
  },
  async disconnectRatePlan({ commit, getters }, { id, list }) {
    commit('beforeUpdate');
    const rooms = list.map(({ cplan: { id, typeid }, plan: { id: rid }, mode }) => ({
      rid,
      id,
      typeid,
      mode,
      inv: false,
    }));
    try {
      await ApiService.put(`${endpoint}/${id}`, { rooms });
      commit('disconnect', list);
      updateChannelInList(commit, getters.channel);
    } catch (error) {
      commit('afterUpdate');
      throw handleApiError(commit, error);
    }
  },
  async updatePlanConnection({ dispatch }, { id, room, updates }) {
    const item = JSON.parse(JSON.stringify(room));
    Object.keys(updates).forEach((k) => {
      item[k] = updates[k];
    });
    await dispatch('connectRatePlans', { id, list: [item], update: true });
  },
  async channelMappings({ commit, getters }, { id, rooms }) {
    commit('beforeUpdate');
    try {
      await ApiService.put(`${endpoint}/${id}`, { rooms });
      commit('mappingsUpdated', { rooms });
      updateChannelInList(commit, getters.channel);
    } catch (error) {
      commit('mappingsUpdated', { error });
      throw handleApiError(commit, error);
    }
  },
  async updateChannelData({ commit, getters }, { id, payload }) {
    commit('beforeUpdate');
    try {
      const sendPayload = {
        mode: 'update',
        ...payload,
      };
      const { data } = await ApiService.patch(`${endpoint}/${id}`, sendPayload);
      commit('data', { data, update: true });
      updateChannelInList(commit, getters.channel);
    } catch (error) {
      // commit('mappingsUpdated', { error });
      commit('afterUpdate');
      throw handleApiError(commit, error);
    }
  },
  async createContract({ commit }, { id, promo }) {
    commit('beforeUpdate');
    try {
      const { mode } = promo;
      const payload = { ...promo };
      delete payload.mode;
      const { data: { contract, plans } } = await ApiService.post(`${endpoint}/${id}/${mode}`, payload);
      commit('promoCreated', { contract, plans });
    } catch (error) {
      const e = handleApiError(commit, error);
      commit('afterUpdate', (e instanceof ValidationError) ? e : null);
      throw e;
    }
  },
  async updateContract({ commit }, { id, promo }) {
    commit('beforeUpdate');
    try {
      const { id: promoId, mode } = promo;
      const payload = { ...promo };
      delete payload.id;
      delete payload.mode;
      const { data: { contract, discount } } = await ApiService.put(`${endpoint}/${id}/${mode}/${promoId}`, payload);
      commit('promoUpdated', { contract, discount });
    } catch (error) {
      commit('afterUpdate');
      throw handleApiError(commit, error);
    }
  },
  async deleteContract({ commit }, { id, promo }) {
    commit('beforeUpdate');
    const { id: promoId, mode } = promo;
    try {
      const { data: { ids } } = await ApiService.delete(`${endpoint}/${id}/${mode}/${promoId}`);
      commit('promoDeleted', { promoId, ids });
    } catch (error) {
      commit('afterUpdate');
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

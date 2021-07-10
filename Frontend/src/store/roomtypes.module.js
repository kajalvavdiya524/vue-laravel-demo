/* eslint-disable no-shadow */

import Vue from 'vue';
import ApiService from '@/services/api.service';
import { apiEndpoint } from '@/shared';
import { ValidationError } from '@/errors';

const endpoint = `${apiEndpoint}/rooms`;

const state = {
  rooms: null,
  typecodes: null,
  pending: false,
  error: null,
  updatePending: false,
  updateError: null,
};

const getters = {
  loaded: (state) => !state.pending && state.rooms != null,
  rooms: (state, getters) => (getters.loaded ? state.rooms : []),
  typecodes: (state, getters) => (getters.loaded ? state.typecodes : []),
  noRooms: (state, getters) => getters.loaded && !getters.rooms.length,
};

const mutations = {
  clearErrors(state) {
    state.error = null;
    state.updateError = null;
  },
  beforeLoading(state, initial = false) {
    if (initial) {
      state.rooms = null;
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
  data(state, { rooms, typecodes }) {
    state.pending = false;
    state.rooms = rooms;
    state.typecodes = typecodes;
  },
  update(state, error = null) {
    state.updatePending = false;
    state.updateError = error;
  },
  created(state, room) {
    state.rooms.push(JSON.parse(JSON.stringify(room)));
    state.updatePending = false;
    state.updateError = null;
  },
  modified(state, room) {
    const idx = state.rooms.findIndex((p) => p.id === room.id);
    if (idx !== -1) {
      Vue.set(state.rooms, idx, JSON.parse(JSON.stringify(room)));
    }
    state.updatePending = false;
    state.updateError = null;
  },
  deleted(state, pid) {
    const idx = state.rooms.findIndex((room) => room.pid === pid);
    if (idx !== -1) {
      state.rooms.splice(idx, 1);
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
      const { data: { rooms, typecodes } } = await ApiService.get(`${endpoint}`);
      commit('data', { rooms, typecodes });
    } catch (error) {
      commit('error', error);
    }
  },
  async createRoom({ commit }, room) {
    commit('beforeUpdate');
    try {
      const { data } = await ApiService.post(`${endpoint}`, { ...room });
      commit('created', data);
    } catch (error) {
      commit('update', error);
      throw handleApiError(commit, error);
    }
  },
  async updateRoom({ commit }, room) {
    commit('beforeUpdate');
    try {
      await ApiService.put(`${endpoint}/${room.id}`, { ...room });
      commit('modified', room);
    } catch (error) {
      commit('update', error);
      throw handleApiError(commit, error);
    }
  },
  async duplicateRoom({ commit }, pid) {
    commit('beforeUpdate');
    try {
      const { data: room } = await ApiService.post(`${endpoint}/${pid}`);
      commit('created', room);
    } catch (error) {
      commit('update', error);
      throw handleApiError(commit, error);
    }
  },
  async deleteRoom({ commit }, pid) {
    commit('beforeUpdate');
    try {
      await ApiService.delete(`${endpoint}/${pid}`);
      commit('deleted', pid);
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

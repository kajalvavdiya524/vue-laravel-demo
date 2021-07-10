/* eslint-disable no-shadow */

import ApiService from '@/services/api.service';
import { apiEndpoint } from '@/shared';
import { HttpError, PMSError, ValidationError } from '@/errors';
import Vue from 'vue';

const endpoint = `${apiEndpoint}/admin/groups`;
// const showEndpoint = `${apiEndpoint}/group`;

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

const state = {
  groups: null,
  pages: null,
  pending: false,
  loading: false,
  error: null,
  updatePending: false,
  updateError: null,
};

const getters = {
  loaded: (state) => !state.pending && state.groups != null,
  groups: (state, getters) => (getters.loaded ? state.groups : []),
  pages: (state, getters) => (getters.loaded ? state.pages : []),
  noGroups: (state, getters) => getters.loaded && !getters.groups.length,
  pending: (state) => state.pending,
};

const mutations = {
  clearErrors(state) {
    state.error = null;
    state.updateError = null;
  },
  beforeLoading(state, initial = false) {
    if (initial) {
      state.groups = null;
    }
    state.error = null;
    state.pending = true;
  },
  update(state, error = null) {
    state.updatePending = false;
    state.updateError = error;
  },
  beforeUpdate(state) {
    state.updateError = null;
    state.updatePending = true;
  },
  createGroup(state, data) {
    state.groups.push(JSON.parse(JSON.stringify(data)));
    state.updatePending = false;
    state.updateError = null;
  },
  groups(state, { groups, pages }) {
    state.pending = false;
    state.groups = groups;
    state.pages = pages;
  },
  error(state, error) {
    state.pending = false;
    state.error = error;
  },
  updateGroup(state, data) {
    const idx = state.groups.findIndex((group) => group.id === data.id);
    if (idx !== -1) {
      Vue.set(state.groups, idx, JSON.parse(JSON.stringify(data)));
    }
    state.updatePending = false;
    state.updateError = null;
  },
  updateOwner(state, data) {
    const group = state.groups.find(({ id }) => id === data.group_id);
    if (group != null) {
      Vue.set(group, 'owner', JSON.parse(JSON.stringify(data)));
      group.users_count += 1;
    }
  },
  modified(state, { id, data }) {
    const group = state.groups.find(({ id: gid }) => gid === id);
    if (group != null) {
      Object.entries(data).forEach(([k, v]) => {
        Vue.set(group, k, v);
      });
    }
  },
  deleted(state, id) {
    const idx = state.groups.findIndex((group) => group.id === id);
    if (idx !== -1) {
      state.groups.splice(idx, 1);
    }
    state.updatePending = false;
    state.updateError = null;
  },
};

const actions = {
  async createGroup({ commit }, data) {
    try {
      let payload;
      if (data.logo == null || data.logo.upload == null) {
        payload = { ...data };
      } else {
        payload = new FormData();
        payload.appendFromObject(data);
      }
      const { data: group } = await ApiService.post(`${endpoint}`, payload);
      commit('createGroup', group);
    } catch (error) {
      commit('update', null);
      handleApiError(commit, error);
    }
  },
  async getGroups({ commit }, forced = false) {
    commit('beforeLoading', forced);
    try {
      const { data } = await ApiService.get(`${endpoint}`);
      commit('groups', data);
    } catch (error) {
      commit('error', error);
      handleApiError(commit, error);
    }
  },
  async deleteGroup({ commit }, id) {
    try {
      await ApiService.delete(`${endpoint}/${id}`);
      commit('deleted', id);
      return true;
    } catch (error) {
      return null;
    }
  },
  async updateGroup({ commit }, data) {
    try {
      let payload;
      if (data.logo == null || data.logo.upload == null) {
        payload = { ...data, _method: 'put' };
      } else {
        payload = new FormData();
        payload.append('_method', 'put');
        payload.appendFromObject(data);
      }
      const { data: group } = await ApiService.post(`${endpoint}/${data.id}`, payload);
      commit('updateGroup', group);
      commit('user/updateHotelsGroup', group, { root: true });
    } catch (error) {
      commit('update', error);
      handleApiError(commit, error);
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

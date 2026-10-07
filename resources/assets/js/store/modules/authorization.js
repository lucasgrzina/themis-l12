import * as api from './../../api/config';
import * as types from './../../mutation-types';

export default {
    state: {
        roles: [],
        perms: []
    },
    mutations: {
        [types.SET_ROLES] (state, payload) {
            state.roles = payload.roles;
        }/*,
        [types.SET_PERMS] (state, payload) {
            state.perms = payload.perms;
        }*/,
        [types.CLEAR_ROLES] (state, payload) {
            state.roles = [];
        }/*,
        [types.CLEAR_PERMS] (state, payload) {
            state.perms = [];
        }*/
    },
    actions: {
        setRoles: ({commit, dispatch}) => {
            axios.get(api.rolesPermisos)
                .then(response => {
                    commit({
                        type: types.SET_ROLES,
                        roles: response.data
                    })
                })
                .catch(error => {
                    console.debug(error);
                    //dispatch('logoutRequest');
                })
        },
        clearRoles: ({commit}) => {
            commit({
                type: types.CLEAR_ROLES
            });
        }
    }
}
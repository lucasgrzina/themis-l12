import * as api from './../../api/config';
import jwtToken from './../../helpers/jwt-token';
import * as types from './../../mutation-types';

export default {
    state: {
        errors: {
            username: null,
            password: null
        }
    },
    mutations: {
        [types.LOGIN_SUCCESS] (state, payload) {
            state.errors.username = null;
            state.errors.passwor = null;
        },
        [types.LOGIN_FAILURE] (state, payload) {
            state.errors.username = payload.errors.username ? payload.errors.username[0] : null;
            state.errors.password = payload.errors.password ? payload.errors.password[0] : null;
        },
        [types.CLEAR_LOGIN_ERRORS] (state, payload) {
            state.errors.username = null;
            state.errors.password = null;
        }
    },
    actions: {
        loginRequest: ({dispatch}, formData) => {
            dispatch('hideAllNotifications');
            return new Promise((resolve, reject) => {
                axios.post(api.login, formData)
                    .then(response => {
                        dispatch('loginSuccess', response.data);
                        resolve();
                    })
                    .catch(error => {
                        dispatch('loginFailure', error);
                        reject(error);
                    });
            });
        },
        forgotRequest: ({dispatch}, formData) => {
            dispatch('hideAllNotifications');
            return new Promise((resolve, reject) => {
                axios.post(api.forgot, formData)
                    .then(response => {
                        dispatch('forgotSuccess', response.data);
                        dispatch('showSuccessNotification',response.data.message)
                        resolve(response);
                    })
                    .catch(error => {
                        console.debug(error);
                        dispatch('forgotFailure', error);
                        reject(error);
                    });
            });
        },        
        loginSuccess: ({commit, dispatch}, jwtTokenObj) => {
            jwtToken.setToken(jwtTokenObj.token);

            commit({
                type: types.LOGIN_SUCCESS
            });
            
            dispatch('loginUser');

        },
        loginFailure: ({commit, dispatch}, body) => {
            commit({
                type: types.LOGIN_FAILURE,
                errors: body.fields || []
            });

            if(body.message && body.message !== 'The given data was invalid.') {
                dispatch('showErrorNotification', body.message);
            }
        },
        forgotSuccess: ({commit, dispatch}) => {
            commit({
                type: types.LOGIN_SUCCESS
            });
        },
        forgotFailure: ({commit, dispatch}, body) => {
            commit({
                type: types.LOGIN_FAILURE,
                errors: body.fields || []
            });

            if(body.message && body.message !== 'The given data was invalid.') {
                dispatch('showErrorNotification', body.message);
            }
        },

        clearLoginErrors: ({commit}) => {
            commit({
                type: types.CLEAR_LOGIN_ERRORS
            });
        },
        logoutRequest: ({dispatch}) => {
            jwtToken.removeToken();

            return new Promise((resolve, reject) => {
                dispatch('unsetAuthUser');
                resolve();
            });
        }
    }
}
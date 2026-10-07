import * as api from './../../api/config';
import * as types from './../../mutation-types';

export default {
    state: {
        id: null,
        authenticated: false,
        name: null,
        username: null,
        role: null,
        perms: [],
        email: null,
        areas: [],
        avisos: [],
        cant_avisos: 0,
        vencimientos: [],
        notifications: [],
        notificarAvisosOnLogin: false,
        time_level: null
    },
    mutations: {
        [types.UPDATE_AUTH_USER_NAME] (state, payload) {
            state.name = payload.value;
        },
        [types.GET_AVISOS_CLIENTES] (state, payload) {
            state.avisos = payload.avisos;
            state.cant_avisos = payload.avisos.length;
        },
        [types.GET_CANT_AVISOS_CLIENTES] (state, payload) {
            state.cant_avisos = payload.avisos;
        },            
        [types.DESCARTAR_AVISO_CLIENTES] (state, payload) {
            state.avisos.splice(payload.index, 1);
        },     
        [types.GET_VENCIMIENTOS] (state, payload) {
            state.vencimientos = payload.vencimientos;
        },                   
        [types.UPDATE_AUTH_USER_EMAIL] (state, payload) {
            state.email = payload.value;
        },
        [types.UPDATE_AUTH_USER_ROLE] (state, payload) {
            state.perms = payload.role.permissions;
            state.role.name = payload.role.name;
        },
        [types.UPDATE_AUTH_USER_AREAS] (state, payload) {
            state.areas = payload.areas;
        },
        [types.SET_AUTH_USER] (state, payload) {
            state.authenticated = true;
            state.name = payload.user.name;
            state.username = payload.user.username;
            state.email = payload.user.email;
            state.role = payload.user.roles[0];
            if (state.perms.length === 0) {
                payload.user.roles[0].permissions.forEach(function(perm) {
                    state.perms.push(perm.name);
                });
            }
            state.id = payload.user.id;
            state.areas = payload.user.areas;
            state.notificarAvisosOnLogin = (payload.user.notificarAvisosOnLogin ? payload.user.notificarAvisosOnLogin : false); 
            state.time_level = payload.user.time_level;
        },
        [types.UPDATE_PROFILE_USER] (state, payload) {
            state.name = payload.user.name;
            state.username = payload.user.username;
            state.email = payload.user.email;
        },        
        [types.UNSET_AUTH_USER] (state, payload) {
            state.authenticated = false;
            state.name = null;
            state.email = null;
            state.role = null;
            state.perms = [];
            state.username = null;
            state.id = null;
            state.notificarAvisosOnLogin = false;
            state.areas = [];
            state.avisos = [];
            state.cant_avisos = 0;
            state.vencimientos = [];
        }
    },
    getters: {
        hasAnyPerm: (state) => (name) =>  {
            let _perms = (typeof name === 'string' ? [name] : name)
            if (state.perms.length > 0) {
                for(let i = 0;i<_perms.length;i++) {
                    if (state.perms.find(item => item == _perms[i])) {
                        return true;
                    }
                }
                return state.perms.find(item => item == name)    
            }
            return null;
        },
        isUserRole: (state) => (role) => {
            return state.role && state.role.id === role.id;
        },
        soyAdministrador: (state) => {
            return (state.role ? state.role.id == 1 : false);
        },
        isMember: (state) => (area_id) => {
            return typeof _.find(state.areas, function(area) { return area.area_id === area_id; }) !== 'undefined'
        },
        isResponsableArea: (state) => (area_id) => {
            return _.find(state.areas, { 'area_id': area_id, 'responsable': true })
        },
        isTimeLevel: (state) => (time_level) => {
            return state.time_level === time_level
        },        
    },
    actions: {
        loginUser: ({commit, dispatch}) => {
            axios.get(api.currentUser)
                .then(response => {
                    commit({
                        type: types.SET_AUTH_USER,
                        user: _.assign({notificarAvisosOnLogin: true},response.data)
                    })

                })
                .catch(error => {
                    dispatch('logoutRequest');
                })
        },
        setAuthUser: ({commit, dispatch}) => {
            axios.get(api.currentUser)
                .then(response => {
                    commit({
                        type: types.SET_AUTH_USER,
                        user: response.data
                    })

                })
                .catch(error => {
                    dispatch('logoutRequest');
                })
        },
        getCantAvisos: ({commit,state},filtros) => {
            let _url = api.cantAvisosPendientes;

            return axios.get(_url)
                .then(response => {
                    commit({
                        type: types.GET_CANT_AVISOS_CLIENTES,
                        avisos: response.data.data
                    })
                    let _notificar = state.notificarAvisosOnLogin;
                    if (state.notificarAvisosOnLogin && state.cant_avisos > 0) {
                        //state.notificarAvisosOnLogin = false;
                    }
                    return ({notificarAvisosOnLogin: _notificar, total: state.cant_avisos});                        
                })
        },        
        getAvisos: ({commit,state},filtros) => {
            let _url = api.avisosPendientes;
            if (filtros) {
                let queryString = Object.keys(filtros).map((key) => {
                    if (filtros[key] != null) {
                        return encodeURIComponent(key) + '=' + encodeURIComponent(filtros[key]);
                    }
                }).join('&');
                _url = _url.concat('?' + queryString);
            }

                return axios.get(_url)
                    .then(response => {
                        commit({
                            type: types.GET_AVISOS_CLIENTES,
                            avisos: response.data.data
                        })                   
                    })
        },
        descartarAviso: ({commit,state}, index) => {
            commit({
                type: types.DESCARTAR_AVISO_CLIENTES,
                index: index
            })            
            //state.avisos.splice(index, 1);    
        },
        getVencimientos: ({commit}) => {
            return axios.get(api.vencimientos)
                .then(response => {
                    commit({
                        type: types.GET_VENCIMIENTOS,
                        vencimientos: response.data.data
                    })
                })
        },        
        unsetAuthUser: ({commit}) => {
            commit({
                type: types.UNSET_AUTH_USER
            });
        },
        updateRole: ({commit, state},role) => {
            if(state.role) {
                commit({
                    type: types.UPDATE_AUTH_USER_ROLE,
                    role
                });
            }
        },
        updateProfile: ({commit,state},user) => {
                commit({
                    type: types.UPDATE_PROFILE_USER,
                    user
                });                
        },
        updateAreas: ({commit,state},areas) => {
                commit({
                    type: types.UPDATE_AUTH_USER_AREAS,
                    areas
                });                
        }        
    }
}
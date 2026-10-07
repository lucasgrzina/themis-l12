import * as api from './../../api/config';
import * as types from './../../mutation-types';

export default {
    state: {
        areas: [],
        clientesTime: []
    },
    mutations: {
        [types.SET_GENERAL_DATA] (state, payload) {
            state.areas = payload.data.areas;
        },
        [types.ADD_CLIENTE_TIME_DATA] (state, payload) {
            if (!_.find(state.clientesTime, { 'id': payload.data.id })) {
                state.clientesTime.push( payload.data)    
            }
        },
        [types.SET_CLIENTES_TIME_DATA] (state, payload) {
            state.clientesTime = payload.data;
        }                
    },
    getters: {
        findAreaById: (state) => (id) => {
            return _.find(state.areas, function(area) { return area.id === id; })
        },
        findAreaByName: (state) => (name) => {
            return _.find(state.areas, function(area) { return area.nombre.toLowerCase() === name; })
        },        
        findAreasByIds: (state) => (ids) => {
            return  _.filter(state.areas, function(item){
                return ids.indexOf(item.id) > -1; 
            })
        },        
        getAreas: (state) => {
            return state.areas;
        }
    },
    actions: {
        loadGeneralData: ({commit, dispatch}) => {
            axios.get(api.generalData)
                .then(response => {
                    commit({
                        type: types.SET_GENERAL_DATA,
                        data: response.data.data
                    })
                })                
        },
        setTimeClientesData({commit}, data) {
            commit({
                type: types.SET_CLIENTES_TIME_DATA,
                data: data
            });
        },        
        addTimeClienteData({commit}, data) {
            commit({
                type: types.ADD_CLIENTE_TIME_DATA,
                data: data
            });
        },        
    }
}
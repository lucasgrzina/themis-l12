import Api from './../../api';

export default {
    state: {
        timer: null,
        pendientes: 0,
        pendientesEquipo: null, // solo administradores
        avisoHoras: 0,
        offset: 0, // ms: hora del servidor - hora del navegador
        finishOpen: false
    },
    mutations: {
        SET_TIMER_STATE (state, payload) {
            state.timer = payload.timer;
            state.pendientes = payload.pendientes;
            state.pendientesEquipo = payload.pendientes_equipo;
            state.avisoHoras = payload.aviso_horas || 0;
            state.offset = payload.ahora ? new Date(payload.ahora).getTime() - Date.now() : 0;
        },
        SET_TIMER_FINISH_OPEN (state, open) {
            state.finishOpen = open;
        }
    },
    actions: {
        loadTimer ({commit, dispatch}) {
            return Api.get('time-horas/timer').then(resp => {
                commit('SET_TIMER_STATE', resp.data);
                if (resp.data.autocerrado) {
                    dispatch('showSuccessNotification', 'Un timer que quedó sin terminar se cerró automáticamente y está pendiente de vincular.');
                }
            });
        },
        startTimer ({commit}, data) {
            return Api.post('time-horas/timer/iniciar', data || {}).then(resp => commit('SET_TIMER_STATE', resp.data));
        },
        stopTimer ({commit}, data) {
            return Api.post('time-horas/timer/terminar', data || {}).then(resp => commit('SET_TIMER_STATE', resp.data));
        },
        discardTimer ({commit}) {
            return Api.post('time-horas/timer/descartar', {}).then(resp => commit('SET_TIMER_STATE', resp.data));
        },
        openTimerFinish ({commit}) {
            commit('SET_TIMER_FINISH_OPEN', true);
        },
        closeTimerFinish ({commit}) {
            commit('SET_TIMER_FINISH_OPEN', false);
        }
    }
}

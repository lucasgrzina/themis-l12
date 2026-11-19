import Api from './../../api';

export default {
    state: {
        timer: null,
        pendientes: 0,
        pendientesEquipo: null, // solo administradores
        avisoHoras: 0,
        offset: 0, // ms: hora del servidor - hora del navegador
        finishOpen: false,
        pausedAt: null // ms (reloj del navegador) en que se abrio el modal de terminar; el cronometro queda congelado
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
        },
        SET_TIMER_PAUSED_AT (state, value) {
            state.pausedAt = value;
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
        stopTimer ({commit, getters}, data) {
            let payload = Object.assign({}, data, { pausado_seg: getters.timerPausedSeconds });
            return Api.post('time-horas/timer/terminar', payload).then(resp => {
                commit('SET_TIMER_PAUSED_AT', null);
                commit('SET_TIMER_STATE', resp.data);
            });
        },
        discardTimer ({commit}) {
            return Api.post('time-horas/timer/descartar', {}).then(resp => commit('SET_TIMER_STATE', resp.data));
        },
        // Cancela el timer en curso (sin guardar) y cierra el modal; queda listo para iniciar otro
        cancelTimer ({commit}) {
            return Api.post('time-horas/timer/descartar', {}).then(resp => {
                commit('SET_TIMER_PAUSED_AT', null);
                commit('SET_TIMER_FINISH_OPEN', false);
                commit('SET_TIMER_STATE', resp.data);
            });
        },
        // Abre el modal de terminar y congela el cronometro
        openTimerFinish ({commit, state}) {
            if (state.timer && !state.pausedAt) commit('SET_TIMER_PAUSED_AT', Date.now());
            commit('SET_TIMER_FINISH_OPEN', true);
        },
        // Cierra el modal y, si estaba en pausa, reanuda el timer descontando la pausa
        closeTimerFinish ({commit, state, getters}) {
            commit('SET_TIMER_FINISH_OPEN', false);
            if (!state.pausedAt || !state.timer) {
                commit('SET_TIMER_PAUSED_AT', null);
                return Promise.resolve();
            }
            let segundos = getters.timerPausedSeconds;
            return Api.post('time-horas/timer/reanudar', { pausado_seg: segundos })
                .then(resp => commit('SET_TIMER_STATE', resp.data))
                .finally(() => commit('SET_TIMER_PAUSED_AT', null));
        }
    },
    getters: {
        timerPausedSeconds: state => state.pausedAt ? Math.max(0, Math.round((Date.now() - state.pausedAt) / 1000)) : 0
    }
}

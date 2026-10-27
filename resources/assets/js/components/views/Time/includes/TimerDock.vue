<template>
  <div v-if="visible">
    <button class="timer-fab" :class="{running: timer, warn: excedido}" :title="titulo" @click="onFab">
      <template v-if="timer">
        <i class="fa fa-stop"></i> <span class="timer-fab-clock">{{ elapsedText }}</span>
      </template>
      <i v-else class="fa fa-play"></i>
    </button>

    <modal v-model="finishOpen" class="themis-modal" effect="fade" :backdrop="false">
      <div slot="modal-header" class="modal-header">
        <h4 class="modal-title">Terminar timer <small>({{ elapsedText }})</small></h4>
      </div>
      <div slot="modal-body" class="modal-body" @keyup.esc="finishOpen = false">
        <modal-errors :messages="finish.errors"/>
        <p class="text-muted">Podés vincularlo ahora o guardarlo sin vincular y completarlo más tarde.</p>
        <div class="row">
          <div class="form-group col-sm-12">
            <label>Cliente</label>
            <v-select :value="finish.cliente" :options="clientes" :on-change="onChangeCliente" placeholder="Seleccioná cliente" label="nombre_completo"></v-select>
          </div>
          <div class="form-group col-sm-6">
            <label>Gestión</label>
            <select v-model="finish.gestion_id" class="form-control">
              <option :value="null"></option>
              <option v-for="g in gestiones" :value="g.id">{{ g.nombre }}</option>
            </select>
          </div>
          <div class="form-group col-sm-6">
            <label>Referencia</label>
            <input type="text" v-model="finish.referencia" list="timer-referencias" class="form-control input-sm">
            <datalist id="timer-referencias"><option v-for="r in referencias">{{ r.nombre }}</option></datalist>
          </div>
          <div class="form-group col-sm-12">
            <label>Descripción</label>
            <textarea v-model="finish.descripcion" class="form-control"></textarea>
          </div>
        </div>
      </div>
      <div slot="modal-footer" class="modal-footer">
        <button class="btn btn-flat btn-default" :disabled="busy" @click.prevent="finishOpen = false">Cancelar</button>
        <button class="btn btn-flat btn-warning" :disabled="busy" @click.prevent="stop(false)">Guardar sin vincular</button>
        <button class="btn btn-flat bg-purple" :disabled="busy" @click.prevent="stop(true)">Guardar y vincular</button>
      </div>
    </modal>
  </div>
</template>

<script>
import { modal } from 'vue-strap'
import vSelect from 'vue-select'
import { mapGetters } from 'vuex'
import Api from '../../../../api'
import TimerMixin from './TimerMixin'

// Timer global: boton flotante + atajo de teclado + modal para terminar.
// Se monta una sola vez en TopMenu para que funcione en toda la plataforma.
export default {
  name: 'TimerDock',
  components: { modal, vSelect },
  mixins: [TimerMixin],
  data () {
    return {
      busy: false,
      gestiones: [],
      referencias: [],
      finish: { errors: '', cliente: null, cliente_id: null, gestion_id: null, referencia: null, descripcion: '' }
    }
  },
  computed: {
    ...mapGetters(['isTimeLevel']),
    visible () {
      return this.isTimeLevel(5) || this.isTimeLevel(1)
    },
    clientes () {
      return _.sortBy(this.$store.state.general.clientesTime, o => o.nombre_completo)
    },
    finishOpen: {
      get () { return this.$store.state.timer.finishOpen },
      set (v) { this.$store.dispatch(v ? 'openTimerFinish' : 'closeTimerFinish') }
    },
    titulo () {
      return (this.timer ? 'Terminar timer' : 'Iniciar timer') + ' (Alt+Shift+T)'
    }
  },
  watch: {
    finishOpen (open) {
      if (open) this.prepareFinish()
    }
  },
  mounted () {
    window.addEventListener('keydown', this.onKey)
  },
  beforeDestroy () {
    window.removeEventListener('keydown', this.onKey)
  },
  methods: {
    onKey (e) {
      if (this.visible && e.altKey && e.shiftKey && (e.key === 'T' || e.key === 't')) {
        e.preventDefault()
        this.onFab()
      }
    },
    onFab () {
      if (this.busy) return
      if (this.timer) {
        this.finishOpen = true
      } else {
        this.busy = true
        this.$store.dispatch('startTimer', {}).finally(() => { this.busy = false })
      }
    },
    prepareFinish () {
      if (!this.timer) { this.finishOpen = false; return }
      this.finish = {
        errors: '', cliente: null, cliente_id: null, gestion_id: null,
        referencia: 'Temas Varios', descripcion: this.timer.descripcion || ''
      }
      this.referencias = []
      Api.combos('time/am-horas').then(resp => {
        this.gestiones = resp.data.gestion
        let def = _.find(resp.data.gestion, g => g.nombre === resp.data.gestion_default)
        if (def && !this.finish.gestion_id) this.finish.gestion_id = def.id
      })
      if (!this.clientes.length) {
        Api.combos('time/clientes/1').then(resp => this.$store.dispatch('setTimeClientesData', resp.data))
      }
    },
    onChangeCliente (item) {
      this.finish.cliente = item || null
      this.finish.cliente_id = item ? item.id : null
      this.referencias = []
      if (item) {
        Api.combos('time/referencias/' + item.id).then(resp => { this.referencias = resp.data.referencias })
      }
    },
    stop (vincular) {
      let f = this.finish
      if (vincular && !(f.cliente_id && f.gestion_id && f.referencia)) {
        f.errors = 'Para vincular completá cliente, gestión y referencia.'
        return
      }
      f.errors = ''
      this.busy = true
      let data = { descripcion: f.descripcion }
      // "Sin vincular" conserva lo que se haya completado; el servidor decide el estado
      if (f.cliente_id) {
        data = Object.assign(data, { cliente_id: f.cliente_id, gestion_id: f.gestion_id, referencia: f.referencia })
      }
      this.$store.dispatch('stopTimer', data).then(() => {
        this.finishOpen = false
        this.$events.fire('time-timer:finished')
      }, err => {
        f.errors = (err && err.message) || 'No se pudo terminar el timer'
      }).finally(() => { this.busy = false })
    }
  }
}
</script>
<style>
  .timer-fab {
    position: fixed; right: 24px; bottom: 24px; z-index: 1030;
    min-width: 52px; height: 52px; padding: 0 16px; border: 0; border-radius: 26px;
    background: #00a65a; color: #fff; font-size: 16px; box-shadow: 0 3px 10px rgba(0,0,0,.3);
  }
  .timer-fab.running { background: #dd4b39; }
  .timer-fab.warn { animation: timer-fab-pulse 1s infinite; }
  .timer-fab-clock { font-variant-numeric: tabular-nums; margin-left: 4px; }
  @keyframes timer-fab-pulse { 50% { opacity: .6; } }
</style>

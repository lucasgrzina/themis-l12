<template>
  <div class="box box-solid timer-bar">
    <div class="box-body">
      <div class="row">
        <div class="col-sm-6 col-md-7">
          <input type="text" v-model="descripcion" class="form-control" placeholder="¿En qué estás trabajando? (opcional)" :disabled="!!timer || busy" @keyup.enter="start">
        </div>
        <div class="col-sm-6 col-md-5 text-right">
          <span class="timer-clock" :class="{running: timer, warn: excedido}">{{ timer ? elapsedText : '00:00:00' }}</span>
          <button v-if="!timer" class="btn btn-sm btn-flat bg-green" :disabled="busy" @click.prevent="start"><i class="fa fa-play"></i> Iniciar</button>
          <template v-else>
            <button class="btn btn-sm btn-flat bg-red" :disabled="busy" @click.prevent="openFinish"><i class="fa fa-stop"></i> Terminar</button>
            <button class="btn btn-sm btn-flat btn-default" :disabled="busy" @click.prevent="discard" title="Descartar"><i class="fa fa-trash"></i></button>
          </template>
        </div>
      </div>
      <p v-if="pendientes > 0" class="text-yellow" style="margin:8px 0 0;">
        <i class="fa fa-exclamation-triangle"></i> Tenés {{ pendientes }} carga{{ pendientes > 1 ? 's' : '' }} sin vincular.
        <a href="javascript:void(0)" @click="$emit('show-pendientes')">Ver</a>
      </p>
    </div>

  </div>
</template>

<script>
import TimerMixin from './TimerMixin'

export default {
  name: 'TimerBar',
  mixins: [TimerMixin],
  data () {
    return { descripcion: '', busy: false }
  },
  methods: {
    start () {
      if (this.busy || this.timer) return
      this.busy = true
      this.$store.dispatch('startTimer', { descripcion: this.descripcion })
        .then(() => { this.descripcion = '' })
        .finally(() => { this.busy = false })
    },
    discard () {
      this.$dialog.confirm('¿Descartar el timer en curso? No se guardará ningún registro.', { loader: true }).then(dialog => {
        this.$store.dispatch('discardTimer').then(() => dialog.close(), () => dialog.close())
      })
    },
    openFinish () {
      this.$store.dispatch('openTimerFinish')
    }
  }
}
</script>
<style>
  .timer-bar .timer-clock { font-size: 24px; font-variant-numeric: tabular-nums; margin-right: 12px; color: #999; vertical-align: middle; }
  .timer-bar .timer-clock.running { color: #00a65a; }
  .timer-bar .timer-clock.warn { color: #dd4b39; }
</style>

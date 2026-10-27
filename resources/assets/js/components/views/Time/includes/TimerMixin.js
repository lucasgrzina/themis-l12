import { mapState } from 'vuex'

// Cronometro compartido por la barra de horas y el widget del header
export default {
  data () {
    return { now: Date.now(), ticker: null }
  },
  computed: {
    ...mapState({
      timer: state => state.timer.timer,
      pendientes: state => state.timer.pendientes,
      timerOffset: state => state.timer.offset,
      avisoHoras: state => state.timer.avisoHoras
    }),
    elapsedSeconds () {
      if (!this.timer) return 0
      return Math.max(0, Math.floor((this.now + this.timerOffset - new Date(this.timer.inicio).getTime()) / 1000))
    },
    excedido () {
      return !!this.timer && this.avisoHoras > 0 && this.elapsedSeconds > this.avisoHoras * 3600
    },
    elapsedText () {
      let s = this.elapsedSeconds
      let pad = n => (n < 10 ? '0' : '') + n
      return pad(Math.floor(s / 3600)) + ':' + pad(Math.floor(s % 3600 / 60)) + ':' + pad(s % 60)
    }
  },
  mounted () {
    this.ticker = setInterval(() => { this.now = Date.now() }, 1000)
  },
  beforeDestroy () {
    clearInterval(this.ticker)
  }
}

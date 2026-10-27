<template>
  <li v-if="visible" class="timer-widget">
    <router-link to="/time/horas" :title="excedido ? 'Timer corriendo hace mucho: ¿te olvidaste de terminarlo?' : (timer ? 'Timer en curso' : 'Ir a Horas')">
      <i class="fa" :class="timer ? (excedido ? 'fa-exclamation-triangle text-red' : 'fa-circle text-green') : 'fa-clock-o'"></i>
      <span v-if="timer" class="timer-widget-clock">{{ elapsedText }}</span>
    </router-link>
  </li>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
import TimerMixin from './TimerMixin'

export default {
  name: 'TimerWidget',
  mixins: [TimerMixin],
  computed: {
    ...mapGetters(['isTimeLevel']),
    ...mapState(['authUser']),
    visible () {
      return this.isTimeLevel(5) || this.isTimeLevel(1)
    }
  },
  watch: {
    visible: { immediate: true, handler (v) { if (v) this.$store.dispatch('loadTimer') } }
  }
}
</script>
<style>
  .timer-widget-clock { font-variant-numeric: tabular-nums; margin-left: 4px; }
</style>

<template>
  <div class="filter-bar">
      <form class="form-inline">
          <div class="input-group input-group-sm" style="max-width: 250px;">
            <input type="text" v-model="filterText" class="form-control input-sm pull-right" @keyup.enter="doFilter" @input="onInput">
            <div class="input-group-btn">
              <button class="btn btn-xs btn-flat bg-purple" @click.prevent="doFilter"><i class="fa fa-search"></i></button>
              <button class="btn btn-xs btn-flat btn-default" @click.prevent="resetFilter"><i class="fa fa-close"></i></button>
              
            </div>
          </div>        
          
      </form>    
  </div>
</template>

<script>
import debounce from 'lodash/debounce'
export default {
  props: {
    // Filtra a medida que se tipea (con debounce) en vez de esperar al Enter/botón
    live: {
      type: Boolean,
      default: true
    },
    debounce: {
      type: Number,
      default: 400
    }
  },
  data () {
    return {
      filterText: '',
      lastEmitted: ''
    }
  },
  created () {
    this.debouncedFilter = debounce(this.doFilter, this.debounce)
  },
  beforeDestroy () {
    this.debouncedFilter.cancel()
  },
  methods: {
    onInput () {
      if (this.live) {
        this.debouncedFilter()
      }
    },
    doFilter () {
      this.debouncedFilter.cancel()
      const text = this.filterText.trim()
      // evita requests repetidos si el valor efectivo no cambió
      if (text === this.lastEmitted) {
        return
      }
      this.lastEmitted = text
      this.$emit('vuetable:filter-set',text)
      //this.$events.fire('filter-set', this.filterText)
    },
    resetFilter () {
      this.debouncedFilter.cancel()
      this.filterText = ''
      this.lastEmitted = ''
      this.$emit('vuetable:filter-reset')
      //this.$events.fire('filter-reset')
    }
  }
}
</script>
<style>
  .filter-bar {
    display: inline-block;
  }
</style>
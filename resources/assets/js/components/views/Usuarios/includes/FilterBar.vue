<template>
  <div class="filter-bar">
      <form class="form-inline">
          <div class="form-group" style="max-width: 250px;">
            <input type="text" v-model="filter.search" class="form-control input-sm pull-right" @keyup.enter="doFilter">
          </div>        
          <div class="form-group" style="max-width: 250px;">
            <select v-model="filter.time" class="form-control input-sm" name="time" placeholder="Time">
              <option :value="null">Todos los usuarios</option>
              <optgroup label="Time">
                <option :value="'todos'">Todos los niveles</option>
                <option :value="1">Nivel 1</option>
                <option :value="2">Nivel 2</option>
                <option :value="5">Administrador</option>
                </optgroup>              
            </select>
          </div>        
          <div class="input-group input-group-sm">
            <div class="input-group-btn">
              <button class="btn btn-xs btn-flat bg-purple" @click.prevent="doFilter"><i class="fa fa-search"></i></button>
              <button class="btn btn-xs btn-flat btn-default" @click.prevent="resetFilter"><i class="fa fa-close"></i></button>
            </div>
          </div>        
      </form>    
  </div>
</template>

<script>
export default {
  data () {
    return {
      filter: {
        search: '',
        time: null
      }
    }
  },
  methods: {
    doFilter () {
      this.$emit('usuarios:filter-set',this.filter)
      //this.$events.fire('filter-set', this.filterText)
    },
    resetFilter () {
      this.filterText = ''
      this.$emit('usuarios:filter-reset')
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
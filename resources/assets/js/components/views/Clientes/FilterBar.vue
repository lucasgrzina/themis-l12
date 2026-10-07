<template>
  <div class="filter-bar">
      <form class="form-inline">
              <div class="form-group">
                <input type="text" placeholder="Cliente" v-model="filter.nombre_completo" class="form-control input-sm" @keyup.enter="doFilter">
              </div>
              <div class="form-group">
                <select v-model="filter.personeria" class="form-control input-sm" name="personeria">
                  <option v-for="option in info.personeria" v-bind:value="option.value">
                    {{ option.nombre }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <button class="btn btn-sm btn-flat btn-default" @click.prevent="resetFilter"><i class="fa fa-close"></i> Limpiar</button>
                <button class="btn btn-sm btn-flat bg-purple" @click.prevent="doFilter"><i class="fa fa-search"></i> Filtrar</button>        
              </div>
          
          
      </form>    
  </div>
</template>

<script>
export default {
  data () {
    return {
      info: {
        personeria: [
          {
            nombre: 'Personeria',
            value: ''
          },
          {
            nombre: 'Humana',
            value: 'H'
          },
          {
            nombre: 'Jurídica',
            value: 'J'
          }
        ]
      },
      filter: {
        nombre_completo: '',
        personeria: ''
      }
    }
  },
  methods: {
    doFilter () {
      this.$emit('clientes:filter-set',this.filter)
      //this.$events.fire('filter-set', this.filterText)
    },
    resetFilter () {
      this.filter.text = ''
      this.$emit('clientes:filter-reset')
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
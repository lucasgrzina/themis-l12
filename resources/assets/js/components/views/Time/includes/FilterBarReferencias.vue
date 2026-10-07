<template>
  <div class="filter-bar">
      <form class="form-inline">
              <div class="form-group" style="width:250px;">
                <v-select
                  :value="info.clientes.selected"
                  :clearSearchOnSelect="true"
                  :on-search="getClientes"
                  :options="info.clientes.data"
                  :on-change="onChangeCliente"
                  placeholder="Ingresa cliente"
                  label="nombre_completo"
                >
                </v-select> 
              </div>
              <!--div class="form-group">
                <button class="btn btn-sm btn-flat bg-purple" @click.prevent="doFilter"><i class="fa fa-search"></i> Filtrar</button>        
              </div-->
          
          
      </form>    
  </div>
</template>

<script>
import vSelect from "vue-select"
import Api from '../../../../api'
export default {
  components: {
    vSelect
  },  
  data () {
    return {
      info: {
        clientes: {
          selected: null,
          data: []
        }, 
      },
      filter: {
        cliente_id: ''
      }
    }
  },
  methods: {
    doFilter () {
      this.$emit('time-referencias:filter-set',this.filter)
      //this.$events.fire('filter-set', this.filterText)
    },
    resetFilter () {
      this.filter.text = ''
      this.$emit('time-referencias:filter-reset')
      //this.$events.fire('filter-reset')
    },
    getClientes (search, loading) {
      this.searchClientes(search, loading, this);
    },
    searchClientes: _.debounce((search, loading, vm) => {
        loading(true)
        Api.combos('time/clientes?search=' + search).then(resp => {
           vm.info.clientes.data = resp.data
           loading(false)
        })
    }, 500), 
    onChangeCliente(item) {
      if (item) {
        this.filter.cliente = item  
      } else {
        this.filter.cliente = null
      }
      
      this.$emit('time-referencias:filter-set',this.filter)
    }       
  }
}
</script>
<style>
  .filter-bar {
    display: inline-block;
  }
</style>
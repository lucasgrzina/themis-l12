<template>
  <div class="box box-solid">
        <div class="box-header with-border modal-header">
          <fieldset class="transparente" :disabled="!canFilter">
            <div class="filter-bar">
                <form class="form-inline">
                    <div v-if="isTimeLevel(5)" class="form-group">
                      <label for="user_id" style="display:block;">Abogado</label>
                        <select v-model="filtros.user_id" class="form-control" name="user_id">
                          <option :value="null">Todos</option>
                          <option v-for="option in info.abogados" v-bind:value="option.id">
                            {{ option.name }}
                          </option>
                        </select>
                    </div>        
                    <div class="form-group" style="width:250px;">
                      <label for="cliente" style="display:block;">Cliente</label>
                      <!--v-select
                        :value="info.clientes.selected"
                        :clearSearchOnSelect="true"
                        :on-search="getClientes"
                        :options="info.clientes.data"
                        :on-change="onChangeCliente"
                        placeholder="Ingresa cliente"
                        label="nombre_completo"
                      >
                      </v-select-->
                      <v-select v-model="info.clientes.selected" :on-change="onChangeCliente" :options="clientes" label="nombre_completo"></v-select> 
                    </div>
                    <div class="form-group" :class="{'has-error': errors.has('filtros.desde')}">
                      <label for="desde" style="display:block;">Desde</label>
                      <datepicker name="desde" v-model="filtros.desde" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
                      <span class="help-block" v-show="errors.has('filtros.desde')">{{ errors.first('filtros.desde') }}</span>
                    </div>  
                    <div class="form-group" :class="{'has-error': errors.has('filtros.hasta')}">
                      <label for="hasta" style="display:block;">Hasta</label>
                      <datepicker name="hasta" v-model="filtros.hasta" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
                      <span class="help-block" v-show="errors.has('filtros.hasta')">{{ errors.first('filtros.hasta') }}</span>
                    </div>              
                    <div class="form-group">
                      <label class="hidden-xs" style="display:block;">&nbsp;</label>
                      <button class="btn btn-sm btn-flat bg-purple" @click.prevent="doFilter"><i class="fa fa-search"></i> Filtrar</button>        
                    </div>
                </form>    
            </div>
             <div class="top-actions" v-if="!isTimeLevel(2)">
                <button-type type="new" @click="create()"></button-type>
             </div>
          </fieldset>
        </div>
  </div>        
</template>

<script>
import vSelect from "vue-select"
import Api from '../../../../api'
import { datepicker } from 'vue-strap'
import moment from 'moment'
import { mapGetters, mapState } from 'vuex'

export default {
  components: {
    vSelect,
    datepicker
  },  
  props: {
    filtros: {
      type: Object,
      default: {}
    }
  },
  data () {
    return {
      canFilter: false,
      info: {
        clientes: {
          selected: null,
          data: []
        },
        responsables: [] 
      }    
    }
  },
  computed: {
    ...mapGetters([
      'isTimeLevel'
    ]),
    ...mapState([
      'authUser','general'
    ]),
    clientes () {
      return _.sortBy(this.general.clientesTime, [function(o) { return o.nombre_completo; }]);
    }           
  },   
  mounted() {
    
    this.getClientes() 
    if (this.authUser.id) {
      this.getFilters()
    }
  },
  methods: {
    getFilters() {
      let _this = this;
      Api.combos('time/abogados').then((resp) => {
        _this.info.abogados = resp.data;
        if (_this.isTimeLevel(5)) {
          _this.filtros.user_id = null;
        }
        _this.canFilter = true;        
        _this.doFilter();
      });     
    },
    doFilter () {
      this.$emit('time-horas:filter-set')
      //this.$events.fire('filter-set', this.filterText)
    },
    create () {
      this.$emit('time-horas:create')
    },
    getClientes (search, loading) {
      let vm = this
      Api.combos('time/clientes/1').then(resp => {
        this.$store.dispatch('setTimeClientesData',resp.data)
         vm.info.clientes.data = resp.data
         //loading(false)
      })        
      //this.searchClientes(search, loading, this);
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
        this.filtros.cliente_id = item.id  
      } else {
        this.filtros.cliente_id = null
      }
    }       
  },
  watch: {
    'authUser.id': function(n,o) {
      if (n) {
        this.getFilters()
      }
    }
  }  
}
</script>
<style>
  .filter-bar {
    display: inline-block;
  }
</style>
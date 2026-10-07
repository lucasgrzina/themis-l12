<template>
<div>
	<div class="box box-primary box-solid themis-modal">
        <div class="box-header with-border modal-header">
          <h3 class="box-title">Filtros</h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
          </div>
          <!-- /.box-tools -->
        </div>
        <div class="box-body">
	      	<fieldset class="transparente" :disabled="!canFilter">
	      	<form class="row">
				<div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.desde')}">
					<label for="desde" style="display:block;">Fecha Alta Desde</label>
					<datepicker name="desde" v-model="filtros.desde" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
					<span class="help-block" v-show="errors.has('filtros.desde')">{{ errors.first('filtros.desde') }}</span>
				</div>	
				<div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.hasta')}">
					<label for="hasta" style="display:block;">Fecha Alta Hasta</label>
					<datepicker name="hasta" v-model="filtros.hasta" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
					<span class="help-block" v-show="errors.has('filtros.hasta')">{{ errors.first('filtros.hasta') }}</span>
				</div>		
				<div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.user_id')}">
					<label for="user_id">Abogado</label>
						<select name="user_id" v-model="filtros.user_id" class="form-control">
							<option :value="null">Todos</option>
							<option v-for="item in info.abogados" :value="item.id">{{ item.name }}</option>
						</select>
                    	<span class="help-block" v-show="errors.has('filtros.user_id')">{{ errors.first('filtros.user_id') }}</span> 
				</div>					
				<!--div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.cliente_id')}">
					<label for="cliente_id">Cliente</label>
						<v-select v-model="info.clientes.selected" :on-change="onChangeCliente" :options="clientes" label="nombre_completo"></v-select>
                      <span class="help-block" v-show="errors.has('filtros.cliente_id')">{{ errors.first('filtros.cliente_id') }}</span> 
				</div-->
	            <div class="form-group col-sm-3">
	            	<label class="hidden-xs" style="display:block;">&nbsp;</label>
	            	<button-type type="filter" @click="doFilter" :disabled="!canFilter"/>
	            </div>				
	      	</form>     
	      	</fieldset>        	
        </div>
  	</div>
	<div class="box">
        <div  v-if="firstFilter" class="box-header">
          <h3 class="box-title">&nbsp;</h3>
          <div class="box-tools">
            <button-type type="print" @click="print()"/>
          </div>
        </div>
        <!-- /.box-header -->
        <div v-if="firstFilter" class="box-body table-responsive no-padding">

	        <table class="table">
	            <tbody>

		            <template v-for="item in list.data.data"  v-if="!list.loading">
			            <template v-if="item.tipo && item.tipo === 'CABECERA'">
				            <tr >
				              <td colspan="8" style="background: #cac8f3;">
				              	<span style="text-transform:uppercase;font-weight:bold;">Cliente: {{ item.referencia }}</span>
				              </td>
				            </tr>
				            <tr>
				              <th>Fecha</th>
				              <th>Referencia</th>
				              <th>Abogado</th>
				              <th>Gestión</th>
				              <th>Descripción</th>
	  			              <th class="text-center" style="background:#eee;border-color: #eee;">Horas</th>
	  			              <th class="text-center">Factura</th>
				            </tr>
			        	</template>
			            <tr v-if="item.tipo && item.tipo === 'PIE'">
			              <td colspan="5" style="text-align:right;">
			              	<span style="text-transform:uppercase;font-weight:bold;">Total Abogado:</span>
			              </td>
			              <td class="text-center" style="background:#eee;border-color: #eee;">
			              	<span style="text-transform:uppercase;font-weight:bold;">{{ item.minutos | minutesToHours }}</span>
			              </td>
			              <td class="text-center" style="">&nbsp;</td>
			            </tr>	
			            <template v-if="item.tipo && item.tipo === 'TOTAL'">
				            <tr>
				              <td colspan="5" style="text-align:right;background:#eee;border-color: #eee;">
				              	<span style="text-transform:uppercase;font-weight:bold;">Total:</span>
				              </td>
				              <td class="text-center" style="background:#ddd;border-color: #ddd;">
				              	<span style="text-transform:uppercase;font-weight:bold;">{{ item.minutos | minutesToHours }}</span>
				              </td>
				              <td class="text-center" style="background:#eee;border-color: #eee;">{{ item.movimientos }} movimiento(s)</td>
				            </tr>
			            </template>
			            <tr v-if="item.id">
			              <td> {{ item.fecha | dateFormat }} </td>
			              <td> {{ item.referencia }} </td>
			              <td> {{ item.usuario ? item.usuario.name : '' }} </td>
			              <td> {{ item.gestion ? item.gestion.nombre : '' }} </td>
			              <td> {{ item.descripcion }} </td>
  			              <td class="text-center" style="background:#eee;border-color: #eee;"> {{ item.minutos | minutesToHours }} </td>
  			              <td class="text-center"> 
  			              	<i class="fa" :class="{'fa-check-square-o':item.facturar,'fa-square-o':!item.facturar}"  style="font-size:20px;line-height:20px;color:#ccc;"></i> 
  			              </td>
			            </tr>
		            	
		            </template>

		            <tr v-if="!list.loading && list.data.data && list.data.data.length == 0">
		            	<td colspan="8">No se encontraron resultados</td>
		            </tr>
		        	<tr v-if="list.loading">
		        		<td colspan="8">
		        			<pulse-loader :loading="list.loading"></pulse-loader>
		        		</td>
		        	</tr>            
	          	</tbody>
				<tfoot>
					<tr>
						<td colspan="8" class="text-right">
							<pagination v-if="list.data && list.data.total > 1" :limit="5"  :data="list.data" @pagination-change-page="changePage"></pagination>
						</td>
					</tr>	
				</tfoot>
	  		</table>
        </div>
        <div v-else class="box-body text-center">
        	<p>Para comenzar, cargue los parámetros y haga click en "Filtrar"</p>
        </div>
        <!-- /.box-body -->
  	</div>


</div>
</template>
<script>
import moment from 'moment'
import Vue from 'vue'
import { mapGetters,mapState } from 'vuex'
import { datepicker } from 'vue-strap'
import config from '../../../../config'
import Api from '../../../../api'
import vSelect from "vue-select"
import pagination from 'laravel-vue-pagination'
import { currency} from '../../../../filters'

export default {
	name: 'ImpresionesCA',
	components: {
		datepicker,
		vSelect,
		pagination
	},
	data() {
		return {
			canFilter: true,
			firstFilter: false,
			info: {
				abogados: []
			},
			apiUrl: '',
			uri: 'time-impresiones/abogado-cliente',			
			filtros: {
		        desde: moment().startOf('month').format('DD/MM/YYYY'),
		        hasta: moment().endOf('month').format('DD/MM/YYYY'),   
				user_id: null,
				page: 1
			},
			list: {
				pagination: {},
				data: {
					data: []
				},
				loading: false
			}
		}
	},
	mounted () {
		//this.getClientes()
		this.getFilters()
	}, 	
	methods: {
	    getFilters() {
	      let _this = this;
	      //this.getClientes()
	      Api.combos('time/abogados').then((resp) => {
	        _this.info.abogados = resp.data;
	        _this.canFilter = true;        
	        //_this.doFilter();
	      });     
	    },		
		doFilter() {
			this.firstFilter = true;
			this.filtros.page = 1;
			//this.filtros.user_id = this.authUser.id;
	    	this.errors.clear('filtros');

	    	if (this.filtros.desde === '') {
	    		this.addError('desde', 'Campo requerido','server','filtros');
	    	}
	    	if (this.filtros.hasta === '') {
	    		this.addError('hasta', 'Campo requerido','server','filtros');
	    	}
	    	if (!this.filtros.user_id) {
	    		//this.addError('user_id', 'Campo requerido','server','filtros');
	    	}

	    	/* f (this.filtros.desde && this.filtros.hasta) {
	    		let desde = moment(this.filtros.desde,'DD/MM/YYYY')
	    		let hasta = moment(this.filtros.hasta,'DD/MM/YYYY')
	    		if (hasta.diff(desde,'days') > 180)
	    		{
	    			this.errors.add('desde', 'El rango debe ser menor a 31 dias','server','filtros');
	    		}
	    	} */

			this.$validator.validateAll('filtros').then((result) => {
	     	    if (result && this.errors.items.length < 1) {
	     	    	this.getData();
		        } else {
		        	this.list.loading = false;
		        }
	      	},errors => {
	      		this.list.loading = false;
	      	});

		},
		changePage(page) {
			this.filtros.page = page;
			this.getData();
		},
		getData() {
 	    	this.list.loading = true;
	    	Api.post(this.uri,this.filtros).then((result) => {
	    		this.list.data.data.length = 0;
	    		this.list.data = result.data;

	    		this.list.loading = false;
	    	}, errors => {
	    		this.list.data.data.length = 0;
	    		this.list.data = [];
	    		this.list.loading = false;
	    	})			    		      	
		},
		print() {
			let queryString = Object.keys(this.filtros).map((key) => {
				if (this.filtros[key] != null) {
			    	return encodeURIComponent(key) + '=' + encodeURIComponent(this.filtros[key]);
			    }
			}).join('&');
			
			window.open(Laravel.webDomain.concat('/time/imp-abogado-cliente/?').concat(queryString));
		},		
	    getClientes (search, loading) {
	      //this.searchClientes(search, loading, this);
	      let vm = this
	      Api.combos('time/clientes/1').then(resp => {
	        this.$store.dispatch('setTimeClientesData',resp.data)
	         vm.info.clientes.data = resp.data
	         //loading(false)
	      }) 	      
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
	}

};	

</script>
<style scoped>
	.form-group{
		vertical-align:top;
	}
	.pagination {
		margin-top: 0;
		margin-bottom: 0; 
	}
</style>
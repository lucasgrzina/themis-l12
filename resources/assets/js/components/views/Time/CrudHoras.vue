<template>
<div>
	<filter-bar @time-horas:create="create" @time-horas:filter-set="doFilter" :filtros="filtros"></filter-bar>

	<div class="box box-warning" v-if="isTimeLevel(5) && filtros.estado === 'pendiente'">
		<div class="box-header with-border"><h3 class="box-title">Pendientes de vincular por usuario</h3></div>
		<div class="box-body table-responsive no-padding">
			<table class="table table-hover">
				<tbody>
					<tr><th>Usuario</th><th class="text-center">Cargas</th><th class="text-center">Minutos</th><th class="text-center">Más antigua</th></tr>
					<tr v-for="r in resumen" style="cursor:pointer" @click="filtrarUsuario(r.user_id)">
						<td>{{ r.name }}</td>
						<td class="text-center">{{ r.cantidad }}</td>
						<td class="text-center">{{ r.minutos }}</td>
						<td class="text-center">{{ r.mas_antigua | dateFormat }}</td>
					</tr>
					<tr v-if="resumen.length === 0"><td colspan="4" class="text-center">No hay cargas pendientes</td></tr>
				</tbody>
			</table>
		</div>
	</div>

	<timer-bar v-if="isTimeLevel(5) || isTimeLevel(1)" @show-pendientes="verPendientes"></timer-bar>

    <div class="box">
	    <div v-if="firstFilter" class="box-body table-responsive no-padding">
	        <table class="table">
	            <tbody>
		        	<tr>
		              <th class="text-center">Fecha</th>
		              <th>Minutos</th>
		              <th>Abogado</th>
		              <th>Cliente</th>
		              <th>Gestión</th>
		              <th>Referencia</th>
		              <th class="text-center">Facturar</th>
		              <th class="text-center"  v-if="!isTimeLevel(2)">Editar</th>
		            </tr>
		            <tr v-for="(item,index) in list.data.data"  v-if="!list.loading">
		            	<td class="text-center">{{ item.fecha | dateFormat }}</td>
		            	<td>{{ item.minutos }}</td>
		            	<td>{{ item.usuario ? item.usuario.name : '' }}</td>
		            	<td>{{ item.cliente ? item.cliente.nombre_completo : '' }}<span v-if="item.estado === 'pendiente'" class="label bg-yellow">Sin vincular</span></td>
		            	<td>{{ item.gestion ? item.gestion.nombre : '' }}</td>
		            	<td>{{ item.referencia }}</td>
		            	<td class="text-center">
		            		<a href="javascript:void(0)" v-if="isTimeLevel(5)" @click="facturar(item)" style="font-size:20px;line-height:20px;color: #605ca8;">
		            			<i class="fa" :class="{'fa-check-square-o':item.facturar,'fa-square-o':!item.facturar}"></i>
		            		</a>
		            		<i v-else class="fa" :class="{'fa-check-square-o':item.facturar,'fa-square-o':!item.facturar}"  style="font-size:20px;line-height:20px;color:#ccc;"></i>
		            	</td>
		            	<td class="text-center" v-if="isTimeLevel(1) || authUser.id == item.user_id">
		            		<button-type type="edit-list" @click="onAction('edit', item, index)"/>
		            		<button-type type="remove-list" @click="onAction('remove', item, index)"/>
		            	</td>
		            </tr>

		            <tr v-if="!list.loading && list.data && list.data.data.length == 0">
		            	<td colspan="11" class="text-center">No se encontraron resultados</td>
		            </tr>
		        	<tr v-if="list.loading">
		        		<td colspan="11">
		        			<pulse-loader :loading="list.loading"></pulse-loader>
		        		</td>
		        	</tr>            
	          	</tbody>
				<tfoot>
					<tr>
						<td colspan="4" v-if="list.data && list.data.total > 1">
							<strong>Total benf. informados:</strong> {{ list.data.total }}
						</td>
						<td colspan="4" class="text-right">
							<pagination v-if="list.data && list.data.total > 1" :limit="5"  :data="list.data" @pagination-change-page="changePage"></pagination>
						</td>
					</tr>	
				</tfoot>
	  		</table>
	    </div>
	    <div v-else class="box-body text-center">
	    	<p>Para comenzar, cargue los parámetros y haga click en "Filtrar"</p>
	    </div>
	</div>


	<modal v-model="amModal.show"  class="themis-modal" effect="fade" :backdrop="false">
	  <div slot="modal-header" class="modal-header">
	    <h4 class="modal-title">
	    	{{ amModal.title }}
	    </h4>
	  </div>
	  <template v-if="amModal.loaded">
		  <div slot="modal-body" class="modal-body" v-if="selectedItem" @keyup.enter="save()" @keyup.esc="closeAmModal()">
		  	<modal-errors :messages="amModal.errors"/>
		  	<div class="row">
				<div class="form-group col-sm-3" :class="{'has-error': errors.has('fecha')}">
					<label for="fecha">Fecha</label>
					<datepicker name="fecha" v-model="selectedItem.fecha_dp" :bootstrap-styling="true" :format="'dd/MM/yyyy'" :language="es" :use-utc="false"></datepicker>
					<span class="help-block" v-show="errors.has('fecha')">{{ errors.first('fecha') }}</span>
				</div>
				<div class="form-group col-sm-3" :class="{'has-error': errors.has('minutos')}">
					<label for="minutos">Minutos</label>
					<input type="number" name="minutos" v-model="selectedItem.minutos" min="5" step="5" class="form-control input-sm" v-validate="'required'" data-vv-validate-on="none">
					<span class="help-block" v-show="errors.has('minutos')">{{ errors.first('minutos') }}</span>
				</div>	
		  		<div class="form-group col-sm-6" :class="{'has-error': errors.has('cliente_id')}">
		  			<label for="cliente_id">Cliente</label>
		            <v-select
		              :value="selectedItem.cliente"
		              :clearSearchOnSelect="true"
		              :on-search="getClientes"
		              :options="info.clientes.data"
		              :on-change="onChangeCliente"
		              placeholder="Ingresa cliente"
		              label="nombre_completo"
		            >
		            </v-select> 
		            <span class="help-block" v-show="errors.has('cliente_id')">{{ errors.first('cliente_id') }}</span>
	            </div>
	            <div class="clearfix"></div>
				<div class="form-group col-sm-3">
					<label for="hora_inicio">Hora desde</label>
					<input type="time" name="hora_inicio" v-model="selectedItem.hora_inicio" @change="onHoraChange" class="form-control input-sm">
				</div>
				<div class="form-group col-sm-3">
					<label for="hora_fin">Hora hasta</label>
					<input type="time" name="hora_fin" v-model="selectedItem.hora_fin" @change="onHoraChange" class="form-control input-sm">
				</div>
				<div class="col-sm-6"><p class="help-block" style="margin-top:25px;">Opcional: al indicar desde/hasta se calculan los minutos (redondeo a 5).</p></div>
				<div class="clearfix"></div>
				<div class="form-group col-sm-6" :class="{'has-error': errors.has('gestion_id')}">
					<label for="gestion_id">Gestión</label>
					<select name="gestion_id" v-model="selectedItem.gestion_id" class="form-control">
						<option :value="null"></option>
						<option v-for="item in info.gestion" :value="item.id">{{ item.nombre }}</option>
					</select>
					<span class="help-block" v-show="errors.has('gestion_id')">{{ errors.first('gestion_id') }}</span>
				</div>	
				<div class="form-group col-sm-6" :class="{'has-error': errors.has('referencia')}">
					<label for="referencia">Referencia</label>
					<input type="text" v-model="selectedItem.referencia" list="referencias" class="form-control input-sm" :disabled="amModal.loadingReferencias" v-validate="'required'" data-vv-validate-on="none">
					<datalist id="referencias">
						<option v-for="item in info.referencias">{{ item.nombre }}</option>
					</datalist>				
					<span class="help-block" v-show="errors.has('referencia')">{{ errors.first('referencia') }}</span>
				</div>			  		
		  	</div>
		  	<div class="row">
				<div class="form-group col-sm-12" :class="{'has-error': errors.has('descripcion')}">
					<label for="descripcion">Descripcion</label>
					<textarea v-model="selectedItem.descripcion" class="form-control"></textarea>
					<span class="help-block" v-show="errors.has('descripcion')">{{ errors.first('descripcion') }}</span>
				</div>			  		
		  	</div>
		  </div>
		  <div slot="modal-footer" class="modal-footer">
		    <button-type type="close" @click="closeAmModal()"/>
		    <button-type type="save" :promise="save"/>
		  </div>
	  </template>
	  <template v-else>
	  	<pulse-loader :loading="true"></pulse-loader>
	  	<div slot="modal-footer" class="modal-footer"></div>
	  </template>
	</modal>
   
</div>
</template>

<script>
import Vue from 'vue'
import moment from 'moment'
import { modal } from 'vue-strap'
import FilterBar from './includes/FilterBarHoras'
import TimerBar from './includes/TimerBar'
import config from '../../../config'
import Api from '../../../api'
import vSelect from "vue-select"
import Datepicker from 'vuejs-datepicker'
import {es} from 'vuejs-datepicker/dist/locale'
import pagination from 'laravel-vue-pagination'
import { mapGetters,mapState } from 'vuex'
export default {
  name: 'ListadoTimeHoras',
  components: {
    modal,
    FilterBar,
    TimerBar,
    vSelect,
    Datepicker,
    pagination

  },
  data () {
	return {
		canFilter: true,
		firstFilter: false,
		selectedItem: null,
		es: es,
		list: {
			pagination: {},
			data: {
				data: []
			},
			loading: false
		},
		filtros: {
	        desde: moment().startOf('month').format('DD/MM/YYYY'),
	        hasta: moment().endOf('month').format('DD/MM/YYYY'),   
			page: 1,
			cliente_id: null,
			estado: null
		},
		resumen: [],		
		amModal: {
			title: 'Crear/Editar Hora',
			submited: false,
			show: false,
			errors: '',
			loadingReferencias: false, 
			loaded: false,
			saving:false
		},
		info: {
			clientes: {
			  selected: null,
			  data: []
			}, 
			gestion: [],
			referencias: [],
			responsables: []
		},		
		uri: 'time-horas/',
		actionPerm: 'time-horas',
		apiUrl: '',
	}
  },  
  computed: {
  	...mapState([
  		'authUser'
  		]),
    ...mapGetters([
      'isTimeLevel'
    ])      
  },    
	mounted () {
		this.apiUrl = config.serverURI + this.uri;
		this.$events.$on('time-timer:finished', this.onTimerFinished);
	},  

  beforeDestroy () {
  	this.$events.$off('time-timer:finished', this.onTimerFinished);
  },
  methods: {
	onTimerFinished() {
		if (this.firstFilter) this.getData();
	},
	onHoraChange() {
		let i = this.selectedItem.hora_inicio, f = this.selectedItem.hora_fin;
		if (!i || !f) return;
		let diff = moment(f, 'HH:mm').diff(moment(i, 'HH:mm'), 'minutes');
		if (diff > 0) {
			this.$set(this.selectedItem, 'minutos', Math.max(5, Math.ceil(diff / 5) * 5));
		}
	},
	filtrarUsuario(userId) {
		this.filtros.user_id = userId;
		this.doFilter();
	},
	getResumen() {
		if (!this.isTimeLevel(5)) return;
		Api.get(this.uri.concat('pendientes-resumen')).then(resp => { this.resumen = resp.data; });
	},
	doFilter() {
		this.firstFilter = true;
		this.filtros.page = 1;

    	this.errors.clear('filtros');

		this.$validator.validateAll('filtros').then((result) => {
     	    if (result) {
     	    	this.getData();
	        } else {
	        	this.list.loading = false;
	        }
      	},errors => {
      		this.list.loading = false;
      	});

	}, 
	getData() {
		if (this.filtros.estado === 'pendiente') this.getResumen();
    	this.list.loading = true;
    	Api.post(this.uri.concat('filtrar'),this.filtros).then((result) => {
    		this.list.data.data.length = 0;
    		this.list.data = result.data;

    		this.list.loading = false;
    	}, errors => {
    		this.list.data.data.length = 0;
    		this.list.data = [];
    		this.list.loading = false;
    	})			    		      	
	},	
	changePage(page) {
		this.filtros.page = page;
		this.getData();
	},		 	
    onAction (action, data, index) {
    	this.$store.dispatch('hideSuccessNotification')
		switch(action) {
			case 'edit':
			this.reset(_.clone(data, true));
			//this.amModal.show = true;
				break;
			case 'remove':
				this.remove(data);
				break;      		
		}
    },  
    create () {
    	this.reset({});
    },
    save () {
    	let _this = this;
    	return new Promise((resolve, reject) => {

	    	this.amModal.errors = ''

			this.errors.clear();

			if (!this.selectedItem.fecha_dp) {
				this.addError('fecha', 'Campo requerido','server');
			}
			if (!this.selectedItem.cliente_id) {
				this.addError('cliente_id', 'Campo requerido','server');
			}
			if (!this.selectedItem.gestion_id) {
				this.addError('gestion_id', 'Campo requerido','server');
			}
			if (!this.selectedItem.referencia) {
				this.addError('referencia', 'Campo requerido','server');
			}

			this.$validator.validateAll().then((result) => {
				if (result && this.errors.items.length < 1) {
					this.amModal.saving = true;
		        	//this.selectedItem.cliente_id = this.selectedCliente.id;
		        	Api.store(this.uri,this.selectedItem)
		        		.then((result) => {
		        				this.$store.dispatch('addTimeClienteData',this.selectedItem.cliente)
		        				this.getData();
		        				this.$store.dispatch('loadTimer');
		        				resolve()
		        				_this.closeAmModal();
		        				this.$store.dispatch('showSuccessNotification',result.data.message)

		        		}, (resp) => {
		        			console.debug(resp.message)
		        			this.amModal.errors = resp.message
		        			if (resp.fields) {
		        				for(var key in resp.fields) {
									this.addError(key, resp.fields[key][0], 'server'); 								    	
							   	}	        				
		        			}
		        			resolve();
		        		});
		        } else {
		        	reject();	
		        }
	      	},errors => {
		      		reject()
	      	})

    	});
    },
    remove (item) {
		this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
			.then((dialog) => {
	        	dialog.loading(true)
	        	Api.delete(this.uri,item)
	        		.then((result) => {
	        			dialog.close()
	        			this.$store.dispatch('showSuccessNotification',result.data.message)
	    				this.getData()
	    				this.$store.dispatch('loadTimer')
	        		},() => {
	        			dialog.close()
	        		})  			
			})    	
    },
    removeSelected (ids,dialog) {
    	dialog.loading(true)
    	Api.delete(this.uri.concat('eliminar-seleccion'),{ids: ids})
    		.then((result) => {
    			dialog.close()
    			this.$refs.list.$refs.vuetable.refresh()
				this.$store.dispatch('showSuccessNotification',result.data.message)
    		},() => {
    			dialog.close()
    		})
    },
    reset (item) {
    	let _this = this;

	    _this.selectedItem = _.assign({
	      	cliente_id: null,
	      	fecha_dp: moment().toDate(),
	      	fecha: moment().format('DD/MM/YYYY'),
	      	id: 0,
	      	gestion: null,
	      	referenciadb: null,
	      	referencia: 'Temas Varios',
	      	minutos: 15,
	      	hora_inicio: item.inicio ? moment(item.inicio).format('HH:mm') : null,
	      	hora_fin: item.fin ? moment(item.fin).format('HH:mm') : null
	    },item);
	    if (!_this.selectedItem.referencia) _this.selectedItem.referencia = 'Temas Varios';

	    this.clearErrors()
	    this.amModal.loaded = false;
	    this.amModal.show = true

		Api.combos('time/am-horas').then(resp => {
			_this.info.gestion = resp.data.gestion
			if (!_this.selectedItem.gestion_id) {
				let def = _.find(resp.data.gestion, g => g.nombre === resp.data.gestion_default)
				if (def) {
					_this.$set(_this.selectedItem, 'gestion_id', def.id)
				}
			}
			this.amModal.loaded = true;
			
		});
    },
    clearErrors() {
	    this.amModal.errors = ''
	    this.$validator.reset()    	
    },    
    closeAmModal () {
    	this.amModal = _.assign(this.amModal,{
    		show: false,
    		submited: false
    	})
    	this.clearErrors();
    	this.resetInfo();
    	//this.reset()
    },
    getClientes (search, loading) {
      this.searchClientes(search, loading, this);
    },
    searchClientes: _.debounce((search, loading, vm) => {
        loading(true)
        Api.combos('time/clientes?search=' + search).then(resp => {
           vm.info.clientes.data = resp.data
           loading(false)
        }).catch(() => {
           vm.info.clientes.data = []
           loading(false)
        })
    }, 500), 
    onChangeCliente(item) {
    	let _this = this
		if (item) {
			_this.selectedItem.cliente = item
			_this.selectedItem.cliente_id = item.id  
			_this.amModal.loadingReferencias = true;
			Api.combos('time/referencias/'+_this.selectedItem.cliente_id).then(resp => {
				_this.info.referencias = resp.data.referencias
				_this.amModal.loadingReferencias = false;
			});
		} else {
			this.selectedItem.cliente = null
			this.selectedItem.cliente_id = null
			this.info.referencias.length = 0;
		}
    },
    resetInfo() {
	    this.info.referencias.length = 0;
	    this.info.clientes.length = 0;
    },
    verPendientes() {
    	this.filtros.estado = 'pendiente';
    	this.doFilter();
    },
    facturar(item) {
    	item.facturar = !item.facturar;
    	Api.post(this.uri.concat('facturar'),item).then(resp => {
    		//item.facturar = !item.facturar; 
    	},error => {
    		console.debug(error);
    		item.facturar = !item.facturar; 
    	});
    }      
  }
}
</script>
<style>
  .top-actions{
    display:inline-block;
    float:right;
  }
</style>
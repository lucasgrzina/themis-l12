<template>
	<div>
		<c-u-cliente :selectedItem="selectedItem" :show="modalCliente.show" :actionPerm="'clientes'" :uri="'clientes/'" @clientes:saved="closeModalCliente" @clientes:back="closeModalCliente" :activeTab="modalCliente.activeTab"></c-u-cliente>		
		
		<div class="box" v-show="!modalCliente.show">
            <div class="box-header">
              <h3 class="box-title">Avisos</h3>
				<div class="box-tools">
					<button-type type="refresh" @click="getList()"></button-type>            	
				</div>	
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
            	<div class="table-responsive">
	            <table class="table table-striped">
	            	<tbody>
	        			<tr>
		                  <th style="width: 150px"><a :class="{'order_by': filtros.orderBy === 'fecha','sorteable':true}" href="javascript:void(0)" @click="changeOrder('fecha')">Fecha/Hora</a></th>
		                  <th><a :class="{'order_by': filtros.orderBy === 'clientes|nombre_completo','sorteable':true}" href="javascript:void(0)" @click="changeOrder('clientes|nombre_completo')">Cliente</a></th>
		                  <th><a :class="{'order_by': filtros.orderBy === 'users|name','sorteable':true}" href="javascript:void(0)" @click="changeOrder('users|name')">Creador</a></th>
						  <th><a :class="{'order_by': filtros.orderBy === 'type_id','sorteable':true}" href="javascript:void(0)" @click="changeOrder('type_id')">Destinatario</a></th>		                  
		                  <th>Motivo</th>
		                  <th>Estado</th>
		                  <th></th>
	                	</tr>
	                	<template v-if="!list.loading">
		                	<tr v-for="(value,index) in authUser.avisos">
			                  <td>{{ value.fecha | datetimeFormat(false,inputFormatDate) }}</td>
			                  <td>{{ value.cliente.nombre_completo }}</td>
			                  <td>{{ value.user.name }}</td>
			                  <td v-if="value.type == 'A'">{{ aAreas[value.type_id] }}</td>
			                  <td v-else>{{ aUsuarios[value.type_id] }}</td>
			                  <td>{{ value.motivo }}</td>
			                  <td v-html="$options.filters.tagAssoc(value.status,info.status)"></td>
			                  <td style="text-align: right;" nowrap="">
								<button-type v-can="['clientes:R']" type="view-list" @click="onAction('view', value, index)"/>
								<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="edit-list" @click="onAction('posponer', value, index)" v-if="puedoEditar(value)"/>
								<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="descartar-list" @click="onAction('descartar', value, index)" v-if="!value.descartado && puedoDescartar(value)"/>
			                  </td>
			            	</tr>
			            </template>
		            	<tr v-else="list.loading">
		            		<td colspan="7">
		            			<pulse-loader :loading="list.loading"></pulse-loader>
		            		</td>
		            	</tr>		            	
		        	</tbody>
	      		</table>
	      		</div>
            </div>
            <!-- /.box-body -->
      	</div>	

		<modal v-model="posponerModal.show"  class="themis-modal" effect="fade" :backdrop="false" :short="true">
		  <div slot="modal-header" class="modal-header">
		    <h4 class="modal-title">
		    	{{ posponerModal.title }}
		    </h4>
		  </div>
		  <div slot="modal-body" class="modal-body" v-if="selectedItem" @keyup.enter="posponer()" @keyup.esc="closePosponerModal()">
		  	<modal-errors :messages="posponerModal.errors"/>
		  	<form :data-vv-scope="'avisos'">
			  	<div class="row">
					<div class="form-group col-sm-12">
						<label for="fecha_original">Fecha Original</label><br>
						<span>{{ posponerModal.fechaAlEditar | datetimeFormat(false,inputFormatDate)}}</span>
					</div>
					<div class="form-group col-sm-12" :class="{'has-error': errors.has('avisos.fecha')}">
						<label for="fecha">Fecha Nueva</label><br>
						<datetime v-model="selectedItem.fecha" 
								input-class="form-control" 
								type="datetime" zone="America/Buenos_Aires" value-zone="America/Buenos_Aires"
								:minute-step="30"
  								:min-datetime="now"></datetime>
						<!--datepicker name="fecha" v-model="selectedItem.fecha" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker-->
						<span class="help-block" v-show="errors.has('avisos.fecha')">{{ errors.first('avisos.fecha') }}</span>
					</div>				
				</div>
			</form>
		  </div>
		  <div slot="modal-footer" class="modal-footer">
		    <button-type type="close" @click="closePosponerModal()"/>
		    <button-type type="save" @click="posponer()"/>
		  </div>
		</modal>      			
	</div>
</template>
<script>
import { mapState, mapGetters } from 'vuex'
import moment from 'moment'
import Vue from 'vue'
import { datepicker,modal } from 'vue-strap'
import config from '../../../config'
import Api from '../../../api'
import CUCliente from '../Clientes/includes/CU.vue'
import { Settings, DateTime } from 'luxon'
Settings.defaultLocale = 'es'
Settings.defaultZoneName = 'America/Buenos_Aires'

export default {
	name: 'Avisos',
	components: {
		datepicker,
		CUCliente,
		modal
	},

	data () {
		return {
			title: 'Avisos',
			now: moment().toISOString(),
			inputFormatDate: moment.ISO_8601,
			info: {
				areas: [],
				usuarios: [],
				status: {"DESCARTADO": ["CUMPLIDO","bg-navy"],"VENCIDO":["VENCIDO","bg-red-active"],"PENDIENTE":["PENDIENTE","bg-aqua"]}
			},			
			list: {
				loading: true,
				data: []
			},	
			filtros: {
				page: 1,
				orderBy: 'fecha',
				sortedBy: 'asc'
			},				
			selectedItem: null,
			selectedIndex: -1,
			modalCliente: {
				show: false,
				activeTab: 0,
			},
			posponerModal: {
				title: 'Nuevo aviso',
				submited: false,
				show: false,
				errors: '',
				edit: false,
				fechaAlEditar: ''		
			},		
			messages: '', 
			uri: 'avisos-clientes/',
			actionPerm: 'clientes',
			apiUrl: '',
			//uri: this.baseUri.concat('/avisos-clientes/pendientes/')
		}
	},  
	computed: {
		...mapState([
		  'authUser'
		]),
		...mapGetters([
			'isMember','findAreasByIds','isResponsableArea'
		]),
		aAreas: function() {
			let _areas = {};
			for(let i =0; i < this.info.areas.length; i++) {
				_areas[this.info.areas[i].id] = this.info.areas[i].nombre;
			}
			return _areas;
		},
		aUsuarios: function() {
			let _usuarios = {};
			for(let i =0; i < this.info.usuarios.length; i++) {
				_usuarios[this.info.usuarios[i].id] = this.info.usuarios[i].name;
			}
			return _usuarios;
		} 				 
	},  
	mounted () {
		let _this = this;
		this.apiUrl = config.serverURI + this.uri;
		Api.combos('solapa-avisos').then((resp) => {
			_this.info.areas = resp.data.areas;
			_this.info.usuarios = resp.data.usuarios;
			this.getList();
		});			

	},  
	methods: {
	    onAction (action, data, index) {
	    	this.$store.dispatch('hideSuccessNotification')
			switch(action) {
				case 'view':
					this.selectedIndex = index;
					this.reset(_.clone(data.cliente, true));
					this.modalCliente.show = true;
					break;
				case 'posponer':
					this.selectedIndex = index;
					this.resetPosponer(_.clone(data, true));
					this.posponerModal.edit = true;
					this.posponerModal.fechaAlEditar = _.clone(data.fecha);					
					this.posponerModal.show = true;
				//this.reset(_.clone(data, true));
				//this.amModal.show = true;
					break;
				case 'descartar':
					this.descartar(data,index);
					break;      		

			}
	    },  
	    getList () {
	    	this.list.loading = true;
	    	this.$store.dispatch('getAvisos',this.filtros)
	    		.then(() => {
	    			this.list.loading = false;
	    		});  
	    },    
		changeOrder(field) {
			if (this.filtros.orderBy === field) {
				this.filtros.sortedBy = (this.filtros.sortedBy === 'asc' ? 'desc' : 'asc');
			} else {
				this.filtros.orderBy = field;
				this.filtros.sortedBy = 'asc';
			}
			this.getList();
		},	    
	    reset (item) {
		    this.selectedItem = _.assign({
		    },item);
		    //this.$store.dispatch('hideSuccessNotification')
	    },
	    clearErrors() {
		    this.amModal.errors = ''
		    //this.$validator.reset()    	
	    },
	    closeModalCliente () {
	    	this.modalCliente = _.assign(this.modalCliente,{
	    		show: false,
	    	})
	    	this.selectedItem = null;	    	
	    	this.selectedIndex = -1;
	    	//this.reset()
	    },
	    resetPosponer (item) {
		    this.selectedItem = item;
		    this.clearErrorsPosponer()   	
	    },
	    clearErrorsPosponer() {
		    this.posponerModal.errors = ''
		    this.errors.clear('avisos')
		    this.$validator.reset()    	
	    },    
	    closePosponerModal () {
	    	this.posponerModal = _.assign(this.posponerModal,{
	    		show: false,
	    		submited: false
	    	})
	    	this.selectedIndex = -1;
	    	this.selectedItem = null;
	    	this.clearErrorsPosponer()
	    },
	    fechaPasada() {
	    	return moment(this.hoy).isAfter(moment(this.selectedItem.fecha,'DD/MM/YYYY').format('YYYY-MM-DD'))
	    },	    
	    posponer() {
	    	this.errors.clear('avisos');

	    	if (this.selectedItem.fecha === '') {
	    		this.addError('fecha', 'Campo requerido','server','avisos');
	    	}/* else {
	    		if (this.posponerModal.fechaAlEditar !== this.selectedItem.fecha && this.fechaPasada()) {
	    			this.addError('fecha', 'La fecha ingresada no puede ser anterior a hoy','server','avisos');
	    		}
	    	} */ 	

			this.$validator.validateAll('avisos').then((result) => {
		        if (result && this.errors.items.length < 1) {
			    	Api.store(this.uri.concat('posponer/'),this.selectedItem)
			    		.then((result) => {
			    				this.$store.dispatch('showSuccessNotification',result.data.message)
			    				this.getList();
			    				this.closePosponerModal();
			    		}, (resp) => {
			    			this.message = resp.message
			    			this.$store.dispatch('showErrorNotification',this.message)
			    			//console.debug(resp);
			    			/*if (resp.fields) {
			    				for(var key in resp.fields) {
									this.addError(key, resp.fields[key][0], 'server'); 								    	
							   	}	        				
			    			}*/
			    		});	        	
		        }
	      	}); 
	    },
	    puedoEditar(aviso) {
	    	/*Puedo editar si:
			Area: Soy creador o soy responsable del area.
			*/
			if (aviso.user_id === this.authUser.id || (aviso.type === 'U' && aviso.type_id === this.authUser.id)) {
				return true;
			} else if (aviso.type === 'A' && this.isResponsableArea(aviso.type_id)) {
				return true;
			}
	    },
	    puedoDescartar(aviso) {
			if ((aviso.type === 'U' && aviso.type_id === this.authUser.id)) {
				return true;
			} else if (aviso.type === 'A' && this.isResponsableArea(aviso.type_id)) {
				return true;
			}
	    },
	    puedoEliminar(aviso) {
	    	return (aviso.user_id === this.authUser.id) || (aviso.type === 'A' && this.isResponsableArea(aviso.type_id))
	    },	    
	    soyDestinatario(aviso) {
	    	return (aviso.user_id === this.authUser.id) || (aviso.type === 'U' && aviso.type_id === this.authUser.id) || (aviso.type === 'A' && this.isMember(aviso.type_id))
	    },
	    soyCreador(aviso) {
	    	return (aviso.user_id === this.authUser.id)
	    },	    
	    descartar (item,index) {
			this.$dialog.confirm('¿Deséa marcar el aviso como cumplido?',{loader: true})
				.then((dialog) => {
		        	dialog.loading(true)
		        	Api.store(this.uri.concat('descartar/'),{id:item.id})
		        		.then((result) => {
		        			dialog.close()
		        			this.$store.dispatch('descartarAviso',index)
		        			this.$store.dispatch('showSuccessNotification',result.data.message)
		    				//this.avisos.splice(index,1)
		    				//this.getList();
		        		},() => {
		        			dialog.close()
		        		})  			
				})    	
	    }, 
	},
	filters: {
		tagAssoc: function(value,statuses) {
			return Vue.options.filters.tagAssoc(value,statuses)
		}
	}  
};		
</script>
<template>
	<div>
		<div class="box">
            <div class="box-header">
              <h3 class="box-title">{{ title }}</h3>
              <div class="box-tools">
			    <button-type v-can="[actionPerm+':U',actionPerm+':C']" type="new" @click="create"/>              	
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
            	<div class="table-responsive">
	            <table class="table table-striped">
	            	<tbody>
	        			<tr>
		                  <th style="width: 150px">Fecha/Hora</th>
		                  <th>Creador</th>
		                  <th>Destinatario</th>
		                  <th>Motivo</th>
		                  <th>Estado</th>
		                  <th></th>
	                	</tr>
	                	<template v-if="!list.loading">
		                	<tr v-for="(value,index) in list.data">
			                  <td>{{ value.fecha | datetimeFormat(false,inputFormatDate) }}</td>
			                  <td>{{ value.user.name }}</td>

			                  <td v-if="value.type == 'A'">{{ aAreas[value.type_id] }}</td>
			                  <td v-else>{{ aUsuarios[value.type_id] }}</td>
			                  <td>{{ value.motivo }}</td>
			                  <td v-html="$options.filters.tagAssoc(value.status,info.status)"></td>
			                  <td style="text-align: right;" nowrap="">
									<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="edit-list" @click="onAction('edit', value, index)" v-if="puedoEditar(value)"/>
									<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="descartar-list" @click="onAction('descartar', value, index)" v-if="!value.descartado && puedoDescartar(value)"/>
									<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="remove-list" @click="onAction('remove',value, index)" v-if="puedoEliminar(value)"/>
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
	
		<modal v-model="amModal.show"  class="themis-modal" effect="fade" :backdrop="false">
		  <div slot="modal-header" class="modal-header">
		    <h4 class="modal-title">
		    	{{ amModal.title }}
		    </h4>
		  </div>
		  <div slot="modal-body" class="modal-body" v-if="selectedItem" @keyup.esc="closeAmModal()">
		  	<modal-errors :messages="amModal.errors"/>
		  	<form @submit.prevent="save()" :data-vv-scope="'avisos'">
		  		<fieldset class="transparente">
				  	<div class="row">
						<div class="form-group col-sm-6" :class="{'has-error': errors.has('avisos.fecha')}">
							<label for="fecha">Fecha</label><br>
							<!--datepicker name="fecha" v-model="selectedItem.fecha" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker-->
							<datetime v-model="selectedItem.fecha" 
										input-class="form-control" 
										type="datetime" zone="America/Buenos_Aires" value-zone="America/Buenos_Aires"
										:minute-step="30"
		  								:min-datetime="now"></datetime>
							<span class="help-block" v-show="errors.has('avisos.fecha')">{{ errors.first('avisos.fecha') }}</span>
						</div>
						<div class="form-group col-sm-6"  v-if="selectedItem.id > 0">
							<label for="nombre" style="display:block;">Creador</label>
							<input type="text" v-model="selectedItem.user.name" :disabled="true" class="form-control"> 
						</div>
					</div>
					
					<div class="row" >
						<div class="form-group col-sm-6" :class="{'has-error': errors.has('avisos.para')}">
							<label for="para">Para</label><br>
							<input type="radio" id="r_type_u" :value="'U'" v-model="selectedItem.type" @change="onChangeType()">&nbsp;&nbsp;<label for="r_type_u">Usuario</label><br>
							<select v-model="selectedItem.type_id" :disabled="selectedItem.type === 'A'" class="form-control">
								<option value="0"></option>
								<option v-for="item in info.usuarios" v-if="item.visible" :value="item.id">{{ item.name }}</option>
							</select>					
							<input type="radio" id="r_type_a" :value="'A'" v-model="selectedItem.type" @change="onChangeType()">&nbsp;&nbsp;<label for="r_type_a">Área</label><br>
							<select v-model="selectedItem.type_id" :disabled="selectedItem.type === 'U'" class="form-control">
								<option value="0"></option>
								<option v-for="item in info.areas" :value="item.id">{{ item.nombre }}</option>
							</select>
							<span class="help-block" v-show="errors.has('avisos.para')">{{ errors.first('avisos.para') }}</span>
						</div>				
					</div>
					

					<div class="row">
						<div class="form-group col-sm-12"  :class="{'has-error': errors.has('avisos.motivo')}">
							<label for="motivo">Motivo</label>
							<textarea name="motivo" v-model="selectedItem.motivo" class="form-control" v-validate="'required'" data-vv-validate-on="none"></textarea>
							<span class="help-block" v-show="errors.has('avisos.motivo')">{{ errors.first('avisos.motivo') }}</span>
						</div>			
					</div>
				</fieldset>
			</form>
		  </div>
		  <div slot="modal-footer" class="modal-footer">
		    <button-type type="close" @click="closeAmModal()"/>
		    <button-type type="save" :promise="save"/>
		  </div>
		</modal>


	</div>
</template>
<script>
import moment from 'moment'
import Vue from 'vue'
import { modal,datepicker } from 'vue-strap'
import config from '../../../../config'
import Api from '../../../../api'
import { mapState, mapGetters } from 'vuex'
import { Settings, DateTime } from 'luxon'
Settings.defaultLocale = 'es'
Settings.defaultZoneName = 'America/Buenos_Aires'
console.debug( DateTime.local().toISO());

export default {
	name: 'SolapaAvisos',
	components: {
		modal
	},
	props: {
	  	baseUri: {
	  		type: String,
	  		required: true
	  	},
	  	clienteId: {
	  		type: Number,
	  		required: true
	  	},
	  	actionPerm: {
	  		type: String,
	  		required: true
	  	},
	  	active: {
	  		type: Boolean,
	  		default: function() {
	  			return false;
	  		}
	  	}
	},
	data () {
		return {
			title: 'Avisos',
			firstLoad: true,
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
			selectedItem: null,
			selectedIndex: -1,
			amModal: {
				title: 'Nuevo aviso',
				loading: true,
				edit: false,
				fechaAlEditar: '',
				submited: false,
				show: false,
				errors: '',

			},
			messages: '', 
			apiUrl: '',
			uri: this.baseUri.concat(this.clienteId).concat('/avisos/')
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
		this.apiUrl = config.serverURI + this.uri;
		//this.getList();
	},  
	methods: {
	    onAction (action, data, index) {
	    	this.$store.dispatch('hideSuccessNotification')
			switch(action) {
				case 'edit':
				this.selectedIndex = index;
				this.reset(_.clone(data, true));
				this.amModal.edit = true;
				this.amModal.fechaAlEditar = _.clone(data.fecha);
				this.amModal.show = true;
					break;
				case 'remove':
					this.remove(data,index);
					break;      		
				case 'descartar':
					this.descartar(data,index);
					break;      		

			}
	    },  
	    getList () {

	    	if (this.clienteId) {
		    	this.list.loading = true;
		    	Api.get(this.uri).then((result) => {
		    		
		    		this.list.data.length = 0;
		    		this.list.data = result.data;

		    		this.list.loading = false;
		    		this.firstLoad = false;
		    	})
	    	}
	    },    
	    create () {
	    	this.reset();
	    	this.amModal.edit = false
	    	this.amModal.fechaAlEditar = '';
	    	this.amModal.show = true
	    },  
	    reset (item) {
		    this.selectedItem = item || {
		    	id: 0,
				user: null,
				user_id: 0,
				cliente_id: this.clienteId,
				motivo: null,
				fecha: this.now,
				descartar: false,
				type: 'U',
				type_id: this.authUser.id
		    }

		    //this.cargarAreas();
		    //console.debug(this.selectedItem.fecha)
		    this.clearErrors()   	
	    },
	    clearErrors() {
		    this.amModal.errors = ''
		    this.errors.clear('avisos')
		    this.$validator.reset()    	
	    },    
	    closeAmModal () {
	    	this.amModal = _.assign(this.amModal,{
	    		show: false,
	    		submited: false,
	    		edit: false,
	    		fechaAlEditar: ''
	    	})
	    	this.clearErrors();
	    	this.selectedIndex = -1;
	    	this.selectedItem = null;
	    },
	    save() {
	    	var _this = this;
	    	return new Promise((resolve, reject) => {
		    	
		    	_this.errors.clear('avisos');

		    	if (_this.selectedItem.fecha === '') {
		    		_this.addError('fecha', 'Campo requerido','server','avisos');
		    	}
				if (_this.selectedItem.type_id < 1) {
					_this.addError('para', 'Campo requerido','server','avisos');	
				}

				_this.$validator.validateAll('avisos').then((result) => {
			        if (result && _this.errors.items.length < 1) {
				    	Api.store(_this.uri,_this.selectedItem)
				    		.then((result) => {
				    				_this.$store.dispatch('showSuccessNotification',result.data.message)
				    				_this.getList();
				    				resolve();
				    				_this.closeAmModal();
				    				_this.$store.dispatch('getAvisos')
				    		}, (resp) => {
				    			_this.message = resp.message
				    			_this.$store.dispatch('showErrorNotification',_this.message)
				    			reject();
				    		});	        	
			        } else {
			        	reject();	
			        }
			        
			        //return;
		      	}); 	    		
	    	});

	    },
	    remove (item,index) {
			this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
				.then((dialog) => {
		        	dialog.loading(true)
		        	Api.delete(this.uri,item)
		        		.then((result) => {
		        			dialog.close()
		        			this.$store.dispatch('showSuccessNotification',result.data.message)
		        			this.$store.dispatch('getAvisos')
		    				//this.avisos.splice(index,1)
		    				this.getList();
		        		},() => {
		        			dialog.close()
		        		})  			
				})    	
	    },    
	    descartar (item,index) {
			this.$dialog.confirm('¿Deséa marcar el aviso como cumplido?',{loader: true})
				.then((dialog) => {
		        	dialog.loading(true)
		        	Api.store(this.uri.concat('descartar/'),{id:item.id})
		        		.then((result) => {
		        			dialog.close()
		        			this.$store.dispatch('showSuccessNotification',result.data.message)
		        			this.$store.dispatch('getAvisos')
		    				//this.avisos.splice(index,1)
		    				this.getList();
		        		},() => {
		        			dialog.close()
		        		})  			
				})    	
	    }, 
	    back() {
	    	this.$emit('clientes:back','avisos')
	    },
	    cargarAreas() {
		  	let _mis_areas = [];

		  	for(let i = 0; i < this.authUser.areas.length; i++) {
		  		_mis_areas.push(this.authUser.areas[i].area_id);
		  	}

		  	if (this.selectedItem.type === 'A' && this.selectedItem.type_id && _mis_areas.indexOf(this.selectedItem.type_id) === -1) {
		  		_mis_areas.push(this.selectedItem.type_id);
		  	}

		  	this.info.areas = this.findAreasByIds(_mis_areas);	    	
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
	    	return (aviso.user_id === this.authUser.id)
	    },
	    soyDestinatario(aviso) {
	    	return (aviso.user_id === this.authUser.id) || (aviso.type === 'U' && aviso.type_id === this.authUser.id) || (aviso.type === 'A' && this.isMember(aviso.type_id))
	    },
	    soyCreador(aviso) {
	    	return (aviso.user_id === this.authUser.id)
	    },
	    onChangeType() {
	    	if (this.selectedItem.type === 'U') {
	    		this.selectedItem.type_id = 0;
	    	} else {
	    		this.selectedItem.type_id = 0;
	    	}
	    }        	
	},
	filters: {
		tagAssoc: function(value,statuses) {
			return Vue.options.filters.tagAssoc(value,statuses)
		}
	},
	watch: {
	    active(newVal) {
	    	var _this = this;
			if (newVal && this.firstLoad) {
				this.getList();
				Api.combos('solapa-avisos').then((resp) => {
					_this.info.areas = resp.data.areas;
					_this.info.usuarios = resp.data.usuarios;
					_this.loading = false;
				});		      	
			}
	    }
    } 
};		
</script>
<style>
	/*.progress-description.del{
		text-align: center;
	}
	.progress-description.del a{
		color: #ffffff!important;
	}
	.progress-description.del a i{
		display: block;
	}*/
</style>
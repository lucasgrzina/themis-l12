<template>
	<div>
		<form id="frm" @submit.prevent="save()" :data-vv-scope="'tramites'">
			<div class="row">
				<div class="col-xs-12">
					<modal-errors :messages="cu_errors"></modal-errors>
				</div>
			</div>		
			<fieldset class="transparente" :disabled="!canSave">
				<div class="row">
					<div class="form-group col-sm-4">
						<label for="nombre_completo">Nro. correlativo</label><br>
						{{ selectedItem.cliente.nro_correlativo }}
					</div>
					<div class="form-group col-sm-4">
						<label for="nombre_completo">Nombre de la soc.</label><br>
						{{ selectedItem.cliente.nombre_completo }}
					</div>
					<div class="form-group col-sm-4">
						<label for="nombre_completo">Tipo de Trámite</label><br>
						{{ selectedItem.requerimiento.tipo_tramite.nombre }}
					</div>					
				</div>
				<div class="clearfix"></div>
				<div class="row">
					<div class="form-group col-sm-3" :class="{'has-error': errors.has('tramites.expediente')}">
						<nro-expediente :areaId="selectedItem.area_id" v-model="selectedItem.expediente" :cuit="selectedItem.cuit"></nro-expediente>
						<span class="help-block" v-show="errors.has('tramites.expediente')">{{ errors.first('tramites.expediente') }}</span>
					</div>
					<div class="form-group col-sm-3" :class="{'has-error': errors.has('tramites.fecha_inicio')}">
						<label for="fecha_inicio">Fecha Inicio</label><br>
						<datepicker name="fecha_inicio" v-model="selectedItem.fecha_inicio" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
						<span class="help-block" v-show="errors.has('tramites.fecha_inicio')">{{ errors.first('tramites.fecha_inicio') }}</span>
					</div>
					<div class="form-group col-sm-3" :class="{'has-error': errors.has('tramites.estado_tramite_id')}">
						<label for="estado_tramite_id">Estado</label>
						<v-select
							v-model="selectedItem.estado"
							:options="info.estado_tra"
							:on-change="onChangeEstadoTra"
							placeholder="Ingresa el estado"
							label="nombre"
						>
						</v-select>
						<span class="help-block" v-show="errors.has('tramites.estado_tramite_id')">{{ errors.first('tramites.estado_tramite_id') }}</span>
					</div>		
					<div v-if="ingresarVto()"  class="form-group col-sm-3" :class="{'has-error': errors.has('tramites.fecha_vto')}">
						<label for="fecha_vto">Fecha Vto.</label><br>
						<datepicker name="fecha_vto" v-model="selectedItem.fecha_vto" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
						<span class="help-block" v-show="errors.has('tramites.fecha_vto')">{{ errors.first('tramites.fecha_vto') }}</span>
					</div>	
				</div>

			</fieldset>
			<div class="row" style="margin-top: 15px;">
					<div class="col-xs-6">
						<label for="archivar" v-if="canSave">Archivar</label>
						<div style="display:inline-block;vertical-align:middle;margin-left:5px;" v-if="canSave">
							<radio-button-group v-model="selectedItem.archivar"></radio-button-group>					
						</div>
					</div>			  		
					<div class="col-xs-6 text-right">
				  		<button-type type="close" @click="close()" />
				  		<button-type type="save" :promise="save" v-if="canSave"/>
				  	</div>
			</div>
        </form>		
	</div>
</template>
<script>
	import moment from 'moment'
	import Vue from 'vue'
	import { modal,datepicker,checkbox,radio } from 'vue-strap'
	import config from '../../../../config'
	import Api from '../../../../api'
	import vSelect from "vue-select"
	import NroExpediente from './NroExpediente'
	import ExpJud from './ExpJud'
	import { mapState } from 'vuex'
	import RadioButtonGroup from '../../../shared/buttons/RadioButtonGroup'
	
export default {
	name: 'TramiteSocietario',
	components: {
		modal,
		vSelect,
		datepicker,
		checkbox,
		radio,
		NroExpediente,
		ExpJud,
		RadioButtonGroup
	},
	props: {
		canSave: {
			type: Boolean,
			default: function() {
				return false;
			}
		},
		baseUri: {
			type: String,
			required: true
		},
		selectedItem: {
			type: Object,
			required: true
		},
		actionPerm: {
			type: String,
			required: true
		},
		info: {
			type: Object,
			required: true
		}
	},
	data () {
		return {
			submited: false,
			cu_errors: '',
			saving: false,
			messages: '', 
			apiUrl: '',
			uri: this.baseUri,
			mounted: false,
			valorArchivar: false
		}
	}, 
  computed: {
    ...mapState([
      'authUser'
    ])
  },   
mounted () {
	this.uri = this.uri.concat(this.selectedItem.cliente_id).concat('/tramites/');
	this.apiUrl = config.serverURI + this.uri;
	this.mounted = true;
	if (this.selectedItem.archivar !== true) {
		this.selectedItem.archivar = false;
	}
	this.valorArchivar = this.selectedItem.archivar;
},  
methods: {	
    clearErrors() {
	    this.cu_errors = ''
	    this.$validator.reset()    	
    },    
    close () {
    	this.submited = false;
    	this.clearErrors()
    	this.$emit('clientes:tramites-close')
    },
    save() {
	  	return new Promise((resolve, reject) => {
	    	this.errors.clear('tramites');
	    	if (!this.selectedItem.expediente) {
	    		this.addError('expediente', 'Campo requerido','server','tramites');
	    	}

	    	if (this.selectedItem.fecha_inicio === '') {
	    		this.addError('fecha_inicio', 'Campo requerido','server','tramites');
	    	}

	    	if (!this.selectedItem.estado_tramite_id) {
	    		this.addError('estado_tramite_id', 'Campo requerido','server','tramites');
	    	}

    		if (this.ingresarVto() && this.selectedItem.fecha_vto === '') {
    			this.addError('fecha_vto', 'Campo requerido','server','tramites');
    		}	

			this.$validator.validateAll('tramites').then((result) => {
	     	    if (result && this.errors.items.length < 1) {
	     	    	this.saving = true;
			    	Api.store(this.uri,this.selectedItem)
			    		.then((result) => {
		    				this.$emit('clientes:tramites-saved',result.data.data)
		    				this.close(true);	    					
		    				this.saving = false; 
		    				resolve();
			    		}, (resp) => {
			    			this.saving = false;
			    			this.message = resp.message
			    			this.$store.dispatch('showErrorNotification',this.message)
			    			reject();
			    			this.saving = false; 
			    		});	
		    		      	
		        } else {
		        	reject();	
		        }
	      	},errors => {
	      		resolve()
	      	});
		});
    },    

	onChangeEstadoTra(item) {
		this.selectedItem.estado = item
		this.selectedItem.estado_tramite_id = (item ? item.id : null)	
	},	
	ingresarVto() {
		return (this.selectedItem.estado_tramite_id === 5143 && this.selectedItem.requerimiento.tipo_tramite_id === 8126);
	}
  }
};		
</script>
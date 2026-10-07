<template>
	<div>
		<form id="frm" @submit.prevent="save()" :data-vv-scope="'tramites'">
			<div class="row">
				<div class="col-xs-12">
					<modal-errors :messages="cu_errors"></modal-errors>
				</div>
			</div>		
			<fieldset class="transparente" :disabled="!canSave">
				<div class="clearfix"></div>	
				<div class="row">
					<div class="form-group col-sm-8">
						<label for="autos">Autos</label><br>
						{{ selectedItem.requerimiento.autos }}
					</div>
					<div class="form-group col-sm-4">
						<label for="parte">Parte</label><br>
						{{ selectedItem.requerimiento.nombre_parte }}
					</div>
				</div>
				<div class="row">
					<div class="form-group col-sm-6" :class="{'has-error': errors.has('tramites.fecha_inicio')}">
						<label for="fecha_inicio">Fecha Inicio</label><br>
						<datepicker name="fecha_inicio" v-model="selectedItem.fecha_inicio" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
						<span class="help-block" v-show="errors.has('tramites.fecha_inicio')">{{ errors.first('tramites.fecha_inicio') }}</span>
					</div>
					<div class="form-group col-sm-6" :class="{'has-error': errors.has('tramites.estado_tramite_id')}">
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
				</div>	
				<template>
					<div class="row">
						<div class="col-sm-12">
							<fieldset>
								<template>
									<div class="r-tipo-favorable row">
										<div class="col-xs-12 col-sm-6">
											<div class="form-group" :class="{'has-error': errors.has('tramites.tipo_resolucion')}">
												<input type="radio" id="r_tipo_res" :value="0" v-model="selectedItem.tipo_resolucion"><label id="primero" for="r_tipo_res">Mediación</label>
											</div>
											<fieldset class="sin-bordes" :disabled="selectedItem.tipo_resolucion != 0">
												<div class="row" >
													<div class="form-group col-xs-12" :class="{'has-error': errors.has('tramites.nro_beneficio')}">
														<label for="nro_beneficio">Nro. Exp.</label>
														<input type="text" name="nro_beneficio" v-model="selectedItem.nro_beneficio" class="form-control" data-vv-validate-on="none">
														<span class="help-block" v-show="errors.has('tramites.nro_beneficio')">{{ errors.first('tramites.nro_beneficio') }}</span>
													</div>				
													<div class="form-group col-xs-12 col-sm-7" :class="{'has-error': errors.has('tramites.fecha_beneficio')}">
														<label for="fecha_beneficio">Fecha de acuerdo</label><br>
														<datepicker name="fecha_beneficio" v-model="selectedItem.fecha_beneficio" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
														<span class="help-block" v-show="errors.has('tramites.fecha_beneficio')">{{ errors.first('tramites.fecha_beneficio') }}</span>
													</div>
													<!--div class="form-group col-xs-12 col-sm-7" :class="{'has-error': errors.has('tramites.monto_beneficio')}">
														<label for="monto_beneficio">Monto del acuerdo</label><br>
														<div class="input-group">
														  <span class="input-group-addon" id="monto_beneficio_lbl">$</span>
														  <input type="text" name="monto_beneficio" v-model="selectedItem.monto_beneficio" class="form-control" placeholder="####.##" aria-describedby="monto_beneficio_lbl" data-vv-validate-on="none">
														</div>																											
														<span class="help-block" v-show="errors.has('tramites.monto_beneficio')">{{ errors.first('tramites.monto_beneficio') }}</span>
													</div-->														
													<div class="form-group col-xs-12 col-sm-5">
														<label>&nbsp;</label><br>
														<button :disabled="!puedoVerBeneficios()" type="button" class="btn btn-sm btn-success" @click="showBeneficios()">Mediación</button>
													</div>
													<div class="form-group col-xs-12">
														<span v-if="mostrarLeyendaBeneficios()" class="text-yellow">Debe guardar el trámite para poder ingresar mediación</span>
													</div>																					
												</div>
											</fieldset>
										</div>
										<div class="col-xs-12 col-sm-6">
											<div class="form-group" :class="{'has-error': errors.has('tramites.tipo_resolucion')}">
												<input type="radio" id="r_tipo_res_no" name="r_tipo_res_no" :value="1" v-model="selectedItem.tipo_resolucion"><label for="r_tipo_res_no">Judicial</label>
											</div>
											<fieldset class="sin-bordes" v-if="mostrarDatosExpJudicial()" :disabled="selectedItem.tipo_resolucion == 0">

												<exp-jud v-if="expJud.show" :data="selectedItem.exp_judicial" :areaId="selectedItem.area_id" @expJud:close="closeExpJudicial"></exp-jud>
												
												<button type="button" :disabled="selectedItem.tipo_resolucion != 1" v-show="!expJud.show" class="btn btn-sm bg-green" @click="showExpJudicial()">Exp. Judicial</button>
												
											</fieldset>								
										</div>
									</div>
								</template>
							</fieldset>
						</div>
					</div>
				</template>			

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
	import { modal,datepicker,checkbox } from 'vue-strap'
	import config from '../../../../config'
	import Api from '../../../../api'
	import vSelect from "vue-select"
	import ExpJud from './ExpJud'
	import { mapState, mapGetters } from 'vuex'
	import RadioButtonGroup from '../../../shared/buttons/RadioButtonGroup'
	
export default {
	name: 'TramiteCivil',
	components: {
		modal,
		vSelect,
		datepicker,
		checkbox,
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
			valorArchivar: false,
			expJud: {
				show: false,
				tramite_sig: null,
				expediente:null
			},			
		}
	}, 
	computed: {
		...mapState([
	  		'authUser'
		]),
		...mapGetters([
			'isResponsableArea','soyAdministrador'
		]),     
	},   
	mounted () {
		this.uri = this.uri.concat(this.selectedItem.cliente_id).concat('/tramites/');
		this.apiUrl = config.serverURI + this.uri;
		this.mounted = true;
		if (this.selectedItem.archivar !== true) {
			this.selectedItem.archivar = false;
		}
		this.valorArchivar = this.selectedItem.archivar;
		//this.selectedItem.resolucion = true;
	},
	methods: {	
	    clearErrors() {
		    this.cu_errors = ''
		    this.$validator.reset()    	
	    },    
	    close (data) {
	    	console.log('close en TP.vue')
	    	this.submited = false;
	    	this.clearErrors()
	    	if (data) {
	    		this.$emit('clientes:tramites-saved',data)
	    	} else {
	    		this.$emit('clientes:tramites-close')	
	    	}
	    },
	    save() {
		  	return new Promise((resolve, reject) => {
		    	this.errors.clear('tramites');
		    	this.errorAnses = '';

		    	if (this.selectedItem.fecha_inicio === '') {
		    		this.addError('fecha_inicio', 'Campo requerido','server','tramites');
		    	}

		    	if (!this.selectedItem.estado_tramite_id) {
		    		this.addError('estado_tramite_id', 'Campo requerido','server','tramites');
		    	}

		    	/*if (this.selectedItem.resolucion) {
		    		if (this.selectedItem.tipo_resolucion != 0 && this.selectedItem.tipo_resolucion != 1) {
		    			this.addError('tipo_resolucion', 'Campo requerido','server','tramites');
		    		} else {*/
		    			if (this.selectedItem.tipo_resolucion == 0) {
				    		if (this.selectedItem.fecha_beneficio === '') {
				    			this.addError('fecha_beneficio', 'Campo requerido','server','tramites');
				    		}
				    		if (!this.selectedItem.nro_beneficio) {
				    			this.addError('nro_beneficio', 'Campo requerido','server','tramites');
				    		}
		    			}
		    		/*}
		    	}*/	    		


/*		    	if (!this.selectedItem.rep_origen_id) {
		    		this.addError('rep_origen_id', 'Campo requerido','server','tramites');
		    	}*/

				this.$validator.validateAll('tramites').then((result) => {
		     	    if (result && this.errors.items.length < 1) {
		     	    	this.saving = true;
				    	Api.store(this.uri,this.selectedItem)
				    		.then((result) => {
			    				this.close(result.data.data);	    					
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
		/*onChangeRepOrigen(item) {
			this.selectedItem.rep_origen = item
			this.selectedItem.rep_origen_id = (item ? item.id : null)	
		},*/	
		mostrarDatosExpJudicial() {
			return true;//this.selectedItem.requerimiento.tipo_tramite_id === 509;
		},
	 	puedoVerBeneficios() {
	 		return this.selectedItem.id > 0 && (this.soyResponsable() || this.soyAdministrador);
	 	},
	 	mostrarLeyendaBeneficios() {
	 		return (this.soyResponsable() || this.soyAdministrador) && !this.selectedItem.id;
	 	},
	 	soyResponsable() {
	 		let _return = false;
	 		for(let i = 0; i < this.selectedItem.requerimiento.responsables.length; i++) {
	 			if (this.selectedItem.requerimiento.responsables[i].user_id === this.authUser.id) {
	 				return true;
	 			}
	 		}
	 	},
	 	showExpJudicial() {
	 		this.expJud.show = true;
	 	},
	 	closeExpJudicial(data) {
	 		if (data) {
	 			this.selectedItem.exp_judicial = data;
	 		}
	 		this.expJud.show = false;
	 	},
	 	showBeneficios() {
	 		this.$emit('clientes:tramites-show-beneficios')
	 	}
	  }
	};		
</script>
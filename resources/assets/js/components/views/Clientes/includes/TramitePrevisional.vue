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
					<div class="form-group col-sm-6" :class="{'has-error': errors.has('tramites.expediente')}">
						<nro-expediente :areaId="selectedItem.area_id" v-model="selectedItem.expediente" :cuit="selectedItem.cuit"></nro-expediente>
						<span class="help-block" v-show="errors.has('tramites.expediente')">{{ errors.first('tramites.expediente') }}</span>
					</div>
					<div class="form-group col-sm-3" :class="{'has-error': errors.has('tramites.fecha_inicio')}">
						<label for="fecha_inicio">Fecha Inicio</label><br>
						<datepickerr v-if="esVueltaAnses()" name="fecha_inicio" v-model="selectedItem.fecha_inicio_dp" :bootstrap-styling="true" :format="'dd/MM/yyyy'" :language="es" @selected="calcFechaVto(selectedItem)" :use-utc="false" :disabledDates="disabledDates"></datepickerr>
						<datepicker v-else name="fecha_inicio" v-model="selectedItem.fecha_inicio" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
						<span class="help-block" v-show="errors.has('tramites.fecha_inicio')">{{ errors.first('tramites.fecha_inicio') }}</span>
					</div>
					<div class="form-group col-sm-3" v-if="esVueltaAnses()">
						<label for="fecha_remision_vto" >Fecha Vto.</label><br>
						{{ selectedItem.fecha_remision_vto }}
					</div>						
				</div>

				<div class="row">
					<div class="form-group col-sm-6" :class="{'has-error': errors.has('tramites.rep_origen_id')}">
						<label for="rep_origen_id">Repartición Origen</label>
						<v-select
							v-model="selectedItem.rep_origen"
							:options="info.rep_origen"
							:on-change="onChangeRepOrigen"
							placeholder="Ingresa la rep. de origen"
							label="nombre"
						>
						</v-select>
						<span class="help-block" v-show="errors.has('tramites.rep_origen_id')">{{ errors.first('tramites.rep_origen_id') }}</span>
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
				<div class="row" v-if="esVueltaAnses()">
					<div class="col-xs-12" style="margin-bottom:20px;">					
						<fieldset>
							<legend>Exp. Judicial (Trámite de origen)</legend>
							<div class="row">
								<div class="col-xs-12">
									<dl class="dl-horizontal">
									  <dt>Nro. Expediente</dt>
									  <dd>{{ selectedItem.tramite_ant.exp_judicial.nro_expediente }}</dd>
									  <dt>Fecha</dt>
									  <dd>{{ selectedItem.tramite_ant.exp_judicial.fecha }}</dd>
									  <dt>Juzgado</dt>
									  <dd>{{ selectedItem.tramite_ant.exp_judicial.juzgado.nombre }}</dd>
									</dl>										
								</div>

							</div>
						</fieldset>
					</div>

					<div class="form-group col-xs-12">					
						<anses :items="selectedItem.anses" :estadosAnses="info.estado_anses" :msgErrors="errorAnses"></anses>
					</div>
					<div class="form-group col-xs-12">
						<input type="checkbox" name="chk-deceased" v-model="selectedItem.deceased" :value="true">
						<span>Actor fallecido</span> 
					</div>
				</div>		

				
				<template>
					<div class="row">
						<div class="col-sm-12">
							<fieldset :disabled="!selectedItem.resolucion">
								<template>
									<legend>
										<checkbox v-model="selectedItem.resolucion" :true-value="true">Resolución</checkbox>
									</legend>
									<div class="r-tipo-favorable row">
										<div class="col-xs-12 col-sm-6">
											<div class="form-group" :class="{'has-error': errors.has('tramites.tipo_resolucion')}">
												<input type="radio" id="r_tipo_res" :value="0" v-model="selectedItem.tipo_resolucion"><label id="primero" for="r_tipo_res">Favorable</label>
											</div>
											<fieldset class="sin-bordes" :disabled="selectedItem.tipo_resolucion != 0">
												<div class="row" >
													<div class="form-group col-xs-12" :class="{'has-error': errors.has('tramites.nro_beneficio')}">
														<label for="nro_beneficio">Nro. Beneficio</label>
														<input type="text" name="nro_beneficio" v-model="selectedItem.nro_beneficio" class="form-control" data-vv-validate-on="none">
														<span class="help-block" v-show="errors.has('tramites.nro_beneficio')">{{ errors.first('tramites.nro_beneficio') }}</span>
													</div>				
													<div class="form-group col-xs-12 col-sm-7" :class="{'has-error': errors.has('tramites.fecha_beneficio')}">
														<label for="fecha_beneficio">Fecha Beneficio</label><br>
														<datepicker name="fecha_beneficio" v-model="selectedItem.fecha_beneficio" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
														<span class="help-block" v-show="errors.has('tramites.fecha_beneficio')">{{ errors.first('tramites.fecha_beneficio') }}</span>
													</div>
													<div class="form-group col-xs-12 col-sm-5">
														<label>&nbsp;</label><br>
														<button :disabled="!puedoVerBeneficios()" type="button" class="btn btn-sm btn-success" @click="showBeneficios()">Beneficios</button>
														
													</div>						
													<div class="form-group col-xs-12">
														<span v-if="mostrarLeyendaBeneficios()" class="text-yellow">Debe guardar el trámite para poder ingresar beneficios</span>
													</div>	
												</div>
											</fieldset>
										</div>
										<div class="col-xs-12 col-sm-6">
											<div class="form-group" :class="{'has-error': errors.has('tramites.tipo_resolucion')}">
												<input type="radio" id="r_tipo_res_no" name="r_tipo_res_no" :value="1" v-model="selectedItem.tipo_resolucion"><label for="r_tipo_res_no">No Favorable</label>
											</div>
											<fieldset class="sin-bordes" v-if="mostrarDatosExpJudicial()" :disabled="selectedItem.tipo_resolucion == 0">
												
												<template v-if="selectedItem.area_id == 1 && !expJud.show && selectedItem.exp_judicial && selectedItem.exp_judicial.nro_expediente">
													<dl class="dl-horizontal">
													  <dt>Nro. Expediente</dt>
													  <dd>{{ selectedItem.exp_judicial.nro_expediente }}</dd>
													  <dt>Fecha</dt>
													  <dd>{{ selectedItem.exp_judicial.fecha }}</dd>
													  <dt>Juzgado</dt>
													  <dd>{{ selectedItem.exp_judicial.juzgado.nombre }}</dd>
													</dl>	
													<dl v-if="selectedItem.exp_judicial.vuelta_anses"  class="dl-horizontal">
													  <dt>Vuelta a ANSES</dt>
													  <dd v-html="$options.filters.booleanLabel(selectedItem.exp_judicial.vuelta_anses)"></dd>														
													  <dt >Nro. Expediente</dt>
													  <dd>{{ (selectedItem.tramite_sig ? selectedItem.tramite_sig.expediente : selectedItem.exp_judicial.tramite_sig.expediente) }}</dd>
													  <dt >Fecha Inicio</dt>
													  <dd>{{ (selectedItem.tramite_sig ? selectedItem.tramite_sig.fecha_inicio : selectedItem.exp_judicial.tramite_sig.fecha_inicio)}}</dd>
													</dl>												
												</template>

												<exp-jud v-if="expJud.show" :data="selectedItem.exp_judicial" :tramiteSig="selectedItem.tramite_sig" :areaId="selectedItem.area_id" @expJud:close="closeExpJudicial" :cuit="selectedItem.cuit" :repOrigen="info.rep_origen"></exp-jud>
												
												<button type="button" :disabled="selectedItem.tipo_resolucion != 1" v-show="!expJud.show" class="btn btn-sm bg-green  center-block" @click="showExpJudicial()">Exp. Judicial</button>
												
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
	import { modal,datepicker,checkbox,radio } from 'vue-strap'
	import config from '../../../../config'
	import Api from '../../../../api'
	import vSelect from "vue-select"
	import NroExpediente from './NroExpediente'
	import ExpJud from './ExpJud'
	import Anses from './Anses'
	import { mapState, mapGetters } from 'vuex'
	import RadioButtonGroup from '../../../shared/buttons/RadioButtonGroup'
	import Datepicker from 'vuejs-datepicker'
	import {es} from 'vuejs-datepicker/dist/locale'
	
export default {
	name: 'TramitePrevisional',
	components: {
		modal,
		vSelect,
		datepicker,
		checkbox,
		radio,
		NroExpediente,
		ExpJud,
		RadioButtonGroup,
		Anses,
		'Datepickerr':Datepicker
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
			disabledDates: {
				days: [6,0]
			},
			es: es,			
			errorAnses: '',
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
				expediente: null
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
		nroDoc: function() {
			return "00"+this.selectedItem.cuit.substring(2,this.selectedItem.cuit.length-1)+"0";
		}    
	},   
	mounted () {
		this.uri = this.uri.concat(this.selectedItem.cliente_id).concat('/tramites/');
		this.apiUrl = config.serverURI + this.uri;
		this.mounted = true;
		if (this.selectedItem.archivar !== true) {
			this.selectedItem.archivar = false;
		}
		this.valorArchivar = this.selectedItem.archivar;
		this.selectedItem.esVueltaAnses = this.esVueltaAnses();
	},
	methods: {
		calcFechaVto(item) {

			Vue.nextTick()
			  .then(function () {
			  	console.debug(item.fecha_inicio_dp)
				if (item.fecha_inicio_dp) {

					item.fecha_remision = moment(item.fecha_inicio_dp).format('DD/MM/YYYY');
					let _fecha_vto = moment(item.fecha_inicio_dp);

					if (_fecha_vto.isoWeekday() !== 6 && _fecha_vto.isoWeekday() !== 7) {
			  			let i = 0;
			  			while(i<120) {
			  				_fecha_vto.add(1,'days');
			  				if (_fecha_vto.isoWeekday() !== 6 && _fecha_vto.isoWeekday() !== 7) {
			  					i++;
			  				}
			  			}
			  			item.fecha_remision_vto = _fecha_vto.format('DD/MM/YYYY');
					} else {
						item.fecha_remision_vto = null;
						item.fecha_remision = '';
					}
				} else {
					item.fecha_remision_vto = null;
					item.fecha_remision = '';
				} 	

			  });
	  	},		
	    clearErrors() {
		    this.cu_errors = ''
		    this.$validator.reset()    	
	    },    
	    close (data) {
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
		    	if (!this.selectedItem.expediente) {
		    		this.addError('expediente', 'Campo requerido','server','tramites');
		    	}

		    	if (this.selectedItem.fecha_inicio === '') {
		    		this.addError('fecha_inicio', 'Campo requerido','server','tramites');
		    	}

		    	if (!this.selectedItem.estado_tramite_id) {
		    		this.addError('estado_tramite_id', 'Campo requerido','server','tramites');
		    	}

		    	if (this.selectedItem.resolucion) {
		    		if (this.selectedItem.tipo_resolucion != 0 && this.selectedItem.tipo_resolucion != 1) {
		    			this.addError('tipo_resolucion', 'Campo requerido','server','tramites');
		    		} else {
		    			if (this.selectedItem.tipo_resolucion == 0) {
				    		if (this.selectedItem.fecha_beneficio === '') {
				    			this.addError('fecha_beneficio', 'Campo requerido','server','tramites');
				    		}
				    		if (!this.selectedItem.nro_beneficio) {
				    			this.addError('nro_beneficio', 'Campo requerido','server','tramites');
				    		}
		    			}
		    		}
		    	}	    		

		    	if (this.esVueltaAnses()) {
			    	if (this.selectedItem.anses.length < 1) {
			    		this.addError('anses', 'Ingrese un estado','server','tramites');
			    		this.errorAnses = 'Ingrese un estado.';
			    	} else {
						let _exist = _.find(this.selectedItem.anses, function(o) { return (o.estado_anses_id === null || o.fecha_remision === ''); });			  
						if (_exist) {
							this.errorAnses = 'Debe completar todos los campos.';
							this.addError('anses', 'Debe completar todos los campos.','server','tramites');
						}  		
			    	}

		    	}

		    	if (!this.selectedItem.rep_origen_id) {
		    		this.addError('rep_origen_id', 'Campo requerido','server','tramites');
		    	}

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
	    esVueltaAnses() {
	    	return this.selectedItem.tramite_ant_id > 0 && (this.selectedItem.requerimiento.tipo_tramite_id === 509 || this.selectedItem.requerimiento.tipo_tramite_id === 8318);
	    },
		onChangeEstadoTra(item) {
			console.debug(item)
			this.selectedItem.estado = item
			this.selectedItem.estado_tramite_id = (item ? item.id : null)	

			switch(this.selectedItem.estado_tramite_id) {
				case 762:
					this.selectedItem.resolucion = false;
					this.selectedItem.tipo_resolucion = 1;
					break;
				case 516:
					this.selectedItem.resolucion = true;
					this.selectedItem.tipo_resolucion = 0;
					break;
				case 745:
					this.selectedItem.resolucion = true;
					this.selectedItem.tipo_resolucion = 1;
					break;
			}

		},		
		onChangeRepOrigen(item) {
			this.selectedItem.rep_origen = item
			this.selectedItem.rep_origen_id = (item ? item.id : null)	
		},	
		mostrarDatosExpJudicial() {
			return this.selectedItem.requerimiento.tipo_tramite_id === 509 || this.selectedItem.requerimiento.tipo_tramite_id === 8318;
		},
	 	esBeneficioAcordado() {
	 		return this.selectedItem.estado_tramite_id == 516 || this.selectedItem.estado_tramite_id == 515;
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
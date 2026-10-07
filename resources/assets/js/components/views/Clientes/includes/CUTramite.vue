<template>
	<div>
		<tramite-previsional v-if="!benModal.show && selectedItem.area_id === 1" :baseUri="baseUri" :actionPerm="'tramites'" :info="info" :selectedItem="selectedItem" @clientes:tramites-close="onClosed" @clientes:tramites-saved="onSaved" :canSave="canSave" @clientes:tramites-show-beneficios="showBeneficios"></tramite-previsional>

		<tramite-laboral v-if="selectedItem.area_id === 2" v-show="!benModal.show" :baseUri="baseUri" :actionPerm="'tramites'" :info="info" :selectedItem="selectedItem" @clientes:tramites-close="onClosed" @clientes:tramites-saved="onSaved" :canSave="canSave" @clientes:tramites-show-beneficios="showBeneficios" @clientes:tramites-show-conciliacion="showConciliacion"></tramite-laboral+>

		<tramite-civil v-if="!benModal.show && selectedItem.area_id === 3" :baseUri="baseUri" :actionPerm="'tramites'" :info="info" :selectedItem="selectedItem" @clientes:tramites-close="onClosed" @clientes:tramites-saved="onSaved" :canSave="canSave" @clientes:tramites-show-beneficios="showBeneficios"></tramite-civil>

		<tramite-comercial v-if="!benModal.show && selectedItem.area_id === 4" :baseUri="baseUri" :actionPerm="'tramites'" :info="info" :selectedItem="selectedItem" @clientes:tramites-close="onClosed" @clientes:tramites-saved="onSaved" :canSave="canSave" @clientes:tramites-show-beneficios="showBeneficios"></tramite-comercial>		

		<tramite-societario v-if="!benModal.show && selectedItem.area_id === 5" :baseUri="baseUri" :actionPerm="'tramites'" :info="info" :selectedItem="selectedItem" @clientes:tramites-close="onClosed" @clientes:tramites-saved="onSaved" :canSave="canSave" @clientes:tramites-show-beneficios="showBeneficios"></tramite-societario>
	
		<div v-if="benModal.show && selectedItem.id > 0">
        	<div v-if="!benModal.am.show">
	        	<div class="table-responsive" >
		            <table class="table table-striped">
		            	<tbody>
		        			<tr>
			                  <th>Detalle</th>
			                  <th>Fecha Cobro</th>
			                  <th>Fecha Alta</th>
			                  <th></th>
		                	</tr>
		                	<tr v-if="!benModal.list.loading && benModal.list.data.length > 0" v-for="(value,index) in benModal.list.data">
			                  <td>
			                  	<template v-if="value.detalle.area_id == 1">
		                  			<dl class="dl-horizontal"> 
		                  				<dt>Haber mensual</dt><dd>{{ value.detalle.haber_mensual | currency }}</dd>
		                  				<dt>Retroactivo</dt><dd>{{ value.detalle.retroactivo | currency }}</dd>
		                  				<dt>Mes alta</dt><dd>{{ value.detalle.mes_alta }}</dd>
		                  			</dl>
			                  	</template>	
			                  	<template v-if="value.detalle.area_id == 2">
		                  			<dl class="dl-horizontal"> 
		                  				<dt>Couta acordada</dt><dd>{{ value.detalle.cuota_acordada | currency }}</dd>
		                  				<dt>Honorarios</dt><dd>{{ value.detalle.honorarios | currency }}</dd>
		                  				<!--dt>Retroactivo</dt><dd>{{ value.detalle.retroactivo | currency }}</dd-->
		                  			</dl>
			                  	</template>	
			                  	<template v-if="value.detalle.area_id == 3">
		                  			<dl class="dl-horizontal"> 
		                  				<dt>Couta acordada</dt><dd>{{ value.detalle.cuota_acordada | currency }}</dd>
		                  				<dt>Honorarios</dt><dd>{{ value.detalle.honorarios | currency }}</dd>
		                  				<dt>Retroactivo</dt><dd>{{ value.detalle.retroactivo | currency }}</dd>
		                  			</dl>
			                  	</template>				                  				                  	
			                  	<template v-if="value.detalle.area_id == 4">
		                  			<dl class="dl-horizontal"> 
		                  				<dt>Couta acordada</dt><dd>{{ value.detalle.cuota_acordada | currency }}</dd>
		                  				<dt>Honorarios</dt><dd>{{ value.detalle.honorarios | currency }}</dd>
		                  				<dt>Retroactivo</dt><dd>{{ value.detalle.retroactivo | currency }}</dd>
		                  			</dl>
			                  	</template>			                  	
			                  </td>
			                  <td>{{ value.fecha_cobro }}</td>
			                  <td>{{ value.created_at }}</td>
			                  <td style="text-align: right;" nowrap="">
								<button-type v-can="[actionPerm+':U']" type="edit-list" @click="onAction('editBen', value, index)"/>
								<button-type v-can="[actionPerm+':U']" type="remove-list" @click="onAction('removeBen',value, index)"/>
			                  </td>
			            	</tr>
			            	<tr v-if="benModal.list.data.length < 1 && !benModal.list.loading">
		            			<td colspan="7">No se encontraron registros.</td>
			            	</tr>
			            	<tr v-if="benModal.list.loading">
			            		<td colspan="7">
			            			<pulse-loader :loading="benModal.loading"></pulse-loader>
			            		</td>
			            	</tr>			            	
			        	</tbody>
		      		</table>
	      		</div>
				<div slot="modal-footer" class="modal-footer">
					<button-type type="close" @click="closeBeneModal()"/>
					<button-type type="add" @click="newBenefConc()"/>
				</div>	      		
      		</div>
      		<div v-else>
      			<form id="frmBene" @submit.prevent="saveBeneficio()" :data-vv-scope="'bene'">
					<div class="row" v-if="benModal.selectedItem.detalle.area_id === 1">
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.haber_mensual')}">
								<label for="haber_mensual">Haber mensual</label>
								<input type="text" name="haber_mensual" v-model="benModal.selectedItem.detalle.haber_mensual" class="form-control" v-validate="'required'" data-vv-validate-on="none">
								<span class="help-block" v-show="errors.has('bene.haber_mensual')">{{ errors.first('bene.haber_mensual') }}</span>
							</div>

							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.retroactivo')}">
								<label for="retroactivo">Retroactivo</label>
								<input type="text" name="retroactivo" v-model="benModal.selectedItem.detalle.retroactivo" class="form-control" v-validate="'required'" data-vv-validate-on="none">
								<span class="help-block" v-show="errors.has('bene.retroactivo')">{{ errors.first('bene.retroactivo') }}</span>
							</div>	
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.mes_alta')}">
								<label for="mes_alta">Mes de alta</label>
								<input type="text" name="mes_alta" v-model="benModal.selectedItem.detalle.mes_alta" class="form-control" v-mask="['##/####']"  placeholder="MM/AAAA" v-validate="'required'" data-vv-validate-on="none">
								<span class="help-block" v-show="errors.has('bene.mes_alta')">{{ errors.first('bene.mes_alta') }}</span>
							</div>
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.agente_pagador')}">
								<label for="agente_pagador">Agente pagador</label>
								<input type="text" name="agente_pagador" v-model="benModal.selectedItem.detalle.agente_pagador" class="form-control" v-validate="'required'" data-vv-validate-on="none">
								<span class="help-block" v-show="errors.has('bene.agente_pagador')">{{ errors.first('bene.agente_pagador') }}</span>
							</div>																				
					</div>
					<div class="row" v-if="benModal.selectedItem.detalle.area_id === 2">
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.cuota_acordada')}">
								<label for="cuota_acordada">Cuota acordada</label>
								<input type="text" name="cuota_acordada" v-model="benModal.selectedItem.detalle.cuota_acordada" class="form-control">
								<span class="help-block" v-show="errors.has('bene.cuota_acordada')">{{ errors.first('bene.cuota_acordada') }}</span>
							</div>
							<!--div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.retroactivo')}">
								<label for="retroactivo">Retroactivo</label>
								<input type="text" name="retroactivo" v-model="benModal.selectedItem.detalle.retroactivo" class="form-control" >
								<span class="help-block" v-show="errors.has('bene.retroactivo')">{{ errors.first('bene.retroactivo') }}</span>
							</div-->	
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.honorarios')}">
								<label for="honorarios">Honorarios</label>
								<input type="text" name="honorarios" v-model="benModal.selectedItem.detalle.honorarios" class="form-control">
								<span class="help-block" v-show="errors.has('bene.honorarios')">{{ errors.first('bene.honorarios') }}</span>
							</div>							
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.agente_pagador')}">
								<label for="agente_pagador">Agente pagador</label>
								<input type="text" name="agente_pagador" v-model="benModal.selectedItem.detalle.agente_pagador" class="form-control">
								<span class="help-block" v-show="errors.has('bene.agente_pagador')">{{ errors.first('bene.agente_pagador') }}</span>
							</div>																				
					</div>
					<div class="row" v-if="benModal.selectedItem.detalle.area_id === 3">
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.cuota_acordada')}">
								<label for="cuota_acordada">Cuota acordada</label>
								<input type="text" name="cuota_acordada" v-model="benModal.selectedItem.detalle.cuota_acordada" class="form-control">
								<span class="help-block" v-show="errors.has('bene.cuota_acordada')">{{ errors.first('bene.cuota_acordada') }}</span>
							</div>
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.retroactivo')}">
								<label for="retroactivo">Retroactivo</label>
								<input type="text" name="retroactivo" v-model="benModal.selectedItem.detalle.retroactivo" class="form-control" >
								<span class="help-block" v-show="errors.has('bene.retroactivo')">{{ errors.first('bene.retroactivo') }}</span>
							</div>	
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.honorarios')}">
								<label for="honorarios">Honorarios</label>
								<input type="text" name="honorarios" v-model="benModal.selectedItem.detalle.honorarios" class="form-control">
								<span class="help-block" v-show="errors.has('bene.honorarios')">{{ errors.first('bene.honorarios') }}</span>
							</div>							
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.agente_pagador')}">
								<label for="agente_pagador">Agente pagador</label>
								<input type="text" name="agente_pagador" v-model="benModal.selectedItem.detalle.agente_pagador" class="form-control">
								<span class="help-block" v-show="errors.has('bene.agente_pagador')">{{ errors.first('bene.agente_pagador') }}</span>
							</div>																					
					</div>		
					<div class="row" v-if="benModal.selectedItem.detalle.area_id === 4">
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.cuota_acordada')}">
								<label for="cuota_acordada">Cuota acordada</label>
								<input type="text" name="cuota_acordada" v-model="benModal.selectedItem.detalle.cuota_acordada" class="form-control">
								<span class="help-block" v-show="errors.has('bene.cuota_acordada')">{{ errors.first('bene.cuota_acordada') }}</span>
							</div>
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.retroactivo')}">
								<label for="retroactivo">Retroactivo</label>
								<input type="text" name="retroactivo" v-model="benModal.selectedItem.detalle.retroactivo" class="form-control" >
								<span class="help-block" v-show="errors.has('bene.retroactivo')">{{ errors.first('bene.retroactivo') }}</span>
							</div>	
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.honorarios')}">
								<label for="honorarios">Honorarios</label>
								<input type="text" name="honorarios" v-model="benModal.selectedItem.detalle.honorarios" class="form-control">
								<span class="help-block" v-show="errors.has('bene.honorarios')">{{ errors.first('bene.honorarios') }}</span>
							</div>							
							<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.agente_pagador')}">
								<label for="agente_pagador">Agente pagador</label>
								<input type="text" name="agente_pagador" v-model="benModal.selectedItem.detalle.agente_pagador" class="form-control">
								<span class="help-block" v-show="errors.has('bene.agente_pagador')">{{ errors.first('bene.agente_pagador') }}</span>
							</div>																					
					</div>															
					<div class="row">
						<div class="form-group col-sm-12" :class="{'has-error': errors.has('bene.fecha_cobro')}">
							<label for="fecha_cobro">Fecha Cobro</label><br>
							<datepicker name="fecha_cobro" v-model="benModal.selectedItem.fecha_cobro" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
							<span class="help-block" v-show="errors.has('bene.fecha_cobro')">{{ errors.first('bene.fecha_cobro') }}</span>
						</div>						
					</div>
					<div slot="modal-footer" class="modal-footer">
						<button-type type="close" @click="backBeneficio()"/>
						<button-type type="save" :promise="saveBeneficio"/>
					</div>
				</form>
      			
      		</div>
		</div>        
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
import TramitePrevisional from './TramitePrevisional'
import TramiteSocietario from './TramiteSocietario'
import TramiteCivil from './TramiteCivil'
import TramiteComercial from './TramiteComercial'
import TramiteLaboral from './TramiteLaboral'
import ExpJud from './ExpJud'
import { mapState } from 'vuex'
import RadioButtonGroup from '../../../shared/buttons/RadioButtonGroup'
export default {
  name: 'SolapaTramites',
  components: {
  	TramitePrevisional,
  	TramiteSocietario,
  	TramiteCivil,
  	TramiteComercial,
  	TramiteLaboral,
  	modal,
  	vSelect,
  	datepicker,
  	checkbox,
  	//radio,
  	//NroExpediente,
  	//ExpJud,
  	//RadioButtonGroup
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
		title: 'Tramite Nro ',
		modal: true,
		submited: false,
		cu_errors: '',
		saving: false,
		messages: '', 
		apiUrl: '',
		uri: this.baseUri,
		beneficios: [],
		/*expJud: {
			show: false,
			tramite_sig: null
		},*/
		benModal: {
			title: 'Beneficio',
			type: 'beneficios/',
			show: false,
			selectedItem: null,
			am: {
				show: false
			},
			list: {
				loading: true,
				data: []
			}
		},
		mounted: false,
		//valorArchivar: false
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
	/*if (this.selectedItem.archivar !== true) {
		this.selectedItem.archivar = false;
	}
	this.valorArchivar = this.selectedItem.archivar;*/
  },  
  methods: {
    onAction (action, data, index) {
    	this.$store.dispatch('hideSuccessNotification')
		switch(action) {
			case 'editBen':
				this.editBenefConc(_.clone(data, true));
				break;
			case 'removeBen':
				this.removeBenefConc(data,index);
				break;      		
		}
    },  	  
    onClosed (result) {
    	this.$emit('clientes:tramites-close')
    },
    onSaved (data) {
		this.$emit('clientes:tramites-saved',data)
    },
	/*onChangeRepOrigen(item) {
		this.selectedItem.rep_origen = item
		this.selectedItem.rep_origen_id = (item ? item.id : null)	
	},
	onChangeEstadoTra(item) {
		this.selectedItem.estado = item
		this.selectedItem.estado_tramite_id = (item ? item.id : null)	
		if (item && this.mostrarResolucion()) {
		}
	},
	mostrarResolucion() {
		//return this.selectedItem.area_id !== 5;
		return true;
	},	
    esVueltaAnses() {
    	return this.selectedItem.area_id === 1 && this.selectedItem.tramite_ant_id > 0 && this.selectedItem.requerimiento.tipo_tramite_id === 509;
    },*/	
    back() {
		this.closeAmModal()
    },
    closeAmModal (result) {
    	this.submited = false;
    	this.clearErrors()
    	this.$emit('clientes:tramites-close',result)
    },
    clearErrors() {
	    this.cu_errors = ''
	    this.$validator.reset()    	
    }, 
 	puedoVerBeneficios() {
 		return this.selectedItem.id > 0 && this.soyResponsable();
 	},	                
    /*save() {
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

	    	if (this.mostrarResolucion()) {
		    	if (this.selectedItem.resolucion) {

	    			switch(this.selectedItem.area_id) {
	    				case 1:
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

	    					break;
	    				case 5:
				    		if (this.ingresarVto() && this.selectedItem.fecha_vto === '') {
				    			this.addError('fecha_vto', 'Campo requerido','server','tramites');
				    		}		    				
	    					break;
	    			}
		    	}	    		
	    	}

	    	if (this.esVueltaAnses()) {
		    	if (!this.selectedItem.estado_anses_id) {
		    		this.addError('estado_anses_id', 'Campo requerido','server','tramites');
		    	}
	    	}

	    	switch (this.selectedItem.area_id) {
	    		case 1:
			    	if (!this.selectedItem.rep_origen_id) {
			    		this.addError('rep_origen_id', 'Campo requerido','server','tramites');
			    	}
	    			break;
				default:
					break;
	    	}

			this.$validator.validateAll('tramites').then((result) => {
	     	    if (result && this.errors.items.length < 1) {
	     	    	this.saving = true;
			    	Api.store(this.uri,this.selectedItem)
			    		.then((result) => {
		    				this.saving = false;
		    				this.$emit('clientes:tramites-saved',result.data.data)
		    				this.closeAmModal(true);	    					
		    				//if (this.selectedItem.area_id !== 1 || (this.selectedItem.id && this.selectedItem.id > 0)) {
							//	this.closeAmModal(true);	    					
		    				//} else {
		    				//	this.selectedItem.id = result.data.data.id;	
		    				//}
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
	ingresarVto() {
		return (this.selectedItem.area_id === 5 || this.selectedItem.area_id === 4 || (this.selectedItem.area_id === 5 && this.selectedItem.requerimiento.tipo_tramite_id === 8126));
	},
 	/*puedoVerBeneficios() {
 		return this.selectedItem.id > 0 && (this.soyResponsable() || this.soyResponsableArea(this.selectedItem.area_id));
 	},
 	
 	soyResponsable() {
 		let _return = false;
 		for(let i = 0; i < this.selectedItem.requerimiento.responsables.length; i++) {
 			if (this.selectedItem.requerimiento.responsables[i].user_id === this.authUser.id) {
 				return true;
 			}
 		}
 	}*/
 	//BENEFICIOS

 	closeBeneModal() {
 		this.benModal.show = false;
 	},
 	backBeneficio() {
 		this.benModal.am.show = false;
 	},
  	saveBeneficio() {
	  	return new Promise((resolve, reject) => {
	    	this.errors.clear('bene');
			// this.$validator.reset();
			console.debug(1);
	    	if (typeof this.benModal.selectedItem.fecha_cobro !== 'undefined' && this.benModal.selectedItem.fecha_cobro === '') {
	    		this.addError('fecha_cobro', 'Campo requerido','server','bene');
				console.debug(2);
	    	}
			console.debug(3);
			this.$validator.validateAll('bene').then((result) => {
				console.debug(4);
				console.debug([result,this.errors.items.length]);
	     	    if (result && this.errors.items.length < 1) {
	     	    	this.saving = true;
			  		Api.store(this.uri.concat(this.selectedItem.id).concat('/').concat(this.benModal.type),this.benModal.selectedItem)
			  			.then(resp => {
							this.loadBenefConc();
							//this.backBeneficio();
							this.saving = false; 
							this.benModal.am.show = false;
							resolve()
				  		},(resp) => {
							this.saving = false;
							this.message = resp.message
							this.$store.dispatch('showErrorNotification',this.message)
							reject()
						});	
		    		      	
		        } else {
		        	reject();	
		        }
	      	},errors => {
	      		resolve()
	      	});
		});
 	},
 	/*mostrarLabelNroBenef() {
 		let _label = 'Nro. ';
 		switch(this.selectedItem.area_id) {
 			case 1:
 				_label+= ' Beneficio';
 				break;
 			case 2:
 				_label+= ' Exp.';
 				break;
			default:
				break;
 		}
 		return _label;
 	},
 	mostrarLabelFechaBenef() {
 		let _label = 'Fecha ';
 		switch(this.selectedItem.area_id) {
 			case 1:
 				_label+= ' Beneficio';
 				break;
 			case 2:
 				_label+= '';
 				break;
			default:
				break;
 		}
 		return _label;
 	}, 	
 	mostrarLabelBtnBenef() {
 		let _label = '';
 		switch(this.selectedItem.area_id) {
 			case 1:
 				_label+= 'Beneficios';
 				break;
 			case 2:
 				_label+= 'Seclo';
 				break;
			default:
				break;
 		}
 		return _label;
 	},*/  	
 	showBeneficios() {
 		this.benModal.type = 'beneficios/';
 		this.benModal.show = true;
 		this.loadBenefConc();
 	},
 	showConciliacion() {
 		this.benModal.type = 'conciliacion/';
 		this.benModal.show = true;
 		this.loadBenefConc();
 	},
 	loadBenefConc() {
 		this.benModal.list.loading = true;
 		Api.get(this.uri.concat(this.selectedItem.id).concat('/').concat(this.benModal.type)).then(resp => {
			this.benModal.list.data.length = 0;
 			this.benModal.list.data = resp.data;
 			this.benModal.list.loading = false;
 		}, error => {
			this.benModal.list.loading = false;
 		});

 	},
 	newBenefConc() {
 		let _selectedDetalle = {area_id: this.selectedItem.area_id};
 		switch(this.selectedItem.area_id) {
 			case 1:
 				_selectedDetalle = _.assign(_selectedDetalle,{
 					haber_mensual: 0,
 					retroactivo: 0,
 					mes_alta: '',
 					agente_pagador: ''
 				});
 				break;
 			case 2:
 				_selectedDetalle = _.assign(_selectedDetalle,{
 					cuota_acordada: 0,
 					honorarios: 0,
 					retroactivo: 0,
 					agente_pagador: ''
 				});
 				break; 		
 			case 3:
 				_selectedDetalle = _.assign(_selectedDetalle,{
 					cuota_acordada: 0,
 					honorarios: 0,
 					retroactivo: 0,
 					agente_pagador: ''
 				});
 				break;
 			case 4:
 				_selectedDetalle = _.assign(_selectedDetalle,{
 					cuota_acordada: 0,
 					honorarios: 0,
 					retroactivo: 0,
 					agente_pagador: ''
 				});
 				break; 				 						
 			default:
 				break;
 		}

 		this.benModal.selectedItem = {
 			tramite_id: this.selectedItem.id,
 			fecha_cobro: '',
 			detalle: _selectedDetalle
 		};

 		this.benModal.am.show = true;
 	},
 	editBenefConc(data) {
 		this.benModal.selectedItem = data;
 		this.benModal.am.show = true;
 	},
 	removeBenefConc(item,index) {
		this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
			.then((dialog) => {
	        	dialog.loading(true)
	        	Api.delete(this.uri.concat(this.selectedItem.id).concat('/').concat(this.benModal.type),item)
	        		.then((result) => {
	        			dialog.close()
	        			this.$store.dispatch('showSuccessNotification',result.data.message)
	    				this.loadBenefConc()
	        		},() => {
	        			dialog.close()
	        		})  			
			})    	

 	},
 	/*showExpJudicial() {
 		this.expJud.show = true;
 	},
 	closeExpJudicial(data) {
 		if (data) {
 			this.selectedItem.exp_judicial = data;
 		}
 		this.expJud.show = false;
 	}*/
  },
  watch: { 
  	/*'selectedItem.fecha_remision'(newVal, oldVal) {
		if (newVal) {
			let _fecha_vto = moment(newVal,'DD/MM/YYYY');
			if (_fecha_vto.isoWeekday() !== 6 && _fecha_vto.isoWeekday() !== 7) {
	  			let i = 0;
	  			while(i<120) {
	  				_fecha_vto.add(1,'days');
	  				if (_fecha_vto.isoWeekday() !== 6 && _fecha_vto.isoWeekday() !== 7) {
	  					i++;
	  				}
	  			}
	  			this.selectedItem.fecha_remision_vto = _fecha_vto.format('DD/MM/YYYY');
			} else {
				this.selectedItem.fecha_remision_vto = null;
			}
		} else {
			this.selectedItem.fecha_remision_vto = null;
		}
  	},
  	'selectedItem.archivar'(newVal, oldVal) { // watch it
    	if (this.valorArchivar !== newVal) {
    		if (newVal) {
    			//Estoy marcandolo como archivado. Seteo el id del usuario actual
    			this.selectedItem.usuario_archivo_id = this.authUser.id;
    			this.selectedItem.fecha_archivo = moment().format('YYYY-MM-DD HH:mm:ss');
    		} else {
    			this.selectedItem.usuario_archivo_id = null;
    			this.selectedItem.fecha_archivo = null;
    		}
    	}
    }*/
  }/*,
  computed: {
  	fecha_habil_vto: function() {
  		return moment(this.selectedItem.fecha_remision,'DD/MM/YYYY').add(120,'days').format('DD/MM/YYYY');
  	}
  }  */
};	
</script>
<style scoped>
	.r-tipo-favorable input[type="radio"] {
		display: inline-block;
	}
	.r-tipo-favorable label#primero {
		margin-right: 30px;
	}
</style>
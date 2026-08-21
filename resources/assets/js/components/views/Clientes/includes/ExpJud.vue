<template>
	<form @submit.prevent="save()" :data-vv-scope="'exp_jud'">
		<div class="row">
			<div class="form-group col-sm-7 col-xs-12" :class="{'has-error': errors.has('exp_jud.nro_expediente')}">
				<label for="nro_expediente">Nro. Expediente</label>
				<input type="text" name="nro_expediente" v-model="selectedItem.nro_expediente" class="form-control" v-validate="'required'" data-vv-validate-on="none" v-mask="['#/##','##/##','###/##','####/##','#####/##','######/##']" placeholder="######/##">
				<span class="help-block" v-show="errors.has('exp_jud.nro_expediente')">{{ errors.first('exp_jud.nro_expediente') }}</span>
			</div>
			<div class="form-group col-sm-7 col-xs-12" :class="{'has-error': errors.has('exp_jud.fecha')}">
				<label for="fecha">Fecha</label><br>
				<datepicker name="fecha" data-vv-validate-on="none" v-model="selectedItem.fecha" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
				<span class="help-block" v-show="errors.has('exp_jud.fecha')">{{ errors.first('exp_jud.fecha') }}</span>
			</div>		
			<div class="form-group col-xs-12" :class="{'has-error': errors.has('exp_jud.juzgado_id')}">
				<label for="juzgado_id">Juzgado</label>
				<v-select
					v-model="selectedItem.juzgado"
					:options="info.juzgados"
					:on-change="onChangeJuzgado"
					placeholder="Ingresa el juzgado"
					label="nombre"
					data-vv-validate-on="none"
				>
				</v-select>
				<span class="help-block" v-show="errors.has('exp_jud.juzgado_id')">{{ errors.first('exp_jud.juzgado_id') }}</span>
			</div>

			<template v-if="mounted && mostrarVueltaAnses()">
				<div class="form-group col-xs-12" >
					<input :disabled="selectedItem.tramite_sig.id > 0" type="checkbox" :value="false" v-model="selectedItem.vuelta_anses"  id="vuelta_anses" name="vuelta_anses"><label for="vuelta_anses">Vuelta a ANSES</label>
				</div>
				<div class="col-xs-12 separador clearfix"></div>

				<fieldset v-if="selectedItem.vuelta_anses" :disabled="selectedItem.tramite_sig.id > 0" class="sinbordes">
					

						<div class="form-group col-xs-12">
							<nro-expediente :areaId="areaId" v-model="selectedItem.tramite_sig.expediente" :cuit="cuit" :type="'nro-doc'"></nro-expediente>
						</div>
						<div class="form-group col-xs-12" :class="{'has-error': errors.has('exp_jud.fecha_inicio')}">
							<label for="fecha_inicio">Fecha Inicio</label><br>
							<datepicker name="fecha_inicio" v-model="selectedItem.tramite_sig.fecha_inicio" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
							<span class="help-block" v-show="errors.has('exp_jud.fecha_inicio')">{{ errors.first('exp_jud.fecha_inicio') }}</span>
						</div>	
						<!--div class="form-group col-xs-12" :class="{'has-error': errors.has('exp_jud.rep_origen_id')}">
							<label for="rep_origen_id">Repartición Origen</label>
							<v-select
								v-model="selectedItem.tramite_sig.rep_origen"
								:options="repOrigen"
								:on-change="onChangeRepOrigen"
								placeholder="Ingresa la rep. de origen"
								label="nombre"
							>
							</v-select>
							<span class="help-block" v-show="errors.has('exp_jud.rep_origen_id')">{{ errors.first('exp_jud.rep_origen_id') }}</span>
						</div-->							
						
					
				</fieldset>
			</template>
			
			<template v-if="mounted && mostrarConciliacion()">
				<div class="form-group col-xs-12" >
					<input type="checkbox" :value="true" v-model="selectedItem.juicio_conciliado" id="juicio_conciliado" name="juicio_conciliado"><label for="juicio_conciliado">Juicio conciliado</label>
				</div>
				<div class="col-xs-12 separador clearfix"></div>

				<fieldset v-if="selectedItem.juicio_conciliado" class="sinbordes">
						<!--div class="form-group col-xs-12" :class="{'has-error': errors.has('exp_jud.nro_conciliacion')}">
							<label for="nro_conciliacion">Nro. Exp.</label>
							<input type="text" name="nro_conciliacion" v-model="selectedItem.nro_conciliacion" class="form-control" data-vv-validate-on="none" v-validate="'required'">
							<span class="help-block" v-show="errors.has('exp_jud.nro_conciliacion')">{{ errors.first('exp_jud.nro_conciliacion') }}</span>
						</div-->				
						<div class="form-group col-xs-12 col-sm-7" :class="{'has-error': errors.has('exp_jud.fecha_conciliacion')}">
							<label for="fecha_conciliacion">Fecha de conciliación</label><br>
							<datepicker name="fecha_conciliacion" v-model="selectedItem.fecha_conciliacion" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
							<span class="help-block" v-show="errors.has('exp_jud.fecha_conciliacion')">{{ errors.first('exp_jud.fecha_conciliacion') }}</span>
						</div>
						<div class="form-group col-xs-12 col-sm-7" :class="{'has-error': errors.has('exp_jud.monto_conciliacion')}">
							<label for="monto_conciliacion">Monto del acuerdo</label><br>
							<div class="input-group">
							  <span class="input-group-addon" id="monto_conciliacion_lbl">$</span>
							  <input type="text" name="monto_conciliacion" v-model="selectedItem.monto_conciliacion" class="form-control" placeholder="####.##" aria-describedby="monto_conciliacion_lbl" data-vv-validate-on="none">
							</div>																											
							<span class="help-block" v-show="errors.has('exp_jud.monto_beneficio')">{{ errors.first('exp_jud.monto_beneficio') }}</span>
						</div>								
						<div class="form-group col-xs-12 col-sm-5">
							<label>&nbsp;</label><br>
							<button :disabled="!puedoVerConciliacion" type="button" class="btn btn-sm btn-success" @click="showConciliacion()">conciliacion</button>
						</div>							
				</fieldset>
			</template>			
			<div class="col-xs-12">
				<button-type type="save" :submit="true"/>
				<button-type type="close" @click="back()"/>        
			</div>	
		</div>
	</form>
</template>
<script>
	import moment from 'moment'
	import Vue from 'vue'
	import config from '../../../../config'
	import Api from '../../../../api'
	import vSelect from "vue-select"
	import NroExpediente from './NroExpediente'
	import { datepicker } from 'vue-strap'
	export default {
	  name: 'ExpJud',
	  components: {
	  	vSelect,
	  	datepicker,
	  	NroExpediente
	  },
	  props: {
	  	data: {
	  		required: true,
	  		default() {
	  			return {};
	  		}
	  	},
		areaId: {
			type: Number,
			required: true
		},	  
		cuit: {
			type: String,
			default() {
				return null;
			}
		},
		tramiteSig: {
			type: Object,
			default() {
				return {};
			}
		},
		tramiteSigId: {
			type: Number,
			default() {
				return 0;
			}
		},
		repOrigen: {
			type: Array,
			default() {
				return [];
			}
		},
		puedoVerConciliacion: {
			type: Boolean,
			default() {
				return false;
			}
		}
	  },
	  data () {
		return {
			title: 'Expediente judicial',
			info: {
				juzgados: [],
			},		
			mounted: false,
			selectedItem: _.assign({
				nro_expediente: null,
				fecha: moment().format('DD/MM/YYYY'),
				fecha_conciliacion: '',
				monto_conciliacion: '',
				nro_conciliacion: null,
				juicio_conciliado: false,
				juzgado: null,
				juzgado_id: null,
				estado_id: 0,
				vuelta_anses: false,
				area_id: this.areaId,
				tramite_sig: _.assign({
		  			selected: false,
		  			expediente: this.expediente,
		  			id: 0,
		  			rep_origen: null,
		  			rep_origen_id: null,
		  			fecha_inicio: moment().format('DD/MM/YYYY'),
		  			fecha_remision: null,
		  			fecha_remision_vto: null,
		  			estado_id: 55 //Iniciado	  			
				},this.tramiteSig)
			},this.data),
			messages: '', 
		}
	  }, 
	computed: {
		expediente: function() {
			return  (this.areaId === 1 ? "000".concat(this.cuit).concat("000").concat("000000") : null);
		} 	  	
	}, 
	beforeMount() {
		console.debug(this.data);

		if (this.selectedItem.tramite_sig.id === 0) {
			this.selectedItem.tramite_sig.expediente = this.expediente;
		}
	},	  
	mounted () {
		setTimeout(() => {
			this.mounted = true;
		},100);

		Api.combos('exp-judicial/' + this.areaId).then(resp => {
			this.info = resp.data;
		});

	},  
	methods: {
		calcFechaVto(item) {

			Vue.nextTick()
			  .then(function () {
				if (item.fecha_inicio) {

					item.fecha_remision = _.clone(item.fecha_inicio);
					let _fecha_vto = moment(item.fecha_inicio,"DD/MM/YYYY");

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
		back() {
			this.$emit('expJud:close')
		},
		save() {
			this.errors.clear('exp_jud');

			if (!this.selectedItem.juzgado_id) {
				this.addError('juzgado_id', 'Campo requerido','server','exp_jud');
			}
			if (this.selectedItem.fecha === '') {
				this.addError('fecha', 'Campo requerido','server','exp_jud');
			}

			if (this.selectedItem.juicio_conciliado) {
				if (this.selectedItem.fecha_conciliacion === '') {
					this.addError('fecha_conciliacion', 'Campo requerido','server','exp_jud');
				}
			}

			this.$validator.validateAll('exp_jud').then((result) => {
		 	    if (result && this.errors.items.length < 1) {
					this.calcFechaVto(this.selectedItem.tramite_sig);	
					if(this.areaId != 5) {
						this.selectedItem.nro_conciliacion = this.selectedItem.nro_expediente;
					}						 	    	
		 	    	this.$emit('expJud:close',_.clone(this.selectedItem))	
				}
			});
				
		},
		onChangeJuzgado(item) {
			this.selectedItem.juzgado = item
			this.selectedItem.juzgado_id = (item ? item.id : null)	
		},	 
		onChangeRepOrigen(item) {
			this.selectedItem.tramite_sig.rep_origen = item
			this.selectedItem.tramite_sig.rep_origen_id = (item ? item.id : null)	
		},
		mostrarVueltaAnses () {
			return this.areaId == 1;
		},
		mostrarConciliacion() {
			return this.areaId == 2;
		},
	 	showConciliacion() {
	 		this.$emit('expJud:tramites-show-conciliacion')
	 	}				
	}
};	

</script>
<style scoped>
	
</style>
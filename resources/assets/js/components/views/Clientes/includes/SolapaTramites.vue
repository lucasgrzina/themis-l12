<template>
	<div>
		<div v-if="!amModal.show">
			<div class="box" v-if="hasAnyPerm(actionPerm+':R')">
	            <div class="box-header">
	              <h3 class="box-title">{{ historicos ? 'Trámites históricos' : 'Trámites pertenecientes al cliente' }}</h3>
					<div class="box-tools">
						<button-type type="refresh" @click="refresh()"></button-type>            	
					</div>	              
	            </div>
	            <!-- /.box-header -->
	            <div class="box-body no-padding">
	            	<div class="table-responsive">
		            <table class="table table-striped">
		            	<tbody>
		        			<tr>
			                  <th style="width: 50px">Req.</th>
			                  <th>Area</th>
			                  <th>Tipo Trámite</th>
			                  <th>Expediente</th>
			                  <th>Autos</th>
			                  <th>F.Inicio</th>
			                  <th>Estado</th>
			                  <th></th>
		                	</tr>
		                	<tr v-if="!list.loading && list.data.length > 0" v-for="(value,index) in list.data">
			                  <td>{{ value.requerimiento.id }}</td>
			                  <td v-html="$options.filters.areaLabel(value.area_id)"></td>
			                  <td>{{ value.requerimiento.tipo_tramite.nombre }}</td>
			                  <td>{{ value.expediente }}</td>
			                  <td>{{ value.requerimiento.autos }}</td>
			                  <td>{{ value.fecha_inicio }}</td>
			                  <td>{{ value.estado.nombre }}</td>
			                  <td style="text-align: right;" nowrap="">
			                  	<button-type v-can="[actionPerm+':R']" type="view-list" @click="onAction('view', value, index)"/>
								<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="edit-list" @click="onAction('edit', value, index)"/>
			                  </td>
			            	</tr>
			            	<tr v-if="list.data.length < 1 && !list.loading">
		            			<td colspan="7">El cliente no tiene tramites asignados.</td>
			            	</tr>
			            	<tr v-if="list.loading">
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
			<div class="box" v-else>
				<div class="box-body">
					<unauthorized-alert/>
				</div>
			</div>	      	
			<div class="row">
				<div class="col-xs-12 text-right">
					<template v-can="actionPerm+':R'">
				    	<button-type v-show="!historicos" type="historicos" @click="verHistoricos()"/>  
				    	<button-type v-show="historicos" type="tramites" @click="verActuales()"/>        
					</template>
			    	<button-type type="back" @click="back()"/>        
				</div>
			</div>
		</div>
		<modal v-model="amModal.show" class="themis-modal" effect="fade" :backdrop="false" :large="true">
			<div slot="modal-header" class="modal-header">
				<h4 class="modal-title">{{ amModal.title }}<br class="visible-xs"><span class="hidden-xs"> | </span>{{ cliente.nombre_completo }} - {{ cliente.cuit }}</h4>
			</div>
			<template v-if="amModal.loaded">			
				<c-u-tramite v-if="amModal.loaded" :baseUri="baseUri" :actionPerm="'tramites'" :info="infoTramites" :selectedItem="amModal.data" @clientes:tramites-close="closeTramiteModal" @clientes:tramites-saved="closeTramiteModal" :isModal="true" :canSave="amModal.canSave" ></c-u-tramite>
			</template>
			<pulse-loader :loading="true" v-else></pulse-loader>
			<div slot="modal-footer" class="modal-footer"></div>
		</modal>		
	</div>
</template>
<script>
import { mapGetters } from 'vuex'
import moment from 'moment'
import Vue from 'vue'
import { modal,datepicker,buttonGroup,radio } from 'vue-strap'
import config from '../../../../config'
import Api from '../../../../api'
import vSelect from "vue-select"
import draggable from 'vuedraggable'
import CUTramite from './CUTramite'
//import PulseLoader from 'vue-spinner/src/PulseLoader.vue'

export default {
  name: 'SolapaTramites',
  components: {
  	modal,
  	vSelect,
  	draggable,
  	datepicker,
  	buttonGroup,
  	radio,
  	CUTramite
  	//PulseLoader
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
		cliente: {
			type: Object,
			required: true
		},		
		actionPerm: {
			type: String,
			required: true
		},
		cuit: {
			type: String,
			default () {
				return "00000000000"
			}
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
		title: 'Tramites',
		firstLoad: true,
		list: {
			loading: true,
			data: []
		},
		info: {
			estados: []
		},		
		selectedItem: null,
		selectedIndex: -1,
		historicos: false,
		amModal: {
			title: 'Tramite N° ',
			canSave: false,
			submited: false,
			show: false,
			errors: '',
			data: {},
			saving: false,
			loaded: false
		},

		messages: '', 
		apiUrl: '',
		uri: this.baseUri.concat(this.clienteId).concat('/tramites/')
	}
  },  
  mounted () {
  	this.apiUrl = config.serverURI + this.uri;
	//this.getList();	
  },  
	computed: {
		...mapGetters([
			'hasAnyPerm'
		])
	},  
  methods: {
    onAction (action, data, index) {
    	this.$store.dispatch('hideSuccessNotification')
		switch(action) {
			case 'edit':
				this.selectedIndex = index;
				this.reset(_.clone(data, true));
				this.amModal.canSave = true;
				break;
			case 'view':
				this.selectedIndex = index;
				this.reset(_.clone(data, true));
				this.amModal.canSave = false;
				break;
			case 'remove':
				this.remove(data,index);
				break;      		
		}
    },  
    refresh () {
    	this.getList();
    },
    getList () {
    	if (this.clienteId && this.hasAnyPerm(this.actionPerm+':R')) {
	    	this.list.loading = true;
	    	let _uri = (this.historicos ? this.uri.concat('historicos/'): this.uri.concat('actuales/'));
	    	Api.get(_uri).then((result) => {
	    		this.list.data.length = 0;
	    		this.list.data = result.data;
	    		//this.historicos = historicos;
	    		this.list.loading = false;
	    		this.firstLoad = false;
	    	})
    	}
    },
    reset (item) {
    	this.selectedItem = item;
    	//this.amModal.loaded = false;
    	this.amModal.show = true;
		Api.combos('solapa-tramites/' + this.selectedItem.area_id).then(resp => {
		   	this.infoTramites = _.assign(resp.data,{
		   	})

			   	let _cuit = this.cuit ? this.cuit.replace(new RegExp('-', 'g'), '') : '00000000000';

		   		this.amModal.title = 'Trámite N° ' + this.selectedItem.id;
		   		this.amModal.data = this.selectedItem;
		   		this.amModal.data.cuit = _cuit;
		   		this.amModal.loaded = true;
		   		//this.amModal.data.requerimiento.responsables = this.selectedItem.responsables;
		})
    },
	closeTramiteModal (data) {
    	this.amModal = _.assign(this.amModal,{
    		data: [],
    		show: false,
    		submited: false,
    		loaded: false
    	})
    	this.selectedIndex = -1;
    	this.selectedItem = {};
    	if (data) {
    		this.getList();
    	}
	},    
    back() {
    	this.$emit('clientes:back','tramites')
    },
    verHistoricos() {
    	this.historicos = true;
    	this.getList();
  	},
  	verActuales() {
  		this.historicos = false;
  		this.getList();
  	}
  },
	watch: {
	    active(newVal) {
	      if (newVal && this.firstLoad) {
	      	this.getList();
	      }
	    }
    }  
};		
</script>
<style scoped>
	#frm .btn-remove-list{
		float: right;
	}
	.progress-description.del{
		text-align: center;
	}
	.progress-description.del a{
		color: #ffffff!important;
	}
	.progress-description.del a i{
		display: block;
	}
</style>
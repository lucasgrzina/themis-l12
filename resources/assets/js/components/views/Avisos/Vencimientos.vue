<template>
	<div>
		<c-u-cliente :selectedItem="selectedItem" :show="modalCliente.show" :actionPerm="'clientes'" :uri="'clientes/'" @clientes:saved="closeModalCliente" @clientes:back="closeModalCliente" :activeTab="modalCliente.activeTab"></c-u-cliente>		
		
		<div class="box" v-show="!modalCliente.show">
            <div class="box-header">
              <h3 class="box-title">Vencimientos</h3>
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
		                  <th style="width: 100px">Fecha</th>
		                  <th>Area</th>
		                  <th>Origen</th>
		                  <th>Cliente</th>
		                  <th>Nro. Trámite</th>
		                  <th>Tipo Trámite</th>
		                  <th>Est. Trámite</th>
		                  <th>Detalle</th>
		                  <!--th></th-->
	                	</tr>
	                	<template v-if="!list.loading">
		                	<tr v-for="(value,index) in authUser.vencimientos">
			                  <td>{{ value.fecha_vto | dateFormat('DD/MM/YYYY')}}</td>
			                  <td style="text-align:center;" v-html="$options.filters.areaLabel(value.area_id)"></td>
			                  <td v-html="value.tipo"></td>
			                  <td>{{ value.cliente }}</td>
			                  <td>{{ value.tramite_id }}</td>
			                  <td>{{ value.tipo_tramite }}</td>
			                  <td>{{ value.estado_tramite }}</td>
			                  <td>
			                  	<template v-if="value.detalle">
		                  			<dl class="dl-horizontal" v-html="mostrarDetalle(value.detalle)"></dl>
			                  	</template>
			                  </td>
			                  <!--td style="text-align: right;" nowrap="">
								<button-type v-can="['clientes:R']" type="view-list" @click="onAction('view', value, index)"/>
			                  </td-->
			            	</tr>
			            </template>
		            	<tr v-else="list.loading">
		            		<td colspan="8">
		            			<pulse-loader :loading="list.loading"></pulse-loader>
		            		</td>
		            	</tr>		            	
		        	</tbody>
	      		</table>
	      		</div>
            </div>
            <!-- /.box-body -->
      	</div>	     			
	</div>
</template>
<script>
import { mapState } from 'vuex'
import moment from 'moment'
import Vue from 'vue'
import { datepicker,modal } from 'vue-strap'
import config from '../../../config'
import Api from '../../../api'
import CUCliente from '../Clientes/includes/CU.vue'

export default {
  name: 'Vencimientos',
  components: {
  	datepicker,
  	CUCliente
  },
  data () {
	return {
		title: 'Vencimientos',
		list: {
			loading: true,
			data: []
		},		
		selectedItem: null,
		selectedIndex: -1,
		modalCliente: {
			show: false,
			activeTab: 0,
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
    ])
  },  
  mounted () {
  	this.apiUrl = config.serverURI + this.uri;
  	this.getList();
  },  
  methods: {
	    onAction (action, data, index) {
	    	this.$store.dispatch('hideSuccessNotification')
			switch(action) {
				case 'view':
					this.selectedIndex = index;
					this.reset(_.clone(data.cliente_id, true));
					//this.modalCliente.show = true;
					break;
			}
	    },  
	    getList () {
	    	this.list.loading = true;
	    	this.$store.dispatch('getVencimientos')
	    		.then(() => {
	    			this.list.loading = false;
	    		});  
	    },    
	    reset (id) {
	    	Api.get('clientes/'.concat(id)).then((response) => {
	    		this.selectedItem = response.data;
	    		this.modalCliente.show = true;
	    	});
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
	    mostrarDetalle(detalle) {
	    	let _detalle = JSON.parse(detalle);
	    	switch (_detalle.area_id) {
	    		case 1:
	    			return  "<dt>Haber mensual</dt><dd>"+ this.$options.filters.currency(_detalle.haber_mensual) + "</dd>" +
		                  	"<dt>Retroactivo</dt><dd>"+ this.$options.filters.currency(_detalle.retroactivo) + "</dd>";
	    			break;
	    		case 2:
	    		case 3:
	    		case 4:
	    			return  "<dt>Cuota acordada</dt><dd>"+ this.$options.filters.currency(_detalle.cuota_acordada) + "</dd>" +
		                  	"<dt>Retroactivo</dt><dd>"+ this.$options.filters.currency(_detalle.retroactivo) + "</dd>" +
		                  	"<dt>Honorarios</dt><dd>"+ this.$options.filters.currency(_detalle.honorarios) + "</dd>";
	    			break;	    			
	    		default:
	    			return "";
	    	}
	    }
        	
  }

};		
</script>
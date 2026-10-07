<template>
<div>
	<div class="box box-primary box-solid themis-modal">
        <div class="box-header with-border modal-header">
          <h3 class="box-title">Filtros</h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
          </div>
          <!-- /.box-tools -->
        </div>
        <div class="box-body">
	      	<fieldset class="transparente" :disabled="!canFilter">
	      	<form class="row">
				<!--div class="form-group col-sm-3" >
					<label for="codigo_desde">Codigo Desde</label>
					<input type="text" name="codigo_desde" v-model="filtros.codigo_desde" class="form-control">
				</div> 
				<div class="form-group col-sm-3" >
					<label for="codigo_hasta">Codigo Hasta</label>
					<input type="text" name="codigo_hasta" v-model="filtros.codigo_hasta" class="form-control">
				</div> 
				<div class="form-group col-sm-3" >
					<label for="nombre_desde">Nombre Desde</label>
					<input type="text" name="nombre_desde" v-model="filtros.nombre_desde" class="form-control">
				</div> 
				<div class="form-group col-sm-3" >
					<label for="nombre_hasta">Nombre Hasta</label>
					<input type="text" name="nombre_hasta" v-model="filtros.nombre_hasta" class="form-control">
				</div>				
				<div class="form-group col-sm-3" >
					<label for="cuit">CUIT</label>
					<input type="text" name="cuit" v-model="filtros.cuit" class="form-control">
				</div> 
				<div class="clearfix"></div-->
				<div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.desde')}">
					<label for="desde" style="display:block;">Fecha Alta Desde</label>
					<datepicker name="desde" v-model="filtros.desde" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
					<span class="help-block" v-show="errors.has('filtros.desde')">{{ errors.first('filtros.desde') }}</span>
				</div>	
				<div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.hasta')}">
					<label for="hasta" style="display:block;">Fecha Alta Hasta</label>
					<datepicker name="hasta" v-model="filtros.hasta" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
					<span class="help-block" v-show="errors.has('filtros.hasta')">{{ errors.first('filtros.hasta') }}</span>
				</div>		
				
				<div class="form-group col-sm-3">
					<label for="tipo_tramite_id">Tipo Tramite</label>
					  <select v-model="filtros.tipo_tramite_id" class="form-control" name="tipo_tramite_id">
					  		<option :value="null">Todos</option>
						  <option v-for="option in info.tipo_tramites" v-bind:value="option.id">
						    {{ option.nombre }}
						  </option>
					  </select>
				</div>
				<div class="form-group col-sm-3">
					<label for="estado_tramite_id">Estado Req.</label>
					  <select v-model="filtros.estado_req_id" class="form-control" name="estado_req_id">
					  		<option :value="null">Todos</option>
						  <option v-for="option in info.estado_req" v-bind:value="option.id">
						    {{ option.nombre }}
						  </option>
					  </select>
				</div>		
				<div class="clearfix"></div>
				<div class="form-group col-sm-3">
					<label for="colega_id">Recomendado por</label>
					  <select v-model="filtros.colega_id" class="form-control" name="colega_id">
					  		<option :value="null">Todos</option>
						  <option v-for="option in info.colegas" v-bind:value="option.id">
						    {{ option.nombre }}
						  </option>
					  </select>
				</div>	
				<div class="form-group col-sm-3">
					<label for="categoria">Categ. cliente</label>
					  <select v-model="filtros.categoria" class="form-control" name="categoria">
					  		<option :value="null">Todos</option>
					  		<option :value="'P'">Persona</option>
					  		<option :value="'E'">Empresa</option>
					  </select>
				</div>	
				<div v-if="soyResponsable()" class="form-group col-sm-3">
					<label for="responsable_id">Abogado Resp.</label>
					  <select v-model="filtros.responsable_id" class="form-control" name="responsable_id">
					  	  <option v-bind:value="0">Todos</option>
						  <option v-for="option in info.responsables" v-bind:value="option.id">
						    {{ option.name }}
						  </option>
					  </select>
				</div>												
				<div class="form-group col-sm-3">
					<label for="empresa_referencia_id">Empresa Referencia</label>
				  	<select v-model="filtros.empresa_referencia_id" class="form-control" name="empresa_referencia_id">
					  	<option :value="null">Todos</option>
						<option v-for="option in info.empresas_referencia" v-bind:value="option.id">
							{{ option.razon_social }}
						</option>
				  	</select>
				</div>		
															      	
									
	            <div class="form-group col-sm-3">
	            	<button-type type="filter" @click="doFilter" :disabled="!canFilter"/>
	            </div>				
	      	</form>     
	      	</fieldset>        	
        </div>
  	</div>
	<div class="box">
        <div  v-if="firstFilter" class="box-header">
          <h3 class="box-title">&nbsp;</h3>
          <div class="box-tools">
            <button-type type="print" @click="print()"/>
          </div>
        </div>
        <!-- /.box-header -->
        <div v-if="firstFilter" class="box-body table-responsive no-padding">
	        <table class="table">
	            <tbody>
		        	<tr>
		              <th>Estado Req.</th>
		              <th>Tipo Tramite</th>
		              <th v-if="area.id == 1">Expediente</th>
		              <th v-if="area.id == 5">Nro. Trámite</th>
		              <th>Fecha Inicio</th>

		              <th v-if="area.id == 1">Rep. Origen</th>
		              <template v-if="area.id == 2 || area.id == 3 || area.id == 4">
			              <th>Autos</th>
			              <th>Parte</th>
		              </template>
		              <th>Est. Tramite</th>
		              <template v-if="area.id == 1">
			              <th>Nro. Benef.</th>
			              <th>Fecha Benef.</th>
		              </template>
		              <template v-if="area.id == 2">
			              <th>Nro. Seclo</th>
			              <th>Fecha Seclo</th>
		              </template>
		              <template v-if="area.id == 3 || area.id == 4">
			              <th>Mediación</th>
			              <th>Fecha Mediación</th>
		              </template>

		            </tr>
		            <template v-for="item in list.data.data"  v-if="!list.loading">
			            <tr v-if="item.tipo && item.tipo === 'CABECERA'">
			              <td colspan="8" style="background: #cac8f3;">
			              	<span style="text-transform:uppercase;font-weight:bold;">({{ item.id_cliente }}) {{ item.nombre_cliente }}</span>
			              	<span style="margin-left:10px;">Fecha Alta: {{ item.fecha_alta | dateFormat }}</span>
			              </td>
			            </tr>
			            <tr v-else>
			              <td> {{ item.estado_requerimiento }} </td>
			              <td> {{ item.tipo_tramite }} </td>
			              <td v-if="area.id == 1"> {{ item.expediente }} </td>
			              <td v-if="area.id == 5"> {{ item.expediente }} </td>
			              <td> {{ item.tramite_f_inicio | dateFormat}} </td>
			              <td v-if="area.id == 1"> {{ item.rep_origen }} </td>
			              <template v-if="area.id == 2 || area.id == 3 || area.id == 4">
				              <td>{{ item.autos }}</td>
				              <td>{{ item.parte | parte }}</td>
			              </template>			              
			              <td> {{ item.estado_tramite }} </td>
			              <td> {{ item.nro_beneficio }} </td>
			              <td> {{ item.fecha_beneficio | dateFormat}} </td>
			            </tr>
		            	
		            </template>

		            <tr v-if="!list.loading && list.data.data && list.data.data.length == 0">
		            	<td colspan="8">No se encontraron resultados</td>
		            </tr>
		        	<tr v-if="list.loading">
		        		<td colspan="8">
		        			<pulse-loader :loading="list.loading"></pulse-loader>
		        		</td>
		        	</tr>            
	          	</tbody>
				<tfoot>
					<tr>
						<td colspan="2" v-if="list.data && list.data.total > 1">
							<strong>Total req. informados:</strong> {{ list.data.total }}
						</td>						
						<td colspan="6" class="text-right">
							<pagination v-if="list.data && list.data.total > 1" :limit="5"  :data="list.data" @pagination-change-page="changePage"></pagination>
						</td>
					</tr>	
				</tfoot>
	  		</table>
        </div>
        <div v-else class="box-body text-center">
        	<p>Para comenzar, cargue los parámetros y haga click en "Filtrar"</p>
        </div>
        <!-- /.box-body -->
  	</div>


</div>
</template>
<script>
import moment from 'moment'
import Vue from 'vue'
import { mapGetters,mapState } from 'vuex'
import { datepicker } from 'vue-strap'
import config from '../../../../config'
import Api from '../../../../api'
import vSelect from "vue-select"
import pagination from 'laravel-vue-pagination'
import { currency} from '../../../../filters'

export default {
	name: 'ReqEmp',
	components: {
		datepicker,
		vSelect,
		pagination
	},
	data() {
		return {
			canFilter: false,
			firstFilter: false,
			info: {
				areas: [],
				tipo_tramites: [],
				estado_req: [],
				empresas_referencia: [],
				responsables: [],
				colegas: []
			},
			apiUrl: '',
			uri: 'informes/requerimientos-empresas',			
			filtros: {
				desde: '',
				hasta: '',
				area_id: 0,
				user_id: 0,
				responsable_area: false,
				responsable_id: 0,
				empresa_referencia_id: null,
				tipo_tramite_id: null,
				estado_req_id: null,
				colega_id: null,
				categoria: null,
				page: 1
			},
			list: {
				pagination: {},
				data: {
					data: []
				},
				loading: false
			}
		}
	},
	computed: {
		area: function() {
			let _area = {
				id: 0,
				nombre: this.$route.params.area
			};
			switch(this.$route.params.area.toLowerCase()) {
				case 'previsional':
					_area.id = 1;
					break;
				case 'laboral':
					_area.id = 2;
					break;
				case 'civil':
					_area.id = 3;
					break;
				case 'comercial':
					_area.id = 4;
					break;
				case 'societario':
					_area.id = 5;
					break;

			}
			return _area;
		},
	    ...mapState([
	      'authUser'
	    ]),		
		...mapGetters([
			'isResponsableArea'
		])  		
	}, 	
	mounted () {
		this.filtros.area_id = this.area.id;
		this.getFilters();
	}, 	
	filters: {
		currency: currency
	},
	methods: {
		doFilter() {
			this.firstFilter = true;
			this.filtros.page = 1;
			this.filtros.user_id = this.authUser.id;
	    	this.errors.clear('filtros');

	    	/*if (this.filtros.codigo_desde === '') {
	    		this.addError('codigo_desde', 'Campo requerido','server','filtros');
	    	}
	    	if (this.filtros.codigo_hasta === '') {
	    		this.addError('codigo_hasta', 'Campo requerido','server','filtros');
	    	}*/

			this.$validator.validateAll('filtros').then((result) => {
	     	    if (result && this.errors.items.length < 1) {
	     	    	this.getData();
		        } else {
		        	this.list.loading = false;
		        }
	      	},errors => {
	      		this.list.loading = false;
	      	});

		},
		changePage(page) {
			this.filtros.page = page;
			this.getData();
		},
		getData() {
 	    	this.list.loading = true;
	    	Api.post(this.uri,this.filtros).then((result) => {
	    		this.list.data.data.length = 0;
	    		this.list.data = result.data;

	    		this.list.loading = false;
	    	}, errors => {
	    		this.list.data.data.length = 0;
	    		this.list.data = [];
	    		this.list.loading = false;
	    	})			    		      	
		},
		detalleBeneficio(detalle) {
			let _detalle = JSON.parse(detalle);
			return "<dt>Haber mensual</dt><dd>"+ currency(_detalle.haber_mensual) +"</dd><dt>Retroactivo</dt><dd>"+ currency(_detalle.retroactivo) +"</dd><dt>Mes alta</dt><dd>"+ _detalle.mes_alta +"</dd><dt>Agente Pagador</dt><dd>"+ _detalle.agente_pagador +"</dd>";
		},
		changeArea() {
			this.filtros.tipo_tramite_id = null;
			this.getFilters();
		},
		getFilters() {
			Api.combos('informes/requerimientos-empresas/'+this.filtros.area_id).then((resp) => {
				this.info = resp.data;
				this.canFilter = true;
			});				
		},
		print() {
			let queryString = Object.keys(this.filtros).map((key) => {
				if (this.filtros[key] != null) {
			    	return encodeURIComponent(key) + '=' + encodeURIComponent(this.filtros[key]);
			    }
			}).join('&');
			
			document.location = Laravel.webDomain.concat('/informes/requerimientos-empresas/?').concat(queryString);
		},		
		soyResponsable() {
			let res = this.isResponsableArea(this.area.id);
			if (res) {
				this.filtros.responsable_area = true;
			} else {
				this.filtros.responsable_area = false;
			}
			return res;
		}		
	}

};	

</script>
<style scoped>
	.form-group{
		vertical-align:top;
	}
	.pagination {
		margin-top: 0;
		margin-bottom: 0; 
	}
</style>
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
        <!-- /.box-header -->
        <div class="box-body">
        	<fieldset class="transparente" :disabled="!canFilter">
		      <form class="row">

					<div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.desde')}">
						<label for="desde" style="display:block;">Desde</label>
						<datepicker name="desde" v-model="filtros.desde" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
						<span class="help-block" v-show="errors.has('filtros.desde')">{{ errors.first('filtros.desde') }}</span>
					</div>	
					<div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.hasta')}">
						<label for="hasta" style="display:block;">Hasta</label>
						<datepicker name="hasta" v-model="filtros.hasta" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
						<span class="help-block" v-show="errors.has('filtros.hasta')">{{ errors.first('filtros.hasta') }}</span>
					</div>		
					<div class="clearfix"></div>
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
						<label for="tipo_tramite_id">Tipo Tramite</label>
						  <select v-model="filtros.tipo_tramite_id" class="form-control" name="tipo_tramite_id">
						  		<option :value="null">Todos</option>
							  <option v-for="option in info.tipo_tramites" v-bind:value="option.id">
							    {{ option.nombre }}
							  </option>
						  </select>
					</div>															      	
					<div class="form-group col-sm-3">
						<label for="estado_tramite_id">Estado Tramite</label>
						  <select v-model="filtros.estado_tramite_id" class="form-control" name="estado_tramite_id">
						  		<option :value="null">Todos</option>
							  <option v-for="option in info.estado_tra" v-bind:value="option.id">
							    {{ option.nombre }}
							  </option>
						  </select>
					</div>	
					<div class="form-group col-sm-3" v-if="area.id === 1">
						<label for="rep_origen_id">Rep. Origen</label>
						  	<select v-model="filtros.rep_origen_id" class="form-control" name="rep_origen_id">
						  		<option :value="null">Todos</option>
							  	<option v-for="option in info.rep_origen" v-bind:value="option.id">
							   		{{ option.nombre }}
							  	</option>
						  	</select>
					</div>				
		            <div class="form-group col-sm-3">
		            	<!--label style="display:block;" class="hidden-xs">&nbsp;</label-->
		            	<button-type type="filter" @click="doFilter" :disabled="!canFilter"/>
		            </div>
		      </form>             	
		    </fieldset>

        </div>
        <!-- /.box-body -->
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
		              <template v-if="area.id === 1">
		              	<th style="width:200px;">Cliente - CUIT/Beneficio</th>
		              	<th>Rep.Origen</th>	
		              	<th>Fecha Beneficio</th>
		              </template>
		              <template v-if="area.id === 2">
		              	<th style="width:200px;">Cliente - CUIT/Seclo</th>
		              	<th>Autos</th>	
		              	<th>Parte</th>	
		              	<th>Fecha</th>
		              </template>
		              <template v-if="area.id === 3 || area.id === 4">
		              	<th style="width:200px;">Cliente - CUIT/Mediación</th>
		              	<th>Autos</th>	
		              	<th>Parte</th>	
		              	<th>Fecha</th>
		              </template>		              
		              <th>Empresa</th>
		              <th>Colega</th>
		              <th>Responsables</th>
		              <th>Fecha Cobro</th>
		              <th>Detalle</th>
		            </tr>
		            <tr v-for="item in list.data.data"  v-if="!list.loading">
		              <td>
		              	<span :class="{'text-underline':item.categoria == 'E'}"><strong>{{ item.nombre_cliente }}</strong></span><br>
		              	<span><strong>{{ (item.cuit ? item.cuit : 'S/C') }}</strong></span><br>
		              	<span>{{ item.nro_beneficio }}</span>
		              </td>
		              <template v-if="area.id === 1">
		              	<td>{{ item.rep_origen }}</td>
		              	<td>{{ item.fecha_beneficio | dateFormat }}</td>
		              </template>
		              <template v-if="area.id === 2 || area.id === 3 || area.id === 4">
		              	<td>{{ item.autos }}</td>
		              	<td>{{ item.parte | parte}}</td>
		              	<td>{{ item.fecha_beneficio | dateFormat }}</td>
		              </template>
		              
		              <td>{{ item.empresa }}</td>
		              <td>{{ item.colega }}</td>
		              <td>{{ item.responsables }}</td>
		              <td>{{ item.fecha_cobro | dateFormat }}</td>
		              <td>
		              	<template>
                  			<dl class="dl-horizontal" v-html="detalleBeneficio(item.detalle)"></dl>		              		
		              	</template>
		              </td>
		            </tr>

		            <tr v-if="!list.loading && list.data && list.data.data.length == 0">
		            	<td colspan="11" class="text-center">No se encontraron resultados</td>
		            </tr>
		        	<tr v-if="list.loading">
		        		<td colspan="11">
		        			<pulse-loader :loading="list.loading"></pulse-loader>
		        		</td>
		        	</tr>            
	          	</tbody>
				<tfoot>
					<tr>
						<td colspan="4" v-if="list.data && list.data.total > 1">
							<strong>Total benf. informados:</strong> {{ list.data.total }}
						</td>
						<td colspan="4" class="text-right">
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
	name: 'InfBeneficios',
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
				estado_tra: [],
				rep_origen: [],
				responsables: []
			},
			apiUrl: '',
			uri: 'informes/beneficios',			
			filtros: {
				desde: moment().format('DD/MM/YYYY'),
				hasta: moment().format('DD/MM/YYYY'),
				area_id: 0,
				user_id: 0,
				responsable_area: false,
				responsable_id: 0,
				rep_origen_id: null,
				tipo_tramite_id: null,
				estado_tramite_id: null,
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
		...mapGetters([
			'isResponsableArea'
		]),
	    ...mapState([
	      'authUser'
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

	    	if (this.filtros.desde === '') {
	    		this.addError('desde', 'Campo requerido','server','filtros');
	    	}
	    	if (this.filtros.hasta === '') {
	    		this.addError('hasta', 'Campo requerido','server','filtros');
	    	}

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
			let _return = "";
			switch(_detalle.area_id) {
				case 1:
					_return = "<dt>Haber mensual</dt><dd>"+ currency(_detalle.haber_mensual) +"</dd><dt>Retroactivo</dt><dd>"+ currency(_detalle.retroactivo) +"</dd><dt>Mes alta</dt><dd>"+ _detalle.mes_alta +"</dd><dt>Agente Pagador</dt><dd>"+ _detalle.agente_pagador +"</dd>";
					break;
				case 2:
					_return = "<dt>Cuota acordada</dt><dd>"+ currency(_detalle.cuota_acordada) +"</dd><dt>Honorarios</dt><dd>"+ currency(_detalle.honorarios) +"</dd>";				
					break;
				case 3:
				case 4:
					_return = "<dt>Cuota acordada</dt><dd>"+ currency(_detalle.cuota_acordada) +"</dd><dt>Retroactivo</dt><dd>"+ currency(_detalle.retroactivo) +"</dd><dt>Honorarios</dt><dd>"+ currency(_detalle.honorarios) +"</dd>";				
					break;
			}
			return _return;
		},
		changeArea() {
			this.filtros.tipo_tramite_id = null;
			this.getFilters();
		},
		getFilters() {
			Api.combos('informes/beneficios/'+this.filtros.area_id).then((resp) => {
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
			
			document.location = Laravel.webDomain.concat('/informes/beneficios/?').concat(queryString);
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


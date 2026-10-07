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
					<div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.anses_desde')}">
						<label for="anses_desde" style="display:block;">Vuelta a Anses Desde</label>
						<datepicker name="anses_desde" v-model="filtros.anses_desde" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
						<span class="help-block" v-show="errors.has('filtros.anses_desde')">{{ errors.first('filtros.anses_desde') }}</span>
					</div>	
					<div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.anses_hasta')}">
						<label for="anses_hasta" style="display:block;">Vuelta a Anses Hasta</label>
						<datepicker name="anses_hasta" v-model="filtros.anses_hasta" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
						<span class="help-block" v-show="errors.has('filtros.anses_hasta')">{{ errors.first('filtros.anses_hasta') }}</span>
					</div>	
					<div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.desde')}">
						<label for="desde" style="display:block;">Estado Desde</label>
						<datepicker name="desde" v-model="filtros.desde" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
						<span class="help-block" v-show="errors.has('filtros.desde')">{{ errors.first('filtros.desde') }}</span>
					</div>	
					<div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.hasta')}">
						<label for="hasta" style="display:block;">Estado Hasta</label>
						<datepicker name="hasta" v-model="filtros.hasta" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
						<span class="help-block" v-show="errors.has('filtros.hasta')">{{ errors.first('filtros.hasta') }}</span>
					</div>	
					<div class="clearfix"></div>
					<div class="form-group col-sm-3">
						<label for="estado_anses_id">Estado</label>
						  <select v-model="filtros.estado_anses_id" class="form-control" name="estado_anses_id">
						  	  <option v-bind:value="0">Todos</option>
							  <option v-for="option in info.estados" v-bind:value="option.id">
							    {{ option.nombre }}
							  </option>
						  </select>
					</div>	
					<div class="form-group col-sm-3">
						<label for="ultimo_estado">Ultimo Estado</label>
						  <select v-model="filtros.ultimo_estado" class="form-control" name="ultimo_estado" :disabled="filtros.estado_anses_id == 0">
						  	  <option v-bind:value="false">NO</option>
						  	  <option v-bind:value="true">SI</option>
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
					<!--div class="form-group col-sm-3">
						<label for="estado_tramite_id">Estado Anses</label>
						  <select v-model="filtros.estado_tramite_id" class="form-control" name="estado_tramite_id">
						  		<option :value="null">Todos</option>
							  <option v-for="option in info.estado_tra" v-bind:value="option.id">
							    {{ option.nombre }}
							  </option>
						  </select>
					</div-->	
		            <div class="form-group col-sm-3">
		            	<label style="display:block;" class="hidden-xs">&nbsp;</label>
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
		            <tbody v-for="item in list.data.data" v-if="!list.loading">
			        	<tr style="background-color: #488db54a;">
			              <th style="width:30%;">Cliente</th>
			              <th style="width:20%;">CUIT/L</th>
			              <th style="width:25%;">Exp. Ppal.</th>
			              <th style="width:25%;">Beneficio</th>
			            </tr>
			            <tr >
			              <td><span style="text-transform:uppercase;">{{ item.cliente.nombre_completo }}</span></td>
			              <td>{{item.cliente.cuit}}</td>
			              <td>{{item.tramite_ant_id > 0 ? item.tramite_ant.expediente : ''}}</td>
			              <td>{{item.nro_beneficio}}</td>
			            </tr>
			            <tr style="background-color: #f5f5f5;">
			            	<th colspan="2" style="text-align:center;">Fecha vuelta Anses</th>
			            	<th colspan="2" style="text-align:center;">Fecha Vto. (120 días)</th>
			            </tr>
			            <tr>
		            		<td colspan="2" style="text-align:center;">{{ item.fecha_remision }}</td>
		            		<td colspan="2" style="text-align:center;">{{ item.fecha_remision_vto }}</td>
			            </tr>
			            <tr v-if="item.deceased" style="background-color: #f5f5f5;">
			            	<th colspan="2" style="text-align:center;">Nombre Conyuge</th>
			            	<th colspan="2" style="text-align:center;">Documento Conyuge</th>
			            </tr>
			            <tr v-if="item.deceased">
		            		<td colspan="2" style="text-align:center;">{{ item.cliente.nombre_conyuge ? item.cliente.nombre_conyuge + ' ' + item.cliente.apellido_conyuge : '--'}}</td>
		            		<td colspan="2" style="text-align:center;">{{ item.cliente.nro_doc_conyuge ? item.cliente.nro_doc_conyuge : '--' }}</td>
			            </tr>
			            <tr style="background-color: #f5f5f5;">
			            	<th style="text-align:right;">Fecha remisión</th>
			            	<th style="text-align:center;">Estado</th>
			            	<th colspan="2" style="text-align:left;">Observación</th>
			            </tr>
			            <template v-if="item.ultimo_estadio_anses">
				            <tr>
				            	<td style="text-align:right;">{{ item.ultimo_estadio_anses.fecha_remision }}</td>
				            	<td style="text-align:center;">{{ item.ultimo_estadio_anses.estado.nombre }}</td>
				            	<td colspan="2" style="text-align:left;">{{ item.ultimo_estadio_anses.observations }}</td>
				            </tr>
			            </template>
			            <template v-else>
				            <tr v-for="subitem in item.anses">
				            	<td style="text-align:right;">{{ subitem.fecha_remision }}</td>
				            	<td style="text-align:center;">{{ subitem.estado.nombre }}</td>
				            	<td colspan="2" style="text-align:left;">{{ subitem.observations }}</td>
				            </tr>
			            </template>
		          	</tbody>
		            <tr v-if="!list.loading && list.data.length > 0 && list.data.data.length == 0">
		            	<td colspan="8">No se encontraron resultados</td>
		            </tr>
		        	<tr v-if="list.loading">
		        		<td colspan="8">
		        			<pulse-loader :loading="list.loading"></pulse-loader>
		        		</td>
		        	</tr> 
					<tfoot v-if="!list.loading">
						<tr>
							<td colspan="2" v-if="list.data && list.data.total > 1">
								<strong>Total trámites informados:</strong> {{ list.data.total }}
							</td>
							<td colspan="6" class="text-right">
								<pagination v-if="list.data && list.data.total > 1"  :data="list.data" @pagination-change-page="changePage"></pagination>
							</td>
						</tr>	
					</tfoot>			        	  		          	
		  		</table>
		            <!--tr v-if="!list.loading && list.data.data && list.data.data.length == 0">
		            	<td colspan="8">No se encontraron resultados</td>
		            </tr>
		        	<tr v-if="list.loading">
		        		<td colspan="8">
		        			<pulse-loader :loading="list.loading"></pulse-loader>
		        		</td>
		        	</tr-->   

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
				estados: [],
				responsables: []
			},
			mostrarTodosResp: false,
			apiUrl: '',
			uri: 'informes/ucadep',			
			filtros: {
				desde: '',
				hasta: '',
				anses_desde: '',
				anses_hasta: '',				
				area_id: 1,
				user_id: 0,
				responsable_area: false,
				responsable_id: 0,
				estado_anses_id: 0,
				ultimo_estado: false,
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
		...mapGetters([
			'isResponsableArea'
		]),
	    ...mapState([
	      'authUser'
	    ])			  		
	}, 		
	mounted () {
		this.getFilters();
		/*Api.combos('responsables/'+this.filtros.area_id).then((resp) => {
			this.info.responsables = resp.data;
			this.filtros.responsable_id = 0;
			this.canFilter = true;
		});	*/		
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

	    	/*if (this.filtros.desde === '') {
	    		this.addError('desde', 'Campo requerido','server','filtros');
	    	}
	    	if (this.filtros.hasta === '') {
	    		this.addError('hasta', 'Campo requerido','server','filtros');
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
		getFilters() {
			Api.combos('informes/ucadep/'+this.filtros.area_id).then((resp) => {
				this.info = resp.data;
				this.canFilter = true;
				if (this.info.responsables.length > 1) {
					this.mostrarTodosResp = true;
					this.filtros.resp_id = null; 
				} else {
					this.mostrarTodosResp = false;
					if (this.info.responsables.length > 0) {
						this.filtros.resp_id = this.info.responsables[0].id; 
					}
				}
			});
		},
		print() {
			let queryString = Object.keys(this.filtros).map((key) => {
				if (this.filtros[key] != null) {
			    	return encodeURIComponent(key) + '=' + encodeURIComponent(this.filtros[key])
			    }
			}).join('&');
			
			document.location = Laravel.webDomain.concat('/informes/ucadep/?').concat(queryString);
		},		
		soyResponsable() {
			let res = this.isResponsableArea(this.filtros.area_id);
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
	.table > tbody + tbody {
	    border-top: 5px solid #ccc;
	}	
</style>


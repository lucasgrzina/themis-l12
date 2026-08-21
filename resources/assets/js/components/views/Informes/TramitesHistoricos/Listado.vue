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
				<div v-if="soyResponsable()" class="form-group col-sm-3">
					<label for="responsable_id">Abogado Resp.</label>
					  <select v-model="filtros.responsable_id" class="form-control" name="responsable_id">
					  	  <option v-bind:value="0">Todos</option>
						  <option v-for="option in info.responsables" v-bind:value="option.id">
						    {{ option.name }}
						  </option>
					  </select>
				</div>					
				<!--div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.area_id')}">
					<label for="area_id">Área</label>
					  <select v-model="filtros.area_id" class="form-control" name="area_id" v-validate="'required'" data-vv-validate-on="none">
						  <option v-for="option in info.areas" v-bind:value="option.id">
						    {{ option.nombre }}
						  </option>
					  </select>
					<span class="help-block" v-show="errors.has('area_id')">{{ errors.first('area_id') }}</span>
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
	            <tbody>
		        	<tr>
						<template v-if="area.id === 1">
							<th>Expediente</th>		
						</template>
						<th>Cliente</th>
						<th>Fecha</th>
						<th>Empresa Ref.</th>
						<th>Abogados Resp.</th>
						<th>Tipo Tramite</th>
						<th>Fecha Archivo</th>
						<th>Usuario Archivo</th>
		            </tr>
		            <tr v-for="item in list.data.data" v-if="!list.loading">
						<template v-if="area.id === 1">
							<td>{{ item.expediente }}</td>
						</template>
						<td>{{ item.nombre_cliente }}</td>
						<td>{{ item.tramite_f_inicio | dateFormat }}</td>
						<td>{{ item.empresas_referencia }}</td>
						<td>{{ item.responsables }}</td>
						<td>{{ item.tipo_tramite }}</td>
						<td>{{ item.fecha_archivo | dateFormat }}</td>
						<td>{{ item.usuario_archivo }}</td>
		            </tr>

		            <tr v-if="!list.loading && list.data.length > 0 && list.data.data.length == 0">
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
							<strong>Total trámites informados:</strong> {{ list.data.total }}
						</td>							
						<td colspan="6" class="text-right">
							<pagination v-if="list.data.data.length > 0" :limit="5" :data="list.data" @pagination-change-page="changePage"></pagination>
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

export default {
	name: 'InfExpJud',
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
				areas: []
			},
			apiUrl: '',
			uri: 'informes/tramites-historicos',			
			filtros: {
				/*desde: moment().subtract(1,'years').format('DD/MM/YYYY'),
				hasta: moment().format('DD/MM/YYYY'),*/
				desde: '',
				hasta: '',
				area_id: 0,
				user_id: 0, 
				responsable_area: false,
				responsable_id: 0,
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
		this.apiUrl = config.serverURI.concat('informes').concat(this.uri);
		this.filtros.area_id = this.area.id;
		Api.combos('responsables/'+this.area.id).then((resp) => {
			this.info.responsables = resp.data;
			this.filtros.responsable_id = 0;
			this.canFilter = true;
		});			
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
		print() {
			let queryString = Object.keys(this.filtros).map((key) => {
				if (this.filtros[key] != null) {
			    	return encodeURIComponent(key) + '=' + encodeURIComponent(this.filtros[key]);
			    }
			}).join('&');
			
			document.location = Laravel.webDomain.concat('/informes/tramites-historicos/?').concat(queryString);
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


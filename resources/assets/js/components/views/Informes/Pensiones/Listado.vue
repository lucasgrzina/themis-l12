<template>
<div>
	<div class="box">
        <div class="box-header">
          <!--h3 class="box-title">&nbsp;</h3>
          <div class="box-tools">
          	<button-type type="filter" @click="doFilter" :disabled="!canFilter"/>
            <button v-if="firstFilter" type="button" class="btn btn-default"><i class="fa fa-print"></i></button>
          </div-->
        </div>
        <!-- /.box-header -->
        <div v-if="firstFilter" class="box-body table-responsive no-padding">
	        <table class="table">
	            <tbody>
		        	<tr>
		              <th style="width:200px;">Cliente</th>
		              <th>Tipo Doc.</th>
		              <th>Nro. Doc.</th>
		              <th>Cónyuge</th>
		              <th>Tipo Doc. Conyuge</th>
		              <th>Nro. Doc. Conyuge</th>
		            </tr>
		            <tr v-for="item in list.data.data">
		              <td>
		              	<span style="text-transform:uppercase;">{{ item.cliente }}</span>
		              </td>
		              <td>{{ item.tipo_doc }}</td>
		              <td>{{ item.nro_doc }}</td>
		              <td>{{ item.nombre_causante }}</td>
		              <td>{{ item.tipo_doc_causante }}</td>
		              <td>{{ item.nro_doc_causante }}</td>
		            </tr>

		            <tr v-if="!list.loading && list.data.data && list.data.data.length == 0">
		            	<td colspan="6">No se encontraron resultados</td>
		            </tr>
		        	<tr v-if="list.loading">
		        		<td colspan="6">
		        			<pulse-loader :loading="list.loading"></pulse-loader>
		        		</td>
		        	</tr>            
	          	</tbody>
				<tfoot>
					<tr>
						<td colspan="2" v-if="list.data && list.data.total > 1">
							<strong>Total pensiones informadas:</strong> {{ list.data.total }}
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
import { datepicker } from 'vue-strap'
import config from '../../../../config'
import Api from '../../../../api'
import vSelect from "vue-select"
import pagination from 'laravel-vue-pagination'

export default {
	name: 'InfPensiones',
	components: {
		datepicker,
		vSelect,
		pagination
	},
	data() {
		return {
			firstFilter: false,
			uri: 'informes/pensiones',			
			filtros: {
				page: 1
			},
			list: {
				data: {
					data: []
				},
				loading: false
			}
		}
	},
	mounted() {
		this.doFilter();
	},

	methods: {
		doFilter() {
			this.firstFilter = true;
			this.filtros.page = 1;
 	    	this.getData();
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


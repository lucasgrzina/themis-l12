<template>
	<div>
	<filter-bar></filter-bar>
	<vuetable 
		ref="vuetable"
      api-url="http://127.0.0.1:8000/api/acciones-controladas"
      :fields="fields"
      :sort-order="sortOrder"
      :css="css.table"
      pagination-path="data"
      data-path="data.data"
      :http-options="httpOptions"
      @vuetable:pagination-data="onPaginationData"
    >
		<template slot="actions" slot-scope="props">
		    <div class="table-button-container">
		        <button class="btn btn-default" @click="onClick('edit-item', props.rowData)"><i class="fa fa-edit"></i> View</button>&nbsp;&nbsp;
		        <button class="btn btn-danger" @click="onClick('delete-item', props.rowData)"><i class="fa fa-remove"></i> Edit</button>&nbsp;&nbsp;
		    </div>
		</template>
    </vuetable>
    <vuetable-pagination ref="pagination" :css="css.pagination"
      @vuetable-pagination:change-page="onChangePage"
    ></vuetable-pagination>	
	</div>
</template>
<script>
import Vue from 'vue'
import jwtToken from '../../../helpers/jwt-token'
import Vuetable from 'vuetable-2/src/components/Vuetable'
import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'


export default {
	name: 'ListadoAcciones',
	components: {
		Vuetable,
		VuetablePagination
	},
	data () {
		return {
			httpOptions: {
				headers: {Authorization: "Bearer " + jwtToken.getToken()}
			},
		  fields: [
	        {
	          name: '__checkbox',
	          titleClass: 'text-center',
	          dataClass: 'text-center',
	        },		  
	        {
	          name: '__sequence',
	          title: '#',
	          titleClass: 'text-right',
	          dataClass: 'text-right'
	        },		  
		  	'nombre','__slot:actions'
		  ],
		  sortOrder: [
		    {
		      field: 'nombre',
		      sortField: 'nombre',
		      direction: 'asc'
		    }
		  ],
		  moreParams: {},
			css: {
			        table: {
			          tableClass: 'table table-bordered table-striped table-hover',
			          ascendingIcon: 'glyphicon glyphicon-chevron-up',
			          descendingIcon: 'glyphicon glyphicon-chevron-down'
			        },
			        pagination: {
			          wrapperClass: 'pagination',
			          activeClass: 'active',
			          disabledClass: 'disabled',
			          pageClass: 'page',
			          linkClass: 'link',
			          icons: {
			            first: '',
			            prev: '',
			            next: '',
			            last: '',
			          },
			        },
			        icons: {
			          first: 'glyphicon glyphicon-step-backward',
			          prev: 'glyphicon glyphicon-chevron-left',
			          next: 'glyphicon glyphicon-chevron-right',
			          last: 'glyphicon glyphicon-step-forward',
			        },
			      },		  
		}
	},

	methods: {
	    onPaginationData (paginationData) {
	      this.$refs.pagination.setPaginationData(paginationData)
	    },
	    onChangePage (page) {
	      this.$refs.vuetable.changePage(page)
	    },
	    editRow(rowData){
	      alert("You clicked edit on"+ JSON.stringify(rowData))
	    },
	    deleteRow(rowData){
	      alert("You clicked delete on"+ JSON.stringify(rowData))
	    },
	    onClick(action,rowData) {
	    	console.log(action);
	    	console.debug(rowData);
	    }
  	},
	  events: {
	    'filter-set' (filterText) {
	      this.moreParams = {
	        filter: filterText
	      }
	      Vue.nextTick( () => this.$refs.vuetable.refresh() )
	    },
	    'filter-reset' () {
	      this.moreParams = {}
	      Vue.nextTick( () => this.$refs.vuetable.refresh() )
	    }
	  }  		
}	
</script>
<style>
.pagination {
  margin: 0;
  float: right;
}
.pagination a.page {
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 10px;
  margin-right: 2px;
}
.pagination a.page.active {
  color: white;
  background-color: #337ab7;
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 10px;
  margin-right: 2px;
}
.pagination a.btn-nav {
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 7px;
  margin-right: 2px;
}
.pagination a.btn-nav.disabled {
  color: lightgray;
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 7px;
  margin-right: 2px;
  cursor: not-allowed;
}
.pagination-info {
  float: left;
}
</style>
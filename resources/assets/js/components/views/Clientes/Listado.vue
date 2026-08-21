<template>
<div>
	<c-u-cliente :selectedItem="selectedItem" :show="cu.show" :actionPerm="actionPerm" :uri="uri" @clientes:saved="saved" @clientes:back="closeAmModal" :activeTab="cu.activeTab"></c-u-cliente>

    <data-table v-show="!cu.show"
    	ref="list"
      :api-url="apiUrl"
      :fields="list.fields"
      :sort-order="list.sortOrder"
      :append-params="list.moreParams"
      :actionPerm="actionPerm"
      @create="create"
      @remove-selected="removeSelected"
      :showRemoveSelected="false"
      >
      <!--template slot="slot-filter-bar">
      	<filter-bar @clientes:filter-set="onFilterSet"></filter-bar>
      </template-->
      <template slot="slot-top-actions">
      	<top-actions @vuetable:create="create" @vuetable:remove-selected="removeSelected" :actionPerm="actionPerm"></top-actions>
      </template>      
      <template slot="actions" slot-scope="props">
			<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="edit-list" @click="onAction('edit', props.rowData, props.rowIndex)"/>
			<button-type v-can="actionPerm+':D'" type="remove-list" @click="onAction('remove', props.rowData, props.rowIndex)"/>
      </template>
    </data-table>
</div>
</template>

<script>
import Vue from 'vue'
import DataTable from '../../shared/datatable/DataTable'

import config from '../../../config'
import Api from '../../../api'
import FilterBar from './FilterBar'
import TopActions from './TopActions'
import CUCliente from './includes/CU.vue'
import moment from 'moment'
export default {
  name: 'ListadoClientes',
  components: {
    DataTable,
    FilterBar,
    TopActions,
    CUCliente
  },
  data () {
	return {
		info: {},
		
		selectedItem: null,
		list: {
			fields: [
			    {
			      name: '__checkbox',
			      titleClass: 'text-center',
			      dataClass: 'text-center',
			    },		  
			    {
			      name: 'id',
			      title: 'ID',
			      titleClass: 'text-center',
			      dataClass: 'text-center',
			      sortField: 'id'
			    },	
			  	{
			  		name: 'personeria',
			  		title: 'Personeria',
			  		callback: 'tagAssoc|{"H": ["Humana","bg-yellow"],"J":["Jurídica","bg-red"]}'
			  	},
			  	{
			  		name: 'categoria',
			  		title: 'Categoría',
			  		callback: 'tagAssoc|{"P": ["Persona","bg-purple"],"E":["Empresa","bg-aqua"]}'
			  	},			  	
			  	{
			  		name: 'nombre_completo',
			  		title: 'Denominación',
			  		sortField: 'nombre_completo'
			  	},
			  	{
			  		name: 'telefonos',
			  		title: 'Teléfonos',
			  		callback: 'formatTelEmail'
			  	},			  	
			  	{
			  		name: '__slot:actions',	
					titleClass: 'text-right',
					dataClass: 'text-right'		  		
			  	}
			],
			sortOrder: [
				{
					name: 'nombre_completo',
					sortField: 'nombre_completo',
					direction: 'asc'
				}
			],
			moreParams: {},
		},
		cu: {
			show: false,
			activeTab: 0,
		},
		uri: 'clientes/',
		actionPerm: 'clientes',
		apiUrl: '',
	}
  },  
  mounted () {
  	this.apiUrl = config.serverURI + this.uri;
  },  
  methods: {
  	onFilterSet (filter) {
  		this.list.moreParams.searchJoin = 'and';
  		this.list.moreParams.search = this.$options.filters.jsonToSearcheable(_.assign({},filter));
		Vue.nextTick( () => {
			if (this.$refs.list.$refs.vuetable) {
			  this.$refs.list.$refs.vuetable.refresh()
			} 
		})  		
  	},
    onAction (action, data, index) {
    	this.$store.dispatch('hideSuccessNotification')
		switch(action) {
			case 'edit':
			this.reset(_.clone(data, true));
			this.cu.show = true;
				break;
			case 'remove':
				this.remove(data);
				break;      		
		}
    },  
    create (personeria) {
    	this.reset({personeria: personeria});
    	this.cu.show = true
    },
    saved (data) {
    	this.$refs.list.$refs.vuetable.refresh();
    	this.closeAmModal();
    },
    remove (item) {
		this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
			.then((dialog) => {
	        	dialog.loading(true)
	        	Api.delete(this.uri,item)
	        		.then((result) => {
	        			dialog.close()
	        			this.$store.dispatch('showSuccessNotification',result.data.message)
	    				this.$refs.list.$refs.vuetable.refresh();
	        		},(error) => {
	        			this.$store.dispatch('showErrorNotification',error.message)
	        			dialog.close()
	        		})  			
			})    	
    },
    removeSelected (ids) {
    	this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
			.then((dialog) => {
				dialog.loading(true)
		    	Api.delete(this.uri.concat('eliminar-seleccion'),{ids: ids})
		    		.then((result) => {
		    			dialog.close()
		    			this.$refs.list.$refs.vuetable.refresh()
						this.$store.dispatch('showSuccessNotification',result.data.message)
		    		},() => {
		    			dialog.close()
		    		})
			})     	
    },
    reset (item) {
	    this.selectedItem = _.assign({
	      nombre_completo: null,
	      id: 0,
	      fecha_nac: '',
	      fecha_ing_pais: '',
	      pais_id: 6,//ARGENTINA
	      fecha_entrevista: moment().format('DD/MM/YYYY'),
	      telefonos: [],
	      emails: [],
	      categoria: (item.personeria == 'H' ? 'P' : null),
	      tipo_aporte_id: null,
	      empresa_referencia_id: null,
	      nacionalidad: null,
	      documentos: [],
	      fecha_vto_directorio: '',
	      provincia_id: '',
	      tipo_sociedad_id: '',
	      cond_iva_id: '',
	      libros_estudio: '',
	      sexo: '',
	      tipo_doc_id: '',
	      nacionalidad: '',
	      pais_id: '',
	      estado_civil: '',
	      tipo_doc_conyuge_id: '',
	    },item);
	    this.$store.dispatch('hideSuccessNotification')
    },
    closeAmModal () {
    	this.cu = _.assign(this.cu,{
    		show: false,
    	})
    	this.selectedItem = null;
    	this.$store.dispatch('hideErrorNotification')
    	//this.reset()
    }
  }
};
</script>

<style>
</style>
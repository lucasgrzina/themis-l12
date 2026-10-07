<template>
<div>
    <data-table
    	ref="list"
      :api-url="apiUrl"
      :fields="list.fields"
      :sort-order="list.sortOrder"
      :append-params="list.moreParams"
      :actionPerm="actionPerm"
      @create="create"
      @remove-selected="removeSelected">
      <template slot="actions" slot-scope="props">
			<button-type v-if="canEdit()" type="edit-list" @click="onAction('edit', props.rowData, props.rowIndex)"/>
			<button-type v-if="canEdit()" type="remove-list" @click="onAction('remove', props.rowData, props.rowIndex)"/>
      </template>
      <template slot="slot-top-actions">
      	<top-actions @time-gestion:create="create" :showButton="canEdit()"></top-actions>
      </template>        
    </data-table>

	<modal v-model="amModal.show"  class="themis-modal" effect="fade" :backdrop="false">
	  <div slot="modal-header" class="modal-header">
	    <h4 class="modal-title">
	    	{{ amModal.title }}
	    </h4>
	  </div>
	  <div slot="modal-body" class="modal-body" v-if="selectedItem" @keyup.enter="save()" @keyup.esc="closeAmModal()">
	  	<modal-errors :messages="amModal.errors"/>
	  	<div class="row">
			<div class="form-group col-sm-12" :class="{'has-error': errors.has('nombre')}">
				<label for="nombre">Nombre</label>
				<input v-focus type="text" name="nombre" v-model="selectedItem.nombre" class="form-control" v-validate="'required'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('nombre')">{{ errors.first('nombre') }}</span>
			</div>
			<div class="checkbox  col-sm-12">
			  <label><input type="checkbox" v-model="selectedItem.vigente">Vigente</label>
			</div>			
		</div>
	  </div>
	  <div slot="modal-footer" class="modal-footer">
	    <button-type type="close" @click="closeAmModal()"/>
	    <button-type type="save" :promise="save"/>
	  </div>
	</modal>
   
</div>
</template>

<script>
import Vue from 'vue'
import { modal } from 'vue-strap'
import DataTable from '../../shared/datatable/DataTable'
import config from '../../../config'
import Api from '../../../api'
import { mapGetters } from 'vuex'
import TopActions from './includes/TopActionsGestion'

export default {
  name: 'ListadoTimeGestion',
  components: {
    DataTable,
    modal,
    TopActions
  },
  data () {
	return {
		selectedItem: null,
		list: {
			fields: [
			    {
			      name: 'id',
			      title: 'ID',
			      titleClass: 'text-center',
			      dataClass: 'text-center',
			      sortField: 'id'
			    },		  
			  	{
			  		name: 'nombre',
			  		title: 'Nombre',
			  		sortField: 'nombre'
			  	},
			  	{
			  		name: 'vigente',
					titleClass: 'text-center',
					dataClass: 'text-center',			  		
			  		title: 'Vigente',
			  		sortField: 'vigente',
			  		callback: 'booleanLabel',
			  	},
			  	{
			  		name: '__slot:actions',	
					titleClass: 'text-right',
					dataClass: 'text-right'		  		
			  	}
			],
			sortOrder: [
				{
					name: 'id',
					sortField: 'id',
					direction: 'asc'
				}
			],
			moreParams: {},
		},
		amModal: {
			title: 'Crear/Editar Gestión',
			submited: false,
			show: false,
			errors: '' 
		},
		uri: 'time-gestion/',
		actionPerm: '',
		apiUrl: '',
	}
  },  
  computed: {
    ...mapGetters([
    	'isTimeLevel'
    ])      
  },  
	mounted () {
		this.apiUrl = config.serverURI + this.uri;
	},  
  methods: {
    onAction (action, data, index) {
    	this.$store.dispatch('hideSuccessNotification')
		switch(action) {
			case 'edit':
			this.reset(_.clone(data, true));
			this.amModal.show = true;
				break;
			case 'remove':
				this.remove(data);
				break;      		
		}
    },  
    create () {
    	this.reset();
    	this.amModal.show = true
    },
    save () {
    	this.amModal.errors = ''
		return this.$validator.validateAll().then((result) => {
	        if (result) {
	        	return Api.store(this.uri,this.selectedItem)
	        		.then((result) => {
	        				this.$refs.list.$refs.vuetable.refresh();
	        				this.closeAmModal();
	        				this.$store.dispatch('showSuccessNotification',result.data.message)
	        		}, (resp) => {
	        			this.amModal.errors = resp.message
	        			if (resp.fields) {
	        				for(var key in resp.fields) {
								this.addError(key, resp.fields[key][0], 'server'); 								    	
						   	}	        				
	        			}
	        		});
	          	return;
	        }
      	})
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
		    			this.$store.dispatch('showErrorNotification','No se puede eliminar el registro ya que se encuentra en uso en carga de horas')

	        			dialog.close()
	        		})  			
			})    	
    },
    removeSelected (ids,dialog) {
    	dialog.loading(true)
    	Api.delete(this.uri.concat('eliminar-seleccion'),{ids: ids})
    		.then((result) => {
    			dialog.close()
    			this.$refs.list.$refs.vuetable.refresh()
				this.$store.dispatch('showSuccessNotification',result.data.message)
    		},(error) => {
    			dialog.close()
    		})
    },
    reset (item) {
	    this.selectedItem = item || {
	      nombre: null,
	      id: 0,
	      vigente: true,
	      modificable: true
	    }
	    this.clearErrors()   	
    },
    clearErrors() {
	    this.amModal.errors = ''
	    this.$validator.reset()    	
    },    
    closeAmModal () {
    	this.amModal = _.assign(this.amModal,{
    		show: false,
    		submited: false
    	})
    	this.reset()
    },
    canEdit () {
    	return this.isTimeLevel(5) || this.isTimeLevel(1)
    }
  }
}
</script>

<style>
</style>
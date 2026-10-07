<template>
<div>
    <data-table
    	ref="list"
      :api-url="apiUrl"
      :fields="list.fields"
      :sort-order="list.sortOrder"
      :append-params="list.moreParams"
      :actionPerm="'roles'"
      :loadOnStart="true"
      @create="create"
      @remove-selected="removeSelected">
      <template slot="actions" slot-scope="props">
		<button-type v-can="'roles:U'" type="edit-list" @click.native="onAction('edit', props.rowData, props.rowIndex)"/>
		<button-type v-can="'roles:D'" type="remove-list" @click.native="onAction('remove', props.rowData, props.rowIndex)"/>
      </template>
    </data-table>


	<modal v-model="amModal.show" class="themis-modal" effect="fade" :backdrop="false">
	  <div slot="modal-header" class="modal-header">
	    <h4 class="modal-title">
	    	{{ amModal.title }}
	    </h4>
	  </div>
	  <div slot="modal-body" class="modal-body" v-if="selectedItem">
	  	<div class="row">

			<div class="callout callout-danger" v-show="amModal.errors.length > 0">
                <h4>Atención!</h4>
                <ul>
            		<li v-for="err in amModal.errors">{{ err }}</li>
                </ul>
            </div>

			<div class="form-group col-sm-12" :class="{'has-error': errors.has('name')}">
				<label for="name">Nombre</label>
				<input v-focus type="text" name="name" v-model="selectedItem.name" class="form-control" v-validate="'required'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('name')">{{ errors.first('name') }}</span>
			</div>

			<div class="clearfix"></div>

			<div class="form-group col-sm-6">
				<label for="accionControlada">Acciones</label>

				  <select class="form-control" name="accionControlada" v-model="amModal.accionControlada">
					  <option v-for="option in info.accionesControladas" v-bind:value="option">
					    {{ option.nombre }}
					  </option>
				  </select>
			</div>
			<div class="form-group col-sm-6">
				<div v-if="amModal.accionControlada" v-for="acc in info.acciones">
				  <input type="checkbox" :value="amModal.accionControlada.nombre_permiso+':'+acc.oper" v-model="selectedItem.permissions">
				  <label>{{ acc.name.toUpperCase() }}</label>
				</div>				
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
import VeeValidate from 'vee-validate'
import Api from '../../../api'
import {UPDATE_AUTH_USER_ROLE} from '../../../mutation-types'
import ButtonType from '../../shared/buttons/ButtonType'

/*Vue.use(VeeValidate,{
	fieldsBagName: 'inputs'
});*/

export default {
  name: 'ListadoRoles',
  components: {
    DataTable,
    modal,
    VeeValidate,
    ButtonType    
  },
  data () {
	return {
		selectedItem: null,
		info: {
			accionesControladas: [],
			acciones: [{oper: 'C',name: 'Crear'},{oper: 'R', name:'Consultar'},{oper: 'U',name:'Modificar'},{oper: 'D',name:'Eliminar'}]
		},		
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
			  		name: 'name',
			  		title: 'Nombre',
			  		sortField: 'name'
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
			title: 'Crear/Editar Rol',
			submited: false,
			show: false,
			errors: [],
			accionControlada: null,
			

		},
		apiUrl: config.serverURI + 'usuarios/roles',
	}
  },  
  mounted () {
  	Api.combos('acciones').then((resp) => {
  		this.info.accionesControladas = resp.data;
	});

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
    	this.clearErrors();
		return this.$validator.validateAll().then((result) => {
	        if (result) {
	        	var method = this.selectedItem.id > 0 ? 'PUT' : 'POST';
	        	var uri = 'usuarios/roles/{id}'.replace('{id}',this.selectedItem.id > 0 ? this.selectedItem.id : '');
	        	
	        	return Api.request(method,uri,this.selectedItem)
	        		.then((result) => {
	        				if (this.$store.getters.isUserRole(this.selectedItem)) {
	        					this.$store.dispatch('updateRole',this.selectedItem);
	        				}
	        				this.$refs.list.$refs.vuetable.refresh();
	        				this.closeAmModal();
	        				this.$store.dispatch('showSuccessNotification',result.data.message)
	        		}, (resp) => {
	        			if (resp.fields) {
	        				for(var key in resp.fields) {
								this.addError(key, resp.fields[key][0], 'server'); 								    	
						   	}	        				
	        			}
	        		});
	          	//return;
	        }
      	});    	
    },
    remove (item) {
    	if (confirm('¿Deséa continuar con la eliminación del registro?')) {
        	var uri = 'usuarios/roles/{id}'.replace('{id}',item.id);
        	
        	Api.request('DELETE',uri,{})
        		.then((result) => {
        			this.$store.dispatch('showSuccessNotification',result.data.message)
    				this.$refs.list.$refs.vuetable.refresh();
        		});    		
    	}
    },
    removeSelected (ids) {
    	var uri = 'usuarios/roles/eliminar-seleccion';
    	Api.request('DELETE',uri,{ids: ids})
    		.then((result) => {
    			this.$refs.list.$refs.vuetable.refresh();
				this.$store.dispatch('showSuccessNotification',result.data.message)
    		});
    },
    reset (item) {
    	var _permissions = [];
    	if (item) {
    		_permissions = item.permissions || [];	
	    	item.permissions = [];
    	}
    	
	    this.selectedItem = item || {
	      name: null,
	      id: 0,
	      permissions: []
	    };
	    this.amModal.accionControlada = this.info.accionesControladas[0];
	    for(let i in _permissions) {
	    	this.selectedItem.permissions.push(_permissions[i].name);
	    }

	    this.clearErrors();    	
    },
    clearErrors() {
	    this.amModal.errors = ''
	    this.$validator.reset()    	
    },  
    closeAmModal () {
    	this.amModal = _.assign({
    		errors: [],
    		show: false,
    		submited: false,
    		accionControlada: null
    	});

    	this.reset();
    }
  }
}
</script>

<style>
</style>
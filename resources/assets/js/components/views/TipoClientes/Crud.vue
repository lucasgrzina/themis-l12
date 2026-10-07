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
      <template slot="slot-top-actions">&nbsp;</template>
      <template slot="actions" slot-scope="props">
			<button-type v-can="actionPerm+':U'" type="edit-list" @click="onAction('edit', props.rowData, props.rowIndex)"/>
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
			<div class="form-group col-sm-12">
				<label for="nombre">Nombre</label><br>
				<span>{{ selectedItem.nombre }}</span>
			</div>
			<div class="form-group col-sm-3">
				<label for="sigla">Sigla</label><br>
				<span>{{ selectedItem.sigla }}</span>
			</div>
		</div>
		<div class="row">
			<label for="doc_req" class="col-xs-12">Doc. Requerida</label>
			<div class="col-sm-10">
				<v-select
					:value="info.docRequerida.selected"
					:clearSearchOnSelect="true"
					:debounce="5000"
					:on-search="getOptions"
					:options="info.docRequerida.data"
					:on-change="onChangeDocReq"
					placeholder="Ingresa el nombre de la doc."
					label="nombre"
				>
				</v-select>				
			</div>
			<div class="col-sm-2">
				<button-type type="add" @click="addDocRequerida()" v-show="this.info.docRequerida.selected"/>
			</div>
			<div class="col-xs-12">
            <draggable :list="selectedItem.doc_requerida" element="ul" class="list-group" style="margin-top:20px;">
                <li class="list-group-item" v-for="(item, index) in selectedItem.doc_requerida">
                	<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="remove-list" @click="removeDocReq(index)"/>
                	<i class="fa fa-arrows handle pull-left"></i>
                	<span class="badge pull-left">{{ (index+1) }}</span>
                	{{ item.doc.nombre }}
                </li>
             </draggable>				
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
import RadioButtonGroup from '../../shared/buttons/RadioButtonGroup'
import config from '../../../config'
import Api from '../../../api'
import vSelect from "vue-select"
import draggable from 'vuedraggable'
export default {
  name: 'ListadoTipoClientes',
  components: {
    DataTable,
    modal,
    vSelect,
    draggable,
    RadioButtonGroup
  },
  data () {
	return {
		info: {
			docRequerida: {
				selected: null,
				data: []
			}
		},
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
			  		name: 'nombre',
			  		title: 'Nombre',
			  		sortField: 'nombre'
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
			title: 'Crear/Editar Tipo de clientes',
			submited: false,
			show: false,
			errors: '' 
		},
		uri: 'tipo-clientes/',
		actionPerm: 'tipos-de-cliente',
		apiUrl: '',
	}
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
	        		},() => {
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
    		},() => {
    			dialog.close()
    		})
    },
    reset (item) {
	    this.selectedItem = item || {
	      nombre: null,
	      id: 0,
	      vigente:true,
	      area_id: null,
	    }

	    if (!this.selectedItem.doc_requerida) {
	    	this.selectedItem.doc_requerida = []
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
	getOptions(search, loading) {
		loading(true)
		Api.combos('doc-requerida?search=' + search).then(resp => {
		   this.info.docRequerida.data = resp.data
		   loading(false)
		})
	},
	addDocRequerida() {
		if (this.info.docRequerida.selected) {
			this.selectedItem.doc_requerida.push({
				doc_requerida_id: this.info.docRequerida.selected.id,
				tipo_cliente_id: this.selectedItem.id,
				doc: {
					id: this.info.docRequerida.selected.id,
					nombre: this.info.docRequerida.selected.nombre
				}	
			})
			this.info.docRequerida.selected = null
			this.info.docRequerida.data = []
		}
	},
	onChangeDocReq(item) {
		this.info.docRequerida.selected = item
	},
	removeDocReq(index) {
		this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
			.then((dialog) => {
	        	dialog.loading(true)
	        	this.selectedItem.doc_requerida.splice(index,1)
	        	dialog.close()
			})  		
	},	    
  }
}
</script>

<style scoped>
	.list-group .btn-remove-list{
		float: right;
	}
</style>
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
      <!--template slot="slot-top-actions">&nbsp;</template-->
      <template slot="actions" slot-scope="props">
			<button-type v-can="actionPerm+':U'" type="edit-list" @click="onAction('edit', props.rowData, props.rowIndex)"/>
			<button-type v-can="actionPerm+':D'" type="remove-list" @click="onAction('remove', props.rowData, props.rowIndex)"/>
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
				<input type="text" name="nombre" v-model="selectedItem.nombre" class="form-control" v-validate="'required'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('nombre')">{{ errors.first('nombre') }}</span>
			</div>
			<div class="form-group col-sm-6" :class="{'has-error': errors.has('area_id')}">
				<label for="area_id">Área</label>

				  <select v-model="selectedItem.area_id" class="form-control" name="area_id" v-validate="'required'" data-vv-validate-on="none">
					  <option v-for="option in info.areas" v-bind:value="option.id">
					    {{ option.nombre }}
					  </option>
				  </select>
				<span class="help-block" v-show="errors.has('area_id')">{{ errors.first('area_id') }}</span>
			</div>			
			<div class="form-group col-sm-6">
				<label for="vigente">Tratamiento</label>
				<select v-model="selectedItem.tratamiento" class="form-control" name="tratamiento">
				  <option v-for="option in info.tratamientos" v-bind:value="option.value">
				    {{ option.nombre }}
				  </option>
				</select>								
			</div>
			<div class="clearfix"></div>
			<div class="form-group col-sm-3">
				<label for="vigente">Vigente</label>
				<radio-button-group v-model="selectedItem.vigente"></radio-button-group>				
			</div>	
			<div class="form-group col-sm-3">
				<label for="inicia_tramite">Inicia trámite</label>
				<radio-button-group v-model="selectedItem.inicia_tramite"></radio-button-group>				
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
  name: 'ListadoTipoTramites',
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
			}, 
			areas: [],
			tratamientos: [
				{
					value: null,
					nombre: 'Ninguno'
				},
				{
					value: 'PENSION',
					nombre: 'Pensión'
				}
			]
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
			  		name: 'area.nombre',
			  		title: 'Area',
			  		sortField: 'area_id'
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
			  		name: 'inicia_tramite',
					titleClass: 'text-center',
					dataClass: 'text-center',			  		
			  		title: 'Inicia trámite',
			  		sortField: 'inicia_tramite',
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
			title: 'Crear/Editar Tipo de trámites',
			submited: false,
			show: false,
			errors: '' 
		},
		uri: 'tipo-tramites/',
		actionPerm: 'tipos-de-tramite',
		apiUrl: '',
	}
  },  
  mounted () {
  	this.apiUrl = config.serverURI + this.uri;
  	Api.combos('areas').then((resp) => {
  		this.info.areas = resp.data;
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
				tipo_tramite_id: this.selectedItem.id,
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
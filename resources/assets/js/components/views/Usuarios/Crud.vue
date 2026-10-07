<template>
<div>
    <data-table
    	ref="list"
      :api-url="apiUrl"
      :fields="list.fields"
      :sort-order="list.sortOrder"
      :append-params="list.moreParams"
      :show-remove-selected="false"
      :actionPerm="'usuarios'"
      @create="create">
      <template slot="slot-filter-bar">
      	<filter-bar @usuarios:filter-set="onFilterSet"></filter-bar>
      </template>      
      <template slot="actions" slot-scope="props">
      		<button v-can="'usuarios:U'"  type="button" class="btn btn-xs bg-orange" @click="onAction('reset-pass', props.rowData, props.rowIndex)"><i class="fa fa-key"></i></button>
			<button-type v-can="'usuarios:U'" type="edit-list" @click.native="onAction('edit', props.rowData, props.rowIndex)"/>
			<button-type v-can="'usuarios:D'" type="remove-list" @click.native="onAction('remove', props.rowData, props.rowIndex)"/>

      </template>
    </data-table>


	<modal v-model="amModal.show" class="themis-modal" effect="fade" :backdrop="false" @keyup.enter="save()" @keyup.esc="closeAmModal()">
	  <div slot="modal-header" class="modal-header">
	    <h4 class="modal-title">
	      {{ amModal.title }}
	    </h4>
	  </div>
	  <div slot="modal-body" class="modal-body" v-if="selectedItem">
		<modal-errors :messages="amModal.errors"/>
		<div class="row">
			<div class="form-group col-sm-6" :class="{'has-error': errors.has('name')}">
				<label for="name">Nombre</label>
				<input type="text" name="name" v-model="selectedItem.name" class="form-control" v-validate="'required'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('name')">{{ errors.first('name') }}</span>
			</div>

			<div class="form-group col-sm-6" :class="{'has-error': errors.has('username')}">
				<label for="username">Usuario</label>
				<input type="text" name="username" v-model="selectedItem.username" class="form-control " v-validate="'required'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('username')">{{ errors.first('username') }}</span>
			</div>
			<div class="clearfix"></div>

			<div class="form-group col-sm-6" :class="{'has-error': errors.has('email')}">
				<label for="email">Email</label>
				<input type="text" name="email" v-model="selectedItem.email" class="form-control" v-validate="'required|email'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('email')">{{ errors.first('email') }}</span>
			</div>

			<div class="form-group col-sm-6" :class="{'has-error': errors.has('password')}" v-if="selectedItem.id < 1">
				<label for="password">Password</label>
				<input type="password" name="password" v-model="selectedItem.password" class="form-control" v-validate="'required'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('password')">{{ errors.first('password') }}</span>
			</div>
			<div class="clearfix"></div>

			<div class="form-group col-sm-6" :class="{'has-error': errors.has('role')}">
				<label for="role">Rol</label>

				  <select v-model="selectedItem.role" class="form-control" name="role" v-validate="'required'" data-vv-validate-on="none">
					  <option v-for="option in info.roles" v-bind:value="option">
					    {{ option.name }}
					  </option>
				  </select>
				<span class="help-block" v-show="errors.has('role')">{{ errors.first('role') }}</span>
			</div>
			<div class="form-group col-sm-3">
				<label for="responsable">Abogado a cargo</label>
				<radio-button-group v-model="selectedItem.responsable"></radio-button-group>
			</div>
			<div class="form-group col-sm-3">
				<label for="time_level">Time: Nivel</label>
				<select v-model="selectedItem.time_level" class="form-control" name="time_level">
					<option :value="null">No usa</option>
					<option :value="5">Administrador</option>
					<option :value="1">Nivel 1</option>
					<option :value="2">Nivel 2</option>
				</select>
			</div>

			<div class="form-group col-sm-12">
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr><td style="width:80%;"><label for="areas">Areas</label></td><td>Miembro</td><td>Resp.</td></tr>	
						</thead>
						<tbody>
							<tr v-for="item in selectedItem._areas">
								<td>{{ item.nombre }}</td>
								<td class="text-center"><input type="checkbox" :value="true" v-model="item.miembro"></td>
								<td class="text-center"><input v-if="item.miembro" type="checkbox" :value="true" v-model="item.responsable"></td>
							</tr>
						</tbody>
					</table>
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
import RadioButtonGroup from '../../shared/buttons/RadioButtonGroup'
import FilterBar from './includes/FilterBar'
import config from '../../../config'
import Api from '../../../api'
import { mapState } from 'vuex'

export default {
  name: 'ListadoUsuarios',
  components: {
    DataTable,
    modal,
    RadioButtonGroup,
    FilterBar
  },
  data () {
	return {
		selectedItem: null,
		info: {
			roles: [],
			areas: []
		},
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
			  		name: 'username',
			  		title: 'Usuario',
			  		sortField: 'username'
			  	},			    	  
			  	{
			  		name: 'name',
			  		title: 'Nombre',
			  		sortField: 'name'
			  	},
			  	{
			  		name: 'email',
			  		title: 'Email',
			  		sortField: 'email'
			  	},			  	
			  	{
			  		name: 'roles',
			  		title: 'Rol',
			  		callback: 'formatRole'
			  	},	
			  	{
			  		name: 'responsable',
			  		title: 'Abogado a cargo	',
				      titleClass: 'text-center',
				      dataClass: 'text-center',			  		
			  		callback: 'booleanLabel'
			  	},	
			  	{
			  		name: 'time_level',
			  		title: 'Time: Nivel',
			  		callback: 'tagAssoc|{"null":["No usa","bg-red"],"1":["Nivel 1","bg-purple disabled"],"2":["Nivel 2","bg-purple"],"5": ["Administrador","bg-purple-active"]}'
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
			title: 'Crear/Editar Usuario',
			submited: false,
			show: false,
			errors: ''
		},
		uri: 'usuarios/',
		apiUrl: '',
	}
  },  
  computed: {
    ...mapState([
      'authUser'
    ])
  },  
  mounted () {
  	this.apiUrl = config.serverURI + this.uri;
  	Api.combos('usuarios').then((resp) => {
  		this.info = resp.data;
	});
  },  
  methods: {
  	onFilterSet (filter) {
  		console.debug(filter)
  		//this.list.moreParams.searchJoin = 'and';
  		/*if (!filter.cliente) {
  			filter.cliente_id = -1;
  			this.selectedCliente = null;
  		} else {
  			filter.cliente_id = filter.cliente.id;
  			this.selectedCliente = _.assign({},filter.cliente);	
  			delete filter.cliente;
  		}*/
  		
  		//this.list.moreParams.search = this.$options.filters.jsonToSearcheable(_.assign({},filter));
  		this.list.moreParams.search = filter.search;
  		this.list.moreParams.time = filter.time;
  		
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
			this.amModal.show = true;
				break;
			case 'remove':
				this.remove(data);
				break;
			case 'reset-pass':
				this.resetPass(data);
				break;      		
		}
    },  
    create () {
    	this.reset({});
    	this.amModal.show = true
    },
    save () {
    	this.amModal.errors = ''
		return this.$validator.validateAll().then((result) => {
	        if (result) {
	        	return Api.store(this.uri,this.selectedItem)
	        		.then((result) => {
	        				this.$refs.list.$refs.vuetable.refresh();
	        				
	        				if (this.authUser.id === this.selectedItem.id) {
	        					//this.$store.dispatch('updateAreas',result.data.message)
	        					this.$store.dispatch('setAuthUser');
	        				}
	        				this.closeAmModal();
	        				

	        				this.$store.dispatch('showSuccessNotification',result.data.message)
	        		}, (resp) => {
	        			this.amModal.errors = resp.message
	        			if (resp.fields) {
	        				for(var key in resp.fields) {
								this.addError(key, resp.fields[key][0], 'server')					    	
						   	}	        				
	        			}
	        		});
	          	return;
	        }
      	})
      	.catch((error) => console.debug(error))    	
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
    removeSelected (ids) {
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
    resetPass (data) {
		this.$dialog.confirm('¿Deséa resetear la clave de ' + data.name + '?',{loader: true})
			.then((dialog) => {
	        	dialog.loading(true)
	    		Api.request('PUT',this.uri.concat('password/reset/').concat(data.id))
	    			.then((resp) => {
	    				dialog.close()
	    				this.$store.dispatch('showSuccessNotification',resp.data.message)
	    			},() => {
		    			dialog.close()
		    		});  			
			})    	
    },
    reset (item) {
	    this.selectedItem = _.extend({
	      name: null,
	      username: null,
	      password: null,
	      email: null,
	      responsable: true,
	      _areas: [],
	      id: 0
	    },item)
	    
	    if (this.selectedItem.id < 1) {
	    	for(let i in this.info.areas) {
	    		this.selectedItem._areas.push({
		    		area_id: this.info.areas[i].id,
		    		miembro: false,
		    		nombre: this.info.areas[i].nombre,
		    		responsable: false	    			
	    		})
	    	}
	    } else {
	    	

	    	this.selectedItem.role = {
	    		id: this.selectedItem.roles[0].id,
	    		name: this.selectedItem.roles[0].name
	    	};

	    	for(let i in this.info.areas) {
	    		let _area = _.find(this.selectedItem.areas, {area_id: this.info.areas[i].id});

	    		this.selectedItem._areas.push({
		    		area_id: this.info.areas[i].id,
		    		miembro: (_area ? true : false),
		    		nombre: this.info.areas[i].nombre,
		    		responsable: (_area ? _area.responsable : false)	    			
	    		})
	    	}	    	
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
    		submited: false,
    	});
    	this.reset();
    }
  }
}
</script>

<style>
</style>
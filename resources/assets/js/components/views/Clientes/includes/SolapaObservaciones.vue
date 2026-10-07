<template>
	<div>
		<div class="box">
            <div class="box-header">
              <h3 class="box-title">{{ title }}</h3>
              <div class="box-tools">
			    <button-type v-can="[actionPerm+':U',actionPerm+':C']" type="new" @click="create"/> 
			    <button-type type="print" @click="print()" v-if="list.data.length > 0"/>             	
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
            	<div class="table-responsive">
	            <table class="table table-striped">
	            	<tbody>
	        			<tr>
		                  <th style="width: 100px"><a :class="{'order_by': filtros.orderBy === 'created_at','sorteable':true}" href="javascript:void(0)" @click="changeOrder('created_at')">Fecha</a></th>
		                  <th style="width: 100px"><a :class="{'order_by': filtros.orderBy === 'users|name','sorteable':true}" href="javascript:void(0)" @click="changeOrder('users|name')">Usuario</a></th>
		                  <th>Observacion</th>
		                  <th></th>
	                	</tr>
	                	<template v-if="!list.loading">
		                	<tr v-for="(value,index) in list.data.data">
			                  <td>{{ value.created_at }}</td>
			                  <td>{{ (value.user ? value.user.name : '') }}</td>
			                  <td>{{ value.observacion }}</td>
			                  <td style="text-align: right;" nowrap="">
								<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="edit-list" @click="onAction('edit', value, index)"/>
								<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="remove-list" @click="onAction('remove',value, index)"/>
			                  </td>
			            	</tr>
			            </template>
		            	<tr v-else="list.loading">
		            		<td colspan="7">
		            			<pulse-loader :loading="list.loading"></pulse-loader>
		            		</td>
		            	</tr>		            	
		        	</tbody>
					<tfoot>
						<tr>
							<td colspan="7" class="text-right">
								<pagination v-if="list.data && list.data.total > 1" :limit="5"  :data="list.data" @pagination-change-page="changePage"></pagination>
							</td>
						</tr>	
					</tfoot>		        	
	      		</table>
	      		</div>
            </div>
            <!-- /.box-body -->
      	</div>	
	
		<modal v-model="amModal.show"  class="themis-modal" effect="fade" :backdrop="false">
		  <div slot="modal-header" class="modal-header">
		    <h4 class="modal-title">
		    	{{ amModal.title }}
		    </h4>
		  </div>
		  <div slot="modal-body" class="modal-body" v-if="selectedItem" @keyup.esc="closeAmModal()">
		  	<modal-errors :messages="amModal.errors"/>
		  	<form @submit.prevent="save()" :data-vv-scope="'observaciones'">
		  	<div class="row" v-if="selectedItem.id > 0">
				<div class="form-group col-sm-6">
					<label for="fecha_archivo">Fecha</label><br>
					<span>{{ selectedItem.created_at }}</span>
				</div>	
				<div class="form-group col-sm-6">
					<label for="nombre">Usuario</label>
					<span >{{ selectedItem.user.name }}</span>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-sm-12"  :class="{'has-error': errors.has('observaciones.observacion')}">
					<label for="observacion">Observacion</label>
					<textarea name="observacion" v-model="selectedItem.observacion" class="form-control" v-validate="'required'" data-vv-validate-on="none"></textarea>
					<span class="help-block" v-show="errors.has('observaciones.observacion')">{{ errors.first('observaciones.observacion') }}</span>
				</div>			
			</div>
			</form>
		  </div>
		  <div slot="modal-footer" class="modal-footer">
		    <button-type type="close" @click="closeAmModal()"/>
		    <button-type type="save" @click="save()"/>
		  </div>
		</modal>


	</div>
</template>
<script>
import moment from 'moment'
import Vue from 'vue'
import { modal } from 'vue-strap'
import config from '../../../../config'
import Api from '../../../../api'
import pagination from 'laravel-vue-pagination'

export default {
  name: 'SolapaObservaciones',
  components: {
  	modal,
  	pagination
  },
  props: {
  	baseUri: {
  		type: String,
  		required: true
  	},
  	clienteId: {
  		type: Number,
  		required: true
  	},
  	actionPerm: {
  		type: String,
  		required: true
  	},
  	active: {
  		type: Boolean,
  		default: function() {
  			return false;
  		}
  	}
  },
  data () {
	return {
		title: 'Observaciones',
		firstLoad: true,
		list: {
			pagination: {},
			data: {
				data: []
			},
			loading: false
		},
		filtros: {
			page: 1,
			orderBy: 'created_at',
			sortedBy: 'desc'
		},
		selectedItem: null,
		selectedIndex: -1,
		amModal: {
			title: 'Nueva observación',
			submited: false,
			show: false,
			errors: '',

		},
		messages: '', 
		apiUrl: '',
		uri: this.baseUri.concat(this.clienteId).concat('/observaciones/')
	}
  },  
  mounted () {
  	this.apiUrl = config.serverURI + this.uri;
  	//this.getList();
  },  
  methods: {
    onAction (action, data, index) {
    	this.$store.dispatch('hideSuccessNotification')
		switch(action) {
			case 'edit':
			this.selectedIndex = index;
			this.reset(_.clone(data, true));
			this.amModal.show = true;
				break;
			case 'remove':
				this.remove(data,index);
				break;      		
		}
    },  
	changePage(page) {
		this.filtros.page = page;
		this.getList();
	},  
	changeOrder(field) {
		if (this.filtros.orderBy === field) {
			this.filtros.sortedBy = (this.filtros.sortedBy === 'asc' ? 'desc' : 'asc');
		} else {
			this.filtros.orderBy = field;
			this.filtros.sortedBy = 'asc';
		}
		this.getList();
	},
    getList () {

    	if (this.clienteId) {
	    	this.list.loading = true;
			let queryString = Object.keys(this.filtros).map((key) => {
				if (this.filtros[key] != null) {
			    	return encodeURIComponent(key) + '=' + encodeURIComponent(this.filtros[key]);
			    }
			}).join('&');
			
			//document.location = Laravel.webDomain.concat('/informes/beneficios/?').concat(queryString);	    	
	    	Api.get(this.uri.concat('?' + queryString)).then((result) => {
	    		
	    		this.list.data.data.length = 0;
	    		this.list.data = result.data;

	    		this.list.loading = false;
	    	}, errors => {
	    		this.list.data.data.length = 0;
	    		this.list.data = [];
	    		this.list.loading = false;
	    	})
    	}
    },    
    create () {
    	this.reset();
    	this.amModal.show = true
    },  
    reset (item) {
	    this.selectedItem = item || {
	      user: null,
	      user_id: 0,
	      cliente_id: this.clienteId,
	      observacion: null,
		  created_at: moment().format('DD/MM/YYYY')
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
    	this.selectedIndex = -1;
    	this.reset()
    },
    save() {
		this.$validator.validateAll('observaciones').then((result) => {
	        if (result) {
		    	Api.store(this.uri,this.selectedItem)
		    		.then((result) => {
		    				this.$store.dispatch('showSuccessNotification',result.data.message)
		    				this.getList();
		    				/*if (this.selectedItem.id > 0) {
		    					this.observaciones[this.selectedIndex] = result.data.data;
		    				} else {
		    					this.observaciones.unshift(result.data.data);
		    				}*/
		    				this.closeAmModal();
		    		}, (resp) => {
		    			this.message = resp.message
		    			this.$store.dispatch('showErrorNotification',this.message)
		    			//console.debug(resp);
		    			/*if (resp.fields) {
		    				for(var key in resp.fields) {
								this.addError(key, resp.fields[key][0], 'server'); 								    	
						   	}	        				
		    			}*/
		    		});	        	
	        }
      	}); 
    },
    remove (item,index) {
		this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
			.then((dialog) => {
	        	dialog.loading(true)
	        	Api.delete(this.uri,item)
	        		.then((result) => {
	        			dialog.close()
	        			this.$store.dispatch('showSuccessNotification',result.data.message)
	    				//this.observaciones.splice(index,1)
	    				this.getList();
	        		},() => {
	        			dialog.close()
	        		})  			
			})    	
    },    

    back() {
    	this.$emit('clientes:back','observaciones')
    },
    print() {
			document.location = Laravel.webDomain.concat('/observaciones/'+this.clienteId);    	
    }        	
  },
	watch: {
	    active(newVal) {
	      if (newVal && this.firstLoad) {
	      	this.getList();
	      }
	    }
    }  
};
</script>
<style>
	.progress-description.del{
		text-align: center;
	}
	.progress-description.del a{
		color: #ffffff!important;
	}
	.progress-description.del a i{
		display: block;
	}
</style>
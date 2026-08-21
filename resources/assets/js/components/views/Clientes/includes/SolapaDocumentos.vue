<template>
	<div>
		<div class="box">
            <div class="box-header">
              <h3 class="box-title">{{ title }}</h3>
              <div class="box-tools">
			    <button-type v-can="[actionPerm+':U',actionPerm+':C']" type="new" @click="create"/>              	
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
            	<div class="table-responsive">
	            <table class="table table-striped">
	            	<tbody>
	        			<tr>
		                  <th style="width: 50px">Fecha</th>
		                  <th>Nombre</th>
		                  <th>Descripción</th>
		                  <th>Archivo</th>
		                  <th></th>
	                	</tr>
	                	<template v-if="!list.loading">
		                	<tr v-for="(value,index) in list.data">
			                  <td>{{ value.fecha_archivo }}</td>
			                  <td>{{ value.nombre }}</td>
			                  <td>{{ value.descripcion }}</td>
			                  <td><a :href="value.full_url" target="_blank">{{ value.nombre_real }}</a></td>
			                  <td style="text-align: right;" nowrap="">
								<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="edit-list" @click="onAction('edit', value, index)"/>
								<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="remove-list" @click="onAction('remove',value, index)"/>
								<button-type type="download-list" @click="onAction('download',value, index)"/>
			                  </td>
			            	</tr>
		            	</template>
		            	<tr v-else="list.loading">
		            		<td colspan="5">
		            			<pulse-loader :loading="list.loading"></pulse-loader>
		            		</td>
		            	</tr>		            	
		        	</tbody>
	      		</table>
	      		</div>
            </div>
            <!-- /.box-body -->
      	</div>	
		<div class="row">
			<div class="col-xs-12 text-right">
		    	<button-type type="back" @click="back()"/>        
			</div>
		</div>		
		<modal v-model="amModal.show"  class="themis-modal" effect="fade" :backdrop="false">
		  <div slot="modal-header" class="modal-header">
		    <h4 class="modal-title">
		    	{{ amModal.title }}<br class="visible-xs"><span class="hidden-xs"> | </span>{{ cliente.nombre_completo }} - {{ cliente.cuit }}
		    </h4>
		  </div>
		  <div slot="modal-body" class="modal-body" v-if="selectedItem" @keyup.esc="closeAmModal()">
		  	<modal-errors :messages="amModal.errors"/>
		  	<form @submit.prevent="save()" :data-vv-scope="'documentos'">
		  	<div class="row">
				<div class="form-group col-sm-6" :class="{'has-error': errors.has('documentos.fecha_archivo')}">
					<label for="fecha_archivo">Fecha</label><br>
					<datepicker name="fecha_archivo" v-model="selectedItem.fecha_archivo" :format="'dd/MM/yyyy'" :clear-button="true" v-validate="'required'" data-vv-validate-on="none"></datepicker>
					<span class="help-block" v-show="errors.has('documentos.fecha_archivo')">{{ errors.first('documentos.fecha_archivo') }}</span>
				</div>	
				<div class="form-group col-sm-6" :class="{'has-error': errors.has('documentos.nombre')}">
					<label for="nombre">Nombre</label>
					<input type="text" name="nombre" v-model="selectedItem.nombre" class="form-control" v-validate="'required'" data-vv-validate-on="none">
					<span class="help-block" v-show="errors.has('documentos.nombre')">{{ errors.first('documentos.nombre') }}</span>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-sm-12">
					<label for="descripcion">Descripcion</label>
					<textarea name="descripcion" v-model="selectedItem.descripcion" class="form-control"></textarea>
				</div>			
			</div>

			<div class="row">
				<div class="form-group col-sm-6">
					<label for="nombre_archivo">Archivo</label><br>
					<div class="info-box bg-aqua" v-if="selectedItem.nombre_archivo">
			            <span class="info-box-icon"><i class="fa fa-files-o"></i></span>

			            <div class="info-box-content">
			              <span class="info-box-text">{{selectedItem.nombre_real}}</span>
			              <!--span class="info-box-number">{{file.progress}}</span-->

			              <div class="progress">
			                <div class="progress-bar" :style="{ width: '100%' }"></div>
			              </div>
				          <span class="progress-description del">
				         	<a href="#" @click="borrarArchivo"><i class="fa fa-trash fa-2x"></i>BORRAR</a>	
				          </span>
			            </div>

					</div>
					<div class="info-box bg-aqua" v-else v-for="(file, index) in amModal.archivos" :key="file.id">
			            <span class="info-box-icon"><i class="fa fa-files-o"></i></span>

			            <div class="info-box-content">
			              <span class="info-box-text">{{file.name}}</span>
			              <!--span class="info-box-number">{{file.progress}}</span-->

			              <div class="progress">
			                <div class="progress-bar" :style="{ width: file.progress+'%' }"></div>
			              </div>
				          <span v-if="file.error" class="progress-description">{{file.error}}</span>
				          <span v-else-if="file.active" class="progress-description">{{file.progress}}%</span>
				          <span v-else class="progress-description"></span>		                  
			            </div>
			            <!-- /.info-box-content -->
			        </div>				      

					<file-upload
						v-if="!selectedItem.nombre_archivo"
						:multiple="false"
						ref="upload"
						v-model="amModal.archivos"
						:post-action="amModal.urlUploads"
						@input-file="inputFile"
						class="btn btn-primary"
						
					>
						Seleccionar archivo
					</file-upload>
					<!--button v-show="!$refs.upload || !$refs.upload.active" @click.prevent="$refs.upload.active = true" type="button">Start upload</button-->					
				</div>			
			</div>
			</form>
		  </div>
		  <div slot="modal-footer" class="modal-footer">
		    <button-type type="close" @click="closeAmModal()"/>
		    <button-type v-if="selectedItem && selectedItem.nombre_archivo" type="save" @click="save()"/>
		  </div>
		</modal>


	</div>
</template>
<script>
import moment from 'moment'
import Vue from 'vue'
import { datepicker, modal } from 'vue-strap'
import FileUpload from 'vue-upload-component'
import config from '../../../../config'
import Api from '../../../../api'

export default {
  name: 'SolapaDocumentos',
  components: {
  	modal,
    datepicker,
    FileUpload
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
	cliente: {
		type: Object,
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
		title: 'Documentos asociados',
		firstLoad: true,
		selectedItem: null,
		selectedIndex: -1,
		list: {
			loading: true,
			data: []
		},		
		amModal: {
			title: 'Nuevo documento',
			archivos: [],
			submited: false,
			show: false,
			errors: '',
			urlUploads: config.serverURI.concat('upload/docs')

		},
		messages: '', 
		apiUrl: '',
		uri: this.baseUri.concat(this.clienteId).concat('/documentos/')
	}
  },  
  mounted () {
  	console.debug([this.baseUri,config.serverURI]);
  	this.apiUrl = config.serverURI + this.uri;
  	//this.getList();
  	this.amModal.archivos = [];
  },  
  methods: {
    getList () {

    	if (this.clienteId) {
	    	this.list.loading = true;
	    	Api.get(this.uri).then((result) => {
	    		
	    		this.list.data.length = 0;
	    		this.list.data = result.data;

	    		this.list.loading = false;
	    		this.firstLoad = false;
	    	})
    	}
    },   	
  	borrarArchivo () {
  		this.selectedItem.nombre_archivo = null;
  		this.selectedItem.nombre_real = null;
  		//this.$refs.upload.clear();
  	},
    inputFile(newFile, oldFile) {
      // Automatic upload
      if (Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
        if (!this.$refs.upload.active) {
          this.$refs.upload.active = true
        }
      }

      if (newFile && !oldFile) {
        // add
        //newFile.active = true;
        console.log('add', newFile)
        // Uploaded successfully
      
      }
      if (newFile && oldFile) {
        // update
        console.log('update', newFile)
        if (newFile.success) {
          console.log('success', newFile.success, newFile)

          this.selectedItem.nombre_archivo = newFile.response.data.doc;
          this.selectedItem.nombre_real = newFile.name;
          this.$refs.upload.clear();
        }          
      }
      if (!newFile && oldFile) {
        // remove
        console.log('remove', oldFile)
      }
    },	
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
			case 'download':
				document.location = data.download_url;
				break;
		}
    },  
    create () {
    	this.reset();
    	this.amModal.show = true
    },  
    reset (item) {
	    this.selectedItem = item || {
	      nombre: null,
	      cliente_id: this.clienteId,
	      nombre_archivo: null,
	      id: 0,
	      descripcion: null,
		  fecha_archivo: moment().format('DD/MM/YYYY'),
		  nombre_real: null	      
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
		this.$validator.validateAll('documentos').then((result) => {
	        if (result) {
		    	Api.store(this.uri,this.selectedItem)
		    		.then((result) => {
		    				this.$store.dispatch('showSuccessNotification',result.data.message)
		    				this.getList();
		    				/*if (this.selectedItem.id > 0) {
		    					this.documentos[this.selectedIndex] = result.data.data;
		    				} else {
		    					this.documentos.push(result.data.data);
		    				}*/
		    				this.closeAmModal();
		    		}, (resp) => {
		    			this.message = resp.message
		    			this.$store.dispatch('showErrorNotification',this.message)
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
	    				//this.documentos.splice(index,1)
	    				this.getList();
	        		},() => {
	        			dialog.close()
	        		})  			
			})    	
    },    

    back() {
    	this.$emit('clientes:back','documentos')
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
<template>
	<div>
		<modal-errors :messages="error"/>
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

			<div class="form-group col-sm-6" :class="{'has-error': errors.has('password')}">
				<label for="password">Password</label>
				<input type="text" name="password" v-model="selectedItem.password" class="form-control" >
				
			</div>
			<div class="clearfix"></div>
		</div>
		  
	 	<div slot="modal-footer" class="modal-footer">
		    <button-type type="close" @click.native="close()"/>
		    <button-type type="save" :promise="save"/>
		</div>
	</div>
</template>
<script>
	import Vue from 'vue'
	import config from '../../../config'
	import Api from '../../../api'


export default {
  name: 'Profile',
  props: {
  	selectedItem: {
  		type: Object,
  		required: true
  	}
  },
  data () {
	return {
		submited: false,
		show: false,
		error: '',
		uri: 'usuarios/update-profile/',
		apiUrl: ''
	}
  },  
  mounted () {
  	this.apiUrl = config.serverURI + this.uri
  },  
  methods: {
    save () {
    	this.error = '';
		return this.$validator.validateAll().then((result) => {
	        if (result) {
	        	return Api.store(this.uri,this.selectedItem)
	        		.then((result) => {
	        				this.$store.dispatch('showSuccessNotification',result.data.message)
	        				this.close(result.data.data)
	        		}, (error) => {
	        			this.error = error.message
	        			if (error.fields) {
	        				for(var key in error.fields) {
								this.addError(key, error.fields[key][0], 'server')						    	
						   	}	        				
	        			}
	        		});
	          	return;
	        }
      	})
      	.catch((errors) => console.debug(errors));    	
    },
    clearErrors () {
    	this.error = '';
    	this.$validator.reset();    	
    },
    close (data) {
    	let _data = data || null;
    	this.clearErrors()
    	this.$events.emit('close-edit-profile',_data)
    }
  }
}		
</script>
<style>
	
</style>
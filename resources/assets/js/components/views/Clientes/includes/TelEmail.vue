<template>
	<div>
	<form @submit.prevent="saveItem()" :data-vv-scope="id">
		<div class="box">
            <div class="box-header">
              <h3 class="box-title">{{ title }}</h3>
              <div class="box-tools">
			    <button-type v-if="canEdit && !editMode" type="new" @click="createItem"/>              	
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
            	<div class="table-responsive">
	            <table class="table table-striped" style="margin-bottom: 0;">
	            	<tbody>
	        			<tr>
		                  <th>{{ label1 }}</th>
		                  <th>{{ label2 }}</th>
		                  <th>&nbsp;</th>
	                	</tr>
	                	<tr v-if="items.length > 0" v-for="(value, index) in items">
		                  <td>{{ value.val }}</td>
		                  <td>{{ value.desc }}</td>
		                  <td style="text-align: right;" nowrap="">
							<button-type v-if="canEdit && !editMode" type="edit-list" @click="editItem(value,index)"/>
							<button-type v-if="canEdit && !editMode" type="remove-list" @click="delItem(value,index)"/>
		                  </td>
		            	</tr>
						<tr v-if="items.length == 0">
							<td colspan="3">
								No hay items cargados
							</td>
						</tr>		            	
		        	</tbody>
					<tfoot v-if="canEdit && editMode">
						<tr>
							<td>
								<div class="form-group" :class="{'has-error': errors.has(id+'.item1')}">
									<input :type="inputType" v-model="selectedItem.val"  class="form-control" name="item1" v-validate="'required'" data-vv-validate-on="none">
									<span class="help-block" v-show="errors.has(id+'.item1')">{{ errors.first(id+'.item1') }}</span>
								</div>
							</td>
							<td>
								<div class="form-group" :class="{'has-error': errors.has(id+'.item2')}">
									<input type="text" v-model="selectedItem.desc"  class="form-control" name="item2" v-validate="'required'" data-vv-validate-on="none">
									<span class="help-block" v-show="errors.has(id+'.item2')">{{ errors.first(id+'.item2') }}</span>
								</div>
							</td>
							<td style="text-align: right;" nowrap="">
								<button-type type="add-list" :submit="true"/>
								<button-type type="close-list" @click="reset"/>
							</td>	
						</tr>

					</tfoot>		        	
	      		</table>
	      		</div>
            </div>
            <!-- /.box-body -->
      	</div>	
	</form>
</div>
</template>
<script>
import Vue from 'vue'

export default {
  name: 'TelEmail',
  props: {
  	items: {
  		type: Array,
  		require: true
  	},
  	type: {
  		type: String,
  		required: true,
  		default: 'T'
  	},
  	canEdit: {
  		type: Boolean,
  		default () {
  			return false;
  		}
  	}
  },
  data () {
	return {
		id: Math.random().toString(36).substring(7),
		selectedIndex: -1,
		title: '',
		selectedItem: {},
		label1: '',
		label2: 'Desc.',
		inputType: 'text',
		editMode: false
	}
  },  
  mounted () {
  	this.reset();
  	switch(this.type) {
  		case 'T':
  			this.label1 = 'Número';
  			this.title = 'Teléfonos';
  			this.inputType = 'tel';
  		break;
  		case 'E':
  			this.label1 = 'E-mail';
  			this.title = 'E-mails';  		
  			this.inputType = 'email';
  		break;
  	}
  },  
  methods: {
  	saveItem () {
  		
		this.$validator.validateAll(this.id).then((result) => {
	        if (result) {
	        	if (this.selectedIndex === -1) {
	        		this.items.push(this.selectedItem);	
	        	} else {
	        		this.items[this.selectedIndex] = _.clone(this.selectedItem);
	        	}
		  		
		  		this.reset();
	        }
      	});  		
  	},

  	editItem (item,index) {
  		this.selectedIndex = index;
  		this.selectedItem = _.clone(item);
  		this.editMode = true;
  	},
  	delItem (index) {
    	this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
			.then((dialog) => {
				dialog.loading(true)
		    	this.items.splice(index,1);	
    			dialog.close();
			})     	
  	},
  	createItem () {
  		this.editMode = true;	
  	},
  	reset () {
  		this.selectedItem = {
			val: null,
			desc: 'Otro'
		}
		this.selectedIndex = -1;
		this.editMode = false;
  	}
  }
}	
</script>
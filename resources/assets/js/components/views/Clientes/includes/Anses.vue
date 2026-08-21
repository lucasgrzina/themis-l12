<template>
		<div class="box" :class="{'box-danger': msgErrors}">
            <div class="box-header">
              <h3 class="box-title text-red">Vuelta a Anses</h3>
              <div class="box-tools">
			    <button-type v-if="canAdd()" type="new" @click="addRow"/>              	
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
				<div class="row hidden-xs">
					<div class="form-group col-sm-4">
						<label for="estado_anses_id">Estado</label>						
					</div>
					<div class="form-group col-sm-3">
						<label for="fecha_remision">Fecha Remisión</label>
					</div>						
					<div class="form-group col-sm-3">
						<label for="observacion">Observaciones</label><br>
					</div>
					<div class="form-group col-sm-2">
						&nbsp;
					</div>
				</div>            	
				<fieldset class="transparente" v-for="(value, index) in items" >
					<div class="row">
						<div class="form-group col-sm-4">
							<label for="estado_anses_id" class="visible-xs">Estado</label>
							<v-select
								v-model="value.estado"
								:options="estadosAnses"
								:on-change="onChangeEstadoAnses(value)"
								placeholder="Ingresa el estado (ANSES)"
								label="nombre"
								:disabled="(index+1) < items.length"
							></v-select>	
						</div>					
						<div class="form-group col-sm-3">
							<label for="fecha_remision" class="visible-xs">Fecha</label>
							<datepicker :name="'dp_'+index" v-model="value.fecha_remision_dp" :bootstrap-styling="true" :format="'dd/MM/yyyy'" :language="es" :use-utc="false" :disabledDates="disabledDates" @selected="calcFecha(value)" :disabled="(index+1) < items.length"></datepicker>
						</div>	
						<div class="form-group col-sm-4">
							<label for="fecha_remision_vto" class="visible-xs">Observaciones</label>
							<textarea type="text" class="form-control" v-model="value.observations"></textarea>
						</div>							
						<div class="form-group col-sm-1">
							<button-type v-if="(index+1) === items.length" type="remove-list" @click="delItem(index)" v-can="['requerimientos:D']"/>
						</div>						
					</div>
				</fieldset>   
				<span class="help-block text-red" v-show="msgErrors">{{ msgErrors }}</span>	
            </div>
      	</div>							
</template>
<script>
	import moment from 'moment'
	import Vue from 'vue'
	//import { datepicker } from 'vue-strap'
	import Datepicker from 'vuejs-datepicker'
	import {es} from 'vuejs-datepicker/dist/locale'
	import config from '../../../../config'
	import Api from '../../../../api'
	import vSelect from "vue-select"
	import { mapState } from 'vuex'
	
export default {
	name: 'Anses',
	components: {
		vSelect,
		Datepicker
	},
	props: {
		items: {
			type: Array,
			required: true
		},
		estadosAnses: {
			type: Array,
			required: true
		},
		msgErrors: {

		}
	},
	data () {
		return {
			disabledDates: {
				days: [6,0]
			},
			es: es,
			canEdit: true,
			cu_errors: '',
			messages: '', 
		}
	}, 
  	computed: {
	    ...mapState([
	      'authUser'
	    ])/*,
	    sortedItems: function() {
	        this.items.sort( ( a, b) => {
	            return new moment(a.fecha_remision_dp) - new moment(b.fecha_remision_dp);
	        });
	        return this.items;
	    }	    */
  	},   
	mounted () {

	
	},
methods: {	
	onChangeEstadoAnses(item) {
		item.estado_anses_id = (item.estado ? item.estado.id : null)	
	},
	addRow () {
		this.items.push({
			id: 0,
			tramite_id: 0,
			estado_anses_id: null,
			estado: null,
			fecha_remision: '',
			fecha_remision_dp: '',
			fecha_remision_vto: '',
			observations: ''
		});
	},
  	delItem (index) {
  			this.items.splice(index,1);	
  	},
  	canAdd () {
  		let _exist;
  		_exist = _.find(this.items, function(o) { return (o.estado_anses_id === null || o.fecha_remision === ''); });
  		return !_exist
  	},
  	calcFecha(item) {
		Vue.nextTick()
		  .then(function () {

			if (item.fecha_remision_dp) {

				item.fecha_remision = moment(item.fecha_remision_dp).format('DD/MM/YYYY');
			} else {
				item.fecha_remision = '';
			} 	



		  });
  	}
  }
};	
</script>
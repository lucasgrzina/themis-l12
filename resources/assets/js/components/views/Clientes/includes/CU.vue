<template>
    <div v-if="show">
    <div class="row" style="margin-bottom: 10px;">
      <h4 style="margin-top:0;color:#0c4b95;" class="col-sm-8">{{ selectedItem.nombre_completo }} - {{ selectedItem.cuit }}</h4>
      <div class="col-xs-12 col-sm-4 text-right">
          <button-type type="back" @click="back()"/>        
      </div>
    </div>      
    
    	<!--a href="#" @click="back()">Volver al listado</a-->
		<modal-errors :messages="messages"/>

		<tabs v-model="tabActive" nav-style="tabs" justified class="tab-cliente">
		  <tab header="General">
		  	<solapa-general :actionPerm="actionPerm" :info="info" :selectedItem="selectedItem" @clientes:save="save" @clientes:back="back"></solapa-general>
		  </tab>
		  <tab header="Movimientos/Avisos" :disabled="disabled">
        <solapa-observaciones v-if="selectedItem.id" :actionPerm="actionPerm" :baseUri="uri" :clienteId="selectedItem.id" :active="tabActive == 1"></solapa-observaciones>
        <solapa-avisos v-if="selectedItem.id" :actionPerm="actionPerm" :baseUri="uri" :clienteId="selectedItem.id" :active="tabActive == 1"></solapa-avisos>
        <div class="row">
          <div class="col-xs-12 text-right">
              <button-type type="back" @click="back()"/>        
          </div>
        </div>          
		  </tab>

		  <tab header="Requerimientos" :disabled="disabled">
		    <solapa-requerimientos v-if="selectedItem.id" :actionPerm="'requerimientos'" :baseUri="uri" :clienteId="selectedItem.id" :cliente="selectedItem" @clientes:back="back" :cuit="selectedItem.cuit"  :active="tabActive == 2"></solapa-requerimientos>
		  </tab>

		  <tab header="Tramites" :disabled="disabled">
		    <solapa-tramites v-if="selectedItem.id" :actionPerm="'tramites'" :baseUri="uri" :clienteId="selectedItem.id" :cliente="selectedItem" @clientes:back="back" :cuit="selectedItem.cuit"  :active="tabActive == 3"></solapa-tramites>
		  </tab>

		  <tab header="Docs" :disabled="disabled">
		    <solapa-documentos v-if="selectedItem.id" :actionPerm="'documentos'" :baseUri="uri" :clienteId="selectedItem.id" :cliente="selectedItem" @clientes:back="back"  :active="tabActive == 4"></solapa-documentos>
		  </tab>

		</tabs>   
    </div>	
</template>
<script>
import Vue from 'vue'
import { modal,tab,tabs,datepicker } from 'vue-strap'
import config from '../../../../config'
import Api from '../../../../api'
import SolapaGeneral from './SolapaGeneral'
import SolapaDocumentos from './SolapaDocumentos'
import SolapaObservaciones from './SolapaObservaciones'
import SolapaAvisos from './SolapaAvisos'
import SolapaRequerimientos from './SolapaRequerimientos'
import SolapaTramites from './SolapaTramites'


export default {
  name: 'CUCliente',
  components: {
    modal,
    tab,
    tabs,
    datepicker,
    SolapaGeneral,
    SolapaDocumentos,
    SolapaObservaciones,
    SolapaAvisos,
    SolapaRequerimientos,
    SolapaTramites
  },
  props: {
	 selectedItem: {
  		type: Object,
  		default: null
  	},  	
  	activeTab: {
  		type: Number,
  		default: 3
  	},
  	actionPerm: {
  		type: String,
  		required: true,
  		default: 'clientes'
  	},
  	uri: {
  		type: String,
  		required:true,
  		default: 'clientes/'
  	},
  	show: {
  		type: Boolean,
  		required: true
  	}
  },
  data () {
	return {
		info: {},
    tabActive: this.activeTab,
		title: 'Crear/Editar Cliente',
		submited: false,
		messages: '',
		apiUrl: '',
    disabled: false
	}
  },  
  mounted () {
  	this.apiUrl = config.serverURI + this.uri;
  	Api.combos('am-cliente').then((resp) => {
  		this.info = resp.data;
	  });
  },  
  methods: {
    save (solapa,data) {
            if (this.selectedItem.id === 0) {
              this.selectedItem.id = data.id;
            } else {
              this.clearErrors();
              this.$emit('clientes:saved',this.selectedItem)
            }    	
    }, 
    back () {
    	this.$emit('clientes:back')
      this.tabActive = 0;
    },
    clearErrors() {
	    this.messages = ''
	    this.$validator.reset()    	
    }    
  },
  watch: {
    'selectedItem.id'(newVal) {
      if (newVal === 0 || newVal === undefined) {
        this.disabled = true;
      } else {
        this.disabled = false;
      }
    }
  }
}	
</script>
<style>

  .tab-cliente .nav-tabs li:not(.active) a{
    border: 1px solid #ddd;
  }
  .tab-cliente > .tab-content {
    margin: 0;
    background-color: white;
    padding: 15px;
    border-left: 1px solid #ddd;
    border-right: 1px solid #ddd;
    border-bottom: 1px solid #ddd;    
  }
  @media (min-width: 768px){
    .tab-cliente .nav-tabs-justified > li > a, .tab-cliente .nav-tabs.nav-justified > li > a {
          border-radius: 4px 4px 0 0;
    }   
    .tab-cliente .nav-tabs li.active {
      border-top-left-radius: 6px;
      border-top-right-radius: 6px;
      border-bottom-right-radius: 3px;
      border-bottom-left-radius: 3px;
      border-top: 3px solid #d2d6de;    
    }
    .tab-cliente .nav-tabs li.active a{
      border-top: 0;
    }
  }
</style>
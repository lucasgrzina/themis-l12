// Import ES6 Promise
import 'es6-promise/auto'
import './bootstrap.js'
import _ from 'lodash'
import Vue from 'vue'

import axios from 'axios'
window.axios = axios
window.Vue = require('vue')

import router from './routes'
import store from './store/index'
import { sync } from 'vuex-router-sync'
import App from './components/App.vue'
import jwtToken from './helpers/jwt-token'
// Import Helpers for filters
import { parte,areaLabel, domain, count, prettyDate, pluralize, jsonToSearcheable, currency, dateFormat, booleanLabel, datetimeFormat,tagAssoc,minutesToHours } from './filters'
import UnauthorizedAlert from './components/shared/alerts/UnauthorizedAlert'
import ButtonType from './components/shared/buttons/ButtonType'
import VuejsDialog from "vuejs-dialog"
import VeeValidate from 'vee-validate'
import ModalErrors from './components/shared/modal/ModalErrors'
import VueTheMask from 'vue-the-mask'
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'
import { Datetime } from 'vue-datetime'
//import Datepicker from 'vuejs-datepicker'


import 'vue-datetime/dist/vue-datetime.css'

import mixinVeeErrors from './mixins/veeErrors'
import veeErrors from './mixins/veeErrors'

// Import Install and register helper items
Vue.filter('count', count)
Vue.filter('domain', domain)
Vue.filter('prettyDate', prettyDate)
Vue.filter('pluralize', pluralize)
Vue.filter('jsonToSearcheable', jsonToSearcheable)
Vue.filter('currency', currency)
Vue.filter('dateFormat', dateFormat)
Vue.filter('datetimeFormat', datetimeFormat)
Vue.filter('booleanLabel', booleanLabel)
Vue.filter('tagAssoc', tagAssoc)
Vue.filter('areaLabel', areaLabel)
Vue.filter('parte', parte)
Vue.filter('minutesToHours', minutesToHours)

Vue.directive('can', function (el, binding) {
  var _perms = store.getters.hasAnyPerm(binding.value);
  el.style.display = (!_perms ? 'none' : 'inline-block');  
});

Vue.directive('focus', function (el) {
    el.focus()
});


Vue.component('datetime', Datetime);


Vue.component('button-type',ButtonType);
//Vue.use(ButtonType);

Vue.component('modal-errors',ModalErrors);
//Vue.use(ModalErrors);

Vue.component('pulse-loader',PulseLoader);
//Vue.use(PulseLoader);

Vue.component('unauthorized-alert',UnauthorizedAlert);
//Vue.use(UnauthorizedAlert);

Vue.mixin(veeErrors);

Vue.use(VuejsDialog,{
    html: true, 
    loader: true,
    okText: 'Continuar',
    cancelText: 'Cancelar',
    animation: 'bounce', 
})

Vue.use(VeeValidate,{
  fieldsBagName: 'inputs'
})
Vue.use(VueTheMask)
axios.interceptors.request.use(config => {
    
    config.headers['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
    config.headers['X-Requested-With'] = 'XMLHttpRequest';

  
    if(jwtToken.getToken()) {
        config.headers['Authorization'] = 'Bearer '+ jwtToken.getToken();
    }

    return config;
}, error => {
    return Promise.reject(error);
});

axios.interceptors.response.use(response => {
    return response;
}, error => {
    let errorData = (error.response && error.response.data ? error.response.data : {});
    
    let responseData = {};
    switch ( error.response.status ) {
      case 401:
        store.dispatch('logoutRequest')
            .then(() => router.push({name: 'login'}))
        responseData = {
          type: error.response.status,
          message: errorData.error  
        }    
        break;
      case 422:
        responseData = {
          type: error.response.status,
          message: errorData.message,
          fields: errorData.errors
        }
        break;
      case 500:
        responseData = {
          type: error.response.status
        }
        break;
      default:
        responseData = {
          type: error.response.status,
          message: errorData.message,
          data: errorData.data
        }
    }
    return Promise.reject(responseData);
});

sync(store, router)

new Vue({
  el: '#app',
  router: router,
  store: store,
  render: h => h(App)/*,
  components: {
    Datepicker
  }*/
})
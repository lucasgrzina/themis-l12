<template>
  <div :class="pageClass">
    <router-view :key="$route.fullPath"></router-view>
  </div>
</template>

<script>
    import jwtToken from './../helpers/jwt-token'
    import { mapState } from 'vuex'
    //import Menus from './menus';
    
    export default {
        name: 'app',
          data() {
            return {
              section: 'Head',
              pageClass: 'sidebar-mini  hold-transition',
              interval: {
                avisos: null,
                vencimientos: null
              }              
            };
          },  
        computed: {
          ...mapState([
            'authUser'
          ]) 
        },        
        created() {

            let _this = this;
            this.setPageClass()
            this.$store.dispatch('loadGeneralData')

            if(jwtToken.getToken()) {
                this.$store.dispatch('setAuthUser');
            }
        },
        updated() {
          this.setPageClass()
        },
        beforeDestroy() {
          clearInterval(this.interval.avisos);
          clearInterval(this.interval.vencimientos);
        },
        methods: {
          setPageClass() {
            this.pageClass = (this.$route.name === 'login' ? 'hold-transition login-page' : 'sidebar-mini  hold-transition')
          },
        },
        watch: {
          'authUser.id': function(n,o) {
            let _this = this;
            if (n) {

              setTimeout(() => {
                _this.$store.dispatch('getCantAvisos').then((data) => {
                    if (data.notificarAvisosOnLogin && data.total > 0 &&  confirm('Tiene avisos pendientes. Desea verlos?')) {
                        //console.debug('a perrr');
                        _this.$router.push('/avisos');
                    }                  
                });
              },1000);

              setTimeout(() => {
                _this.$store.dispatch('getVencimientos');  
              },2000);

              if (!this.interval.avisos) {
                this.interval.avisos = setInterval(function(){
                    _this.$store.dispatch('getCantAvisos');    
                },(60 * 1000 * 60));
              }

              if (!this.interval.vencimientos) {
                this.interval.vencimientos = setInterval(function(){
                    _this.$store.dispatch('getVencimientos');  
                },(60 * 1000 * 60));

              }

            }

          }
        }
  };

</script>
<style>
  html{
    /*height: 100%;*/
  }
  .sorteable{
    color: initial!important;
  }
  .sorteable.order_by{
    text-decoration: underline;
    font-weight: bolder;
  }
  .form-group.has-error .help-block {
      color: #dd4b39;
  }
  .themis-modal .modal-content{
    border-radius: 5px;
    border-top: 2px solid #337ab7 !important;
  }
  .themis-modal .modal-content .help-block{
    margin-bottom: 0;
    font-size: 13px;
  }
  .themis-modal .modal-content select, .themis-modal .modal-content input[type="text"] {
    height: 30px;
    line-height: 30px;    
  }
  .themis-modal .modal-header{
    padding: 10px;
    border-bottom-color: #337ab7 !important;
    background-color: #337ab7 !important;
  }
  .themis-modal .modal-header .modal-title{
    color: #fff;
    font-size: 14px;
  }
  .themis-modal .modal-footer {
    border-top-color: #ffffff;
    padding: 0px 15px 10px;
  }

  .v-spinner {
    text-align: center;
  }

  .v-select input[type=search]{
    width: 1px!important;
  }
  .v-select.open input[type=search]{
    width: auto!important;
  }  

  fieldset {
    padding: 6px 17px;
    border: 2px solid #d2d6de;
    border-radius: 8px;
  }

  fieldset {
    margin: 0;
    min-width: 0;
  }
  fieldset legend {
    width: initial;
    margin-bottom: 0;
    border-bottom: none;
    font-size: 14px;
    font-weight: 600;        
  }
  fieldset legend label.checkbox span{
    top: 7px!important;
    left: 2px!important;
  }

  fieldset.transparente {
    padding: 0;
    border-radius: 0;
    border: 0;
  }
  fieldset.sinbordes {
    border: 0;
  }
  .cargando-themis{
    display: none;
  }
  .content-wrapper div.content{
    min-height: 530px;
  }

  .text-underline {
    text-decoration: underline;
  }
</style>
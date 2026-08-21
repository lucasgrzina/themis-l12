<template>
  <div>
    <notification></notification>
    <div class="login-box">
      <div class="login-logo">
        <img :src="logo">
      </div>
      <!-- /.login-logo -->
      <div class="login-box-body">
        <p class="login-box-msg">Ingrese su usuario para recuperar su contraseña</p>

        <form >
          <div class="form-group has-feedback" :class="{ 'has-error' : loginErrors.username}">
            <input class="form-control" name="username" placeholder="Usuario" type="text" v-model="username" autocomplete="username">
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
            <span class="help-block">{{ loginErrors.username }}</span>
          </div>
          <div class="row">
            <!-- /.col -->
            <div class="col-sm-4 pull-right">
              <button-type type="forgot" :promise="forgot" :submit="false"/>
            </div>
            <div class="col-sm-8">
              <router-link tag="a" to="/login">
                Ingresar al sistema
              </router-link>              
            </div>

            <!-- /.col -->
          </div>
        </form>
        <!-- /.social-auth-links -->

        
      </div>
      <!-- /.login-box-body -->
    </div>    

  </div>
</template>

<script>
import {mapState} from 'vuex';
import config from '../config'
import Notification from './Notification.vue'

export default {
    name: 'Forgot',
    components: {
      Notification
    },
    created() {
        this.$store.dispatch('clearLoginErrors');
    },
    data() {
        return {
            username: null,
            loading: '',
            response: '',
            logo: config.logo
        }
    },
    computed: {
        ...mapState({
            loginErrors: state => state.login.errors
        })
    },
    methods: {

        forgot() {
            const forgotData = {
                username: this.username
            };
            return this.$store.dispatch('forgotRequest', forgotData)
                .then((response) => this.$router.push({name: 'login'}))
        },
        toggleLoading () {
          this.loading = (this.loading === '') ? 'loading' : ''
        }
    }
}

</script>

<style>
.login-page {
    background: #fff;
}
</style>

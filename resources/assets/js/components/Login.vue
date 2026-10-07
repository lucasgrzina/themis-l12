<template>
  <div>
    <notification></notification>
    <div class="login-box">
      <div class="login-logo">
        <img :src="logo">
      </div>
      <!-- /.login-logo -->
      <div class="login-box-body">
        <p class="login-box-msg">Ingrese sus credenciales para acceder al sistema</p>

        <form>
          <div class="form-group has-feedback" :class="{ 'has-error' : loginErrors.username}">
            <input class="form-control" name="username" placeholder="Usuario" type="text" v-model="username" autocomplete="username">
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
            <span class="help-block">{{ loginErrors.username }}</span>
          </div>
          <div class="form-group has-feedback" :class="{ 'has-error' : loginErrors.password}">
            <input type="password" class="form-control" name="password" placeholder="Password" v-model="password" autocomplete="current-password">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            <span class="help-block">{{ loginErrors.password  }}</span>
          </div>
          <div class="row">
            <!-- /.col -->
            <div class="col-sm-4 pull-right">
              <button-type type="login" :promise="login" :submit="false"/>
            </div>

            <div class="col-sm-8">
              <router-link tag="a" to="/forgot">
                Olvide mi contraseña
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
    name: 'Login',
    components: {
      Notification
    },
    created() {
        this.$store.dispatch('clearLoginErrors');
    },
    data() {
        return {
            username: null,
            password: null,
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
        login() {
            const loginData = {
                username: this.username,
                password: this.password
            };
            return this.$store.dispatch('loginRequest', loginData)
                .then((response) => this.$router.push({name: 'dashboard'}))
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

<template>
  <div>  
  <div :class="['wrapper', classes]">
    <header class="main-header">

      <!-- Header Navbar -->
      <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="javascript:;" class="sidebar-toggle" data-toggle="offcanvas" role="button" id="btn-menu">
          <span class="sr-only">Toggle navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <!-- Messages-->
            <!--li class="dropdown messages-menu">
              <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-envelope-o"></i>
                <span class="label label-success">{{ authUser.messages | count }}</span>
              </a>
              <ul class="dropdown-menu">
                <li class="header">You have {{ authUser.messages | count }} message(s)</li>
                <li v-if="authUser.messages.length > 0">
                  <ul class="menu">
                    <li>
                      <a href="javascript:;">
                        <h4>
                          Support Team
                          <small>
                            <i class="fa fa-clock-o"></i> 5 mins</small>
                        </h4>
                        <p>Why not consider this a test message?</p>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="footer" v-if="authUser.messages.length > 0">
                  <a href="javascript:;">See All Messages</a>
                </li>
              </ul>
            </li-->
            <!-- /.messages-menu -->
  
            <top-menu-avisos :type="'avisos'" v-if="hasAnyPerm('avisos:R')"></top-menu-avisos>
            <top-menu-avisos :type="'vencimientos'" v-if="hasAnyPerm('avisos:R')"></top-menu-avisos>
            <!-- Tasks Menu -->
            <!--li class="dropdown tasks-menu">
              <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-flag-o"></i>
                <span class="label label-danger">{{ authUser.tasks | count }} </span>
              </a>
              <ul class="dropdown-menu">
                <li class="header">You have {{ authUser.tasks | count }} task(s)</li>
                <li v-if="authUser.tasks.length > 0">
                  <ul class="menu">
                    <li>
                      <a href="javascript:;">
                        <h3>
                          Design some buttons
                          <small class="pull-right">20%</small>
                        </h3>
                        <div class="progress xs">
                          <div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only">20% Complete</span>
                          </div>
                        </div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="footer" v-if="authUser.tasks.length > 0">
                  <a href="javascript:;">View all tasks</a>
                </li>
              </ul>
            </li-->
  
            <!-- User Account Menu -->
            <li class="dropdown user user-menu">
              <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                <!-- The user image in the navbar-->
                <!--img v-bind:src="demo.avatar" class="user-image" alt="User Image"-->
                <!-- hidden-xs hides the username on small devices so only the image appears. -->
                <span class="hidden-xs">{{ authUser.name }}</span>
              </a>

              <ul class="dropdown-menu">
                <!-- User image -->
                <li class="user-header">
                  

                  <p>
                    {{ authUser.name }}<br>
                    <small>({{ (authUser.role ? authUser.role.name : '') }})</small>
                  </p>
                </li>
                <!-- Menu Body -->
                <!--li class="user-body">
                  <div class="row">
                    <div class="col-xs-4 text-center">
                      <a href="#">Followers</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Sales</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Friends</a>
                    </div>
                  </div>
                  
                </li-->
                <!-- Menu Footer-->
                <li class="user-footer">
                  <div class="pull-left">
                    <a href="javascript:void(0)" @click="showProfileModal" class="btn btn-default btn-sm btn-flat">Mi perfil</a>
                  </div>
                  <div class="pull-right">
                    <a @click.prevent.stop="logout()" class="btn btn-sm bg-navy btn-flat">Salir</a>
                  </div>
                </li>
              </ul>

            </li>
          </ul>
        </div>
      </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <sidebar :display-name="authUser.name" />
  
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          {{$route.name.toUpperCase() }}
          <small>{{ $route.meta.description }}</small>
        </h1>
        <ol class="breadcrumb">
          <li>
            <a href="javascript:;">
              <i class="fa fa-home"></i>Home</a>
          </li>
          <li class="active text-capitalize">{{$route.name}}</li>
        </ol>
      </section>
      <div class="content">
        <notification></notification>
        <router-view></router-view>
      </div>
    </div>
    <!-- /.content-wrapper -->
  
    <!-- Main Footer -->
    <footer class="main-footer">
      <!--strong>Copyright &copy; {{year}}
        <a href="javascript:;">CoPilot</a>.</strong> All rights reserved.-->
    </footer>
  </div>
    <modal v-model="profileModal.show" class="themis-modal" effect="fade" :backdrop="false">
      <div slot="modal-header" class="modal-header">
        <h4 class="modal-title">
          {{ profileModal.title }}
        </h4>
      </div>
      <div slot="modal-body" class="modal-body" v-if="profileModal.selectedItem">
        <profile :selected-item="profileModal.selectedItem" v-if="profileModal.selectedItem"/>
      </div>
      <div slot="modal-footer" class="modal-footer">
      </div>
    </modal>        
  </div>
  <!-- ./wrapper -->
</template>

<script>
import { mapState,mapGetters } from 'vuex'
import config from '../config'
import Sidebar from './Sidebar'
import Notification from './Notification.vue'
import { modal } from 'vue-strap'
import Profile from './views/Usuarios/Profile.vue'
import VueEvents from 'vue-events'
import TopMenuAvisos from './includes/TopMenuAvisos.vue'
//import 'hideseek'

export default {
  name: 'TopMenu',
  components: {
    Sidebar,
    Notification,
    modal,
    Profile,
    TopMenuAvisos
  },
  created () {
    this.$events.$on('close-edit-profile',this.closeProfileModal)
  },
  data: function () {
    return {
      // section: 'Dash',
      year: new Date().getFullYear(),
      classes: {
        fixed_layout: config.fixedLayout,
        hide_logo: config.hideLogoOnMobile
      },
      error: '',
      profileModal: {
        show: false,
        selectedItem: null,
        title: 'Mi perfil'
      }
    }
  },
  computed: {
    ...mapState([
      'authUser'
    ]),
    ...mapGetters([
      'hasAnyPerm'
    ]),    
  },
  methods: {
    showProfileModal() {
      this.profileModal.selectedItem = {
        id: this.authUser.id,
        name: this.authUser.name,
        username: this.authUser.username,
        password: this.authUser.password,
        email: this.authUser.email
      };
      this.profileModal.show = true;
    },
    closeProfileModal(data) {
      if (data) {
        this.$store.dispatch('updateProfile',data);
      }
      this.profileModal.show = false;
    },
    changeloading () {
      //this.$store.commit('TOGGLE_SEARCHING')
    },
    logout() {
        if (this.authUser.avisos.length > 0 &&  confirm('Tiene avisos pendientes. Desea verlos?')) {
            //console.debug('a perrr');
            this.$router.push('/avisos');
        } else {
          this.$store.dispatch('logoutRequest')
              .then(() => {
                  this.$router.push({name: 'login'});
              });
        }                  
    }    
  }
};
</script>

<style lang="scss">
.wrapper.fixed_layout {
  .main-header {
    position: fixed;
    width: 100%;
  }

  .content-wrapper {
    padding-top: 50px;
  }

  .main-sidebar {
    position: fixed;
    height: 100vh;
  }
}

.wrapper.hide_logo {
  @media (max-width: 767px) {
    .main-header .logo {
      display: none;
    }
  }
}

.logo-mini,
.logo-lg {
  text-align: left;

  img {
    padding: .4em !important;
  }
}

.logo-lg {
  img {
    display: -webkit-inline-box;
    width: 25%;
  }
}

.user-panel {
  height: 4em;
}

hr.visible-xs-block {
  width: 100%;
  background-color: rgba(0, 0, 0, 0.17);
  height: 1px;
  border-color: transparent;
}
</style>

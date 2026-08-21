<template>
  <aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

      <!-- Sidebar user panel (optional) -->
      <div class="user-panel">
        <div class="image">
          <img :src="urlLogo" />
          <a href="javascript:;" class="sidebar-toggle-mobile" data-toggle="offcanvas" role="button" id="btn-menu">
            <i class="fa fa-bars"></i>
          </a>
        </div>

      </div>

      <!-- Sidebar Menu -->
      <sidebar-menu />
      <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
  </aside>
</template>
<script>
import SidebarMenu from './SidebarMenu'
import config from '../config'
export default {
  name: 'Sidebar',
  props: ['displayName', 'pictureUrl'],
  components: { SidebarMenu },
  mounted: function () {
    //window.jQuery('[data-toggle="hideseek"]').off().hideseek()
  },
  data() {
    return {
      urlLogo: config.serverURI + '../img/logo1.jpg'
    }
  }
};
</script>
<style>
  .user-panel {
    height: 5em;
    padding: 0;
  }
  .user-panel .image img {
    max-width: 100%;
  }
  .main-sidebar, .left-side{
    min-height: 100%;
  }
  .sidebar-toggle-mobile{
    display: none;
  }
  @media (max-width: 980px)
  {
    .user-panel .image img {
      max-width: 90%;
    }
    .sidebar-toggle-mobile {
      display: inline-block;
    }
  }
</style>

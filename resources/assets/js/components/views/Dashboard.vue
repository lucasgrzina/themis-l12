<template>
  <!-- Main content -->
  <section class="content">
    <!-- Info boxes -->
    <div class="row" v-if="!loading">
      <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
          <span class="info-box-icon bg-aqua"><i class="fa fa-user"></i></span>

          <div class="info-box-content">
            <span class="info-box-text">CLIENTES</span>
            <span class="info-box-text">(Hum. + Jur.)</span>
            <span class="info-box-number">{{ info.clientesH }} + {{ info.clientesJ }}</span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      <!-- /.col -->
      <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
          <span class="info-box-icon bg-green"><i class="fa fa-users"></i></span>

          <div class="info-box-content">
            <span class="info-box-text">Usuarios</span>
            <span class="info-box-number">{{ info.usuarios }}</span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>

      <!-- /.col -->

      <!-- fix for small devices only -->
      <div class="clearfix visible-sm-block"></div>
      <!-- /.col -->
      <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
          <span class="info-box-icon bg-yellow"><i class="fa fa-bell-o"></i></span>

          <div class="info-box-content">
            <span class="info-box-text">AVISOS</span>
            <span class="info-box-number">{{ info.avisos }}</span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      <!-- /.col -->
      <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
          <span class="info-box-icon bg-red"><i class="fa fa-calendar"></i></span>

          <div class="info-box-content">
            <span class="info-box-text">VENCIMIENTOS</span>
            <span class="info-box-number">{{ authUser.vencimientos | count }}</span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>      
    </div>
    <pulse-loader :loading="loading"></pulse-loader>
    <!-- /.row -->

  </section>
  <!-- /.content -->
</template>

<script>
  import Vue from 'vue'
import config from '../../config'
import Api from '../../api'
import { mapState } from 'vuex'

export default {

  data () {
    return {
      uri: 'dashboard/',
      apiUrl: '',
      loading: true,
      info: {

      }
    }
  },
  mounted () {
    this.apiUrl = config.serverURI + this.uri;
    this.loading = true;
    Api.get(this.uri).then((result) => {
      this.info = result.data;
      console.debug(this.info);
      //this.historicos = historicos;
      this.loading = false;
    })    
  },    
    computed: {
      ...mapState([
          'authUser'
      ])
    }
};
</script>
<style>
.info-box {
  cursor: pointer;
}
.info-box-content {
  text-align: center;
  vertical-align: middle;
  display: inherit;
}
.fullCanvas {
  width: 100%;
}
</style>

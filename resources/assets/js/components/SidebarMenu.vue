<template>
  <ul class="sidebar-menu">
    <router-link tag="li" class="pageLink" to="/">
      <a>
        <i class="fa fa-dashboard"></i>
        <span class="page">Dashboard</span>
      </a>
    </router-link>
    <router-link tag="li" class="pageLink" to="/clientes/listado">
      <a>
        <i class="fa fa-user"></i>
        <span class="page">Clientes</span>
      </a>
    </router-link>
    <li class="header">ADMINISTRACION</li>
    <li class="treeview">
      <a href="#">
        <i class="fa fa-users"></i>
        <span>Usuarios</span>
        <span class="pull-right-container">
          <i class="fa fa-angle-left fa-fw pull-right"></i>
        </span>
      </a>
      <ul class="treeview-menu">
        <router-link tag="li" class="pageLink" to="/usuarios/listado">
          <a>
            <i class="fa fa-users"></i>
            <span class="page">Usuarios</span>
          </a>
        </router-link>
        <router-link tag="li" class="pageLink" to="/usuarios/roles">
          <a>
            <i class="fa fa-check-square-o"></i>
            <span class="page">Roles</span>
          </a>
        </router-link>
      </ul>
    </li>
    <li class="header">UTILIDADES GRALES.</li>
    <li class="treeview">
      <a href="#">
        <i class="fa fa-cogs"></i>
        <span>Adic. del sistema</span>
        <span class="pull-right-container">
          <i class="fa fa-angle-left fa-fw pull-right"></i>
        </span>
      </a>
      <ul class="treeview-menu">            
        <router-link tag="li" class="pageLink" to="/sistema/tipo-documentos">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Tipos de documentos</span>
          </a>
        </router-link>
        <router-link tag="li" class="pageLink" to="/sistema/paises">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Paises</span>
          </a>
        </router-link>  
        <router-link tag="li" class="pageLink" to="/sistema/provincias">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Provincias</span>
          </a>
        </router-link>        
        <router-link tag="li" class="pageLink" to="/sistema/areas">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Areas</span>
          </a>
        </router-link>      
        <router-link tag="li" class="pageLink" to="/sistema/tipo-aportes">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Tipos de aportes</span>
          </a>
        </router-link>
        <router-link tag="li" class="pageLink" to="/sistema/tipo-tramites">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Tipos de trámites</span>
          </a>
        </router-link>  
        <router-link tag="li" class="pageLink" to="/sistema/estado-requerimientos">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Estados de req.</span>
          </a>
        </router-link>    
        <router-link tag="li" class="pageLink" to="/sistema/estado-tramites">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Estados de trámites</span>
          </a>
        </router-link>
        <router-link tag="li" class="pageLink" to="/sistema/estado-anses">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Estados UCADEP</span>
          </a>
        </router-link>            
        <router-link tag="li" class="pageLink" to="/sistema/tipo-sociedades">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Tipos de sociedades</span>
          </a>
        </router-link>
        <router-link tag="li" class="pageLink" to="/sistema/cond-iva">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Cond. IVA</span>
          </a>
        </router-link>    
        <router-link tag="li" class="pageLink" to="/sistema/colegas">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Colegas</span>
          </a>
        </router-link>    
        <router-link tag="li" class="pageLink" to="/sistema/empresas-referencia">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Empresas Ref.</span>
          </a>
        </router-link>    
        <router-link tag="li" class="pageLink" to="/sistema/reparticion-origen">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Rep. de origen</span>
          </a>
        </router-link>
        <router-link tag="li" class="pageLink" to="/sistema/juzgados">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Juzgados</span>
          </a>
        </router-link>        
        <router-link tag="li" class="pageLink" to="/sistema/estados-expediente">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Estados de Expediente</span>
          </a>
        </router-link> 
        <router-link tag="li" class="pageLink" to="/sistema/documentacion-requerida">
          <a>
            <i class="">&nbsp;</i>
            <span class="page">Doc. requerida</span>
          </a>
        </router-link>              
      </ul>
    </li>
    <template v-if="canViewTime()">
    <li class="header">TIME</li>
    <li class="treeview">
      <a href="#">
        <i class="fa fa-cogs"></i>
        <span>Time</span>
        <span class="pull-right-container">
          <i class="fa fa-angle-left fa-fw pull-right"></i>
        </span>
      </a>
      <ul class="treeview-menu">   
        <template v-if="canViewTimeGestion()">
          <router-link tag="li" class="pageLink" to="/time/gestion">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Gestión</span>
            </a>
          </router-link>
        </template>         
        <template v-if="canViewTimeReferencias()">
          <router-link tag="li" class="pageLink" to="/time/referencias">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Referencias</span>
            </a>
          </router-link>   
        </template>    
        <template v-if="canViewTimeHoras()">
          <router-link tag="li" class="pageLink" to="/time/horas">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Horas</span>
            </a>
          </router-link>          
        </template>
        <template v-if="canViewTimeImpresiones()">
          <li class="treeview">
            <a href="#">
              <span>Impresiones</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left fa-fw pull-right"></i>
              </span>
            </a> 
            <ul class="treeview-menu">   
              <router-link tag="li" class="pageLink" to="/time/impresiones/cliente-abogado">
                <a>
                  <i class="">&nbsp;</i>
                  <span class="page">Por Cliente/Abogado</span>
                </a>
              </router-link>  
              <router-link tag="li" class="pageLink" to="/time/impresiones/abogado-cliente">
                <a>
                  <i class="">&nbsp;</i>
                  <span class="page">Por Abogado/Cliente</span>
                </a>
              </router-link> 
              <router-link tag="li" class="pageLink" to="/time/impresiones/abogado-resumen">
                <a>
                  <i class="">&nbsp;</i>
                  <span class="page">Por Abogado (Resumen)</span>
                </a>
              </router-link>
            </ul>         
          </li> 
        </template>  
      </ul>
    </li>

    </template>
    <template v-can="['consulta-de-informes:R']">
      <li class="header">INFORMES</li>
      <li class="treeview" v-if="isMember(1)">
        <a href="#">
          <i class="fa fa-book"></i>
          <span>Previsional</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left fa-fw pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">            
          <router-link tag="li" class="pageLink" to="/informes/ucadep">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Seguimiento UCADEP</span>
            </a>
          </router-link>         
          <router-link tag="li" class="pageLink" to="/informes/requerimientos/previsional">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Requerimientos</span>
            </a>
          </router-link>
          <router-link tag="li" class="pageLink" to="/informes/exp-jud/previsional">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Expedientes Judiciales</span>
            </a>
          </router-link>        
          <router-link tag="li" class="pageLink" to="/informes/beneficios/previsional">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Beneficios</span>
            </a>
          </router-link>       
          <router-link tag="li" class="pageLink" to="/informes/tramites/previsional">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Trámites</span>
            </a>
          </router-link>
          <router-link tag="li" class="pageLink" to="/informes/pensiones/previsional">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Pensiones</span>
            </a>
          </router-link> 
          <router-link tag="li" class="pageLink" to="/informes/tramites-historicos/previsional">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Trámites Históricos</span>
            </a>
          </router-link>               
        </ul>
      </li>
      <li class="treeview" v-if="isMember(2)">
        <a href="#">
          <i class="fa fa-book"></i>
          <span>Laboral</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left fa-fw pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">           
          <router-link tag="li" class="pageLink" to="/informes/requerimientos/laboral">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Requerimientos</span>
            </a>
          </router-link>       
          <router-link tag="li" class="pageLink" to="/informes/exp-jud/laboral">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Expedientes Judiciales</span>
            </a>
          </router-link>        
          <router-link tag="li" class="pageLink" to="/informes/seclo/laboral">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Seclo</span>
            </a>
          </router-link>       
          <router-link tag="li" class="pageLink" to="/informes/tramites/laboral">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Trámites</span>
            </a>
          </router-link>
          <router-link tag="li" class="pageLink" to="/informes/tramites-historicos/laboral">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Trámites Históricos</span>
            </a>
          </router-link>               
        </ul>
      </li> 
      <li class="treeview" v-if="isMember(3)">
        <a href="#">
          <i class="fa fa-book"></i>
          <span>Civil</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left fa-fw pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">     
          <router-link tag="li" class="pageLink" to="/informes/requerimientos/civil">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Requerimientos</span>
            </a>
          </router-link>              
          <router-link tag="li" class="pageLink" to="/informes/exp-jud/civil">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Expedientes Judiciales</span>
            </a>
          </router-link>        
          <router-link tag="li" class="pageLink" to="/informes/mediacion/civil">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Mediación</span>
            </a>
          </router-link>       
          <router-link tag="li" class="pageLink" to="/informes/tramites/civil">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Trámites</span>
            </a>
          </router-link>
          <router-link tag="li" class="pageLink" to="/informes/tramites-historicos/civil">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Trámites Históricos</span>
            </a>
          </router-link>               
        </ul>
      </li>      
      <li class="treeview" v-if="isMember(4)">
        <a href="#">
          <i class="fa fa-book"></i>
          <span>Comercial</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left fa-fw pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">   
          <router-link tag="li" class="pageLink" to="/informes/requerimientos/comercial">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Requerimientos</span>
            </a>
          </router-link>                
          <router-link tag="li" class="pageLink" to="/informes/exp-jud/comercial">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Expedientes Judiciales</span>
            </a>
          </router-link>        
          <router-link tag="li" class="pageLink" to="/informes/mediacion/comercial">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Mediación</span>
            </a>
          </router-link>       
          <router-link tag="li" class="pageLink" to="/informes/tramites/comercial">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Trámites</span>
            </a>
          </router-link>
          <router-link tag="li" class="pageLink" to="/informes/tramites-historicos/comercial">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Trámites Históricos</span>
            </a>
          </router-link>               
        </ul>
      </li>    
      <li class="treeview" v-if="isMember(5)">
        <a href="#">
          <i class="fa fa-book"></i>
          <span>Societario</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left fa-fw pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu"> 
          <router-link tag="li" class="pageLink" to="/informes/requerimientos/societario">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Requerimientos</span>
            </a>
          </router-link>                
          <router-link tag="li" class="pageLink" to="/informes/tramites/societario">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Trámites</span>
            </a>
          </router-link>
          <router-link tag="li" class="pageLink" to="/informes/tramites-historicos/societario">
            <a>
              <i class="">&nbsp;</i>
              <span class="page">Trámites Históricos</span>
            </a>
          </router-link>               
        </ul>
      </li>          
    </template>
  </ul>
</template>
<script>
import { mapGetters } from 'vuex'
export default {
  name: 'SidebarName',
  computed: {
    ...mapGetters([
      'isMember','isTimeLevel'
    ])      
  },
  methods: {
    canViewTime () {
      return this.isTimeLevel(1) || this.isTimeLevel(2) || this.isTimeLevel(5)
    },
    canViewTimeGestion () {
      return this.isTimeLevel(5) || this.isTimeLevel(1)
    },
    canViewTimeReferencias () {
      return this.isTimeLevel(5) || this.isTimeLevel(1)
    },   
    canViewTimeImpresiones () {
      return this.isTimeLevel(5) || this.isTimeLevel(1) || this.isTimeLevel(2)
    },   
    canViewTimeHoras () {
      return this.isTimeLevel(5) || this.isTimeLevel(1)
    },          
  }    
};
</script>
<style>
  /* override default */
  .sidebar-menu>li>a {
    padding: 12px 15px 12px 15px;
  }

  .sidebar-menu li.active>a>.fa-angle-left, .sidebar-menu li.active>a>.pull-right-container>.fa-angle-left {
    animation-name: rotate;
    animation-duration: .2s;
    animation-fill-mode: forwards;
  }

  @keyframes rotate {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(-90deg);
    }
  }
</style>

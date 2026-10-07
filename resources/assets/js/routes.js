import Vue from 'vue';
import VueRouter from 'vue-router';
import jwtToken from './helpers/jwt-token';

Vue.use(VueRouter);

import Store from './store/index'

import TopMenu from './components/TopMenu.vue'
import LoginView from './components/Login.vue'
import ForgotView from './components/Forgot.vue'
import NotFoundView from './components/404.vue'

// Import Views - Dash
import DashboardView from './components/views/Dashboard.vue'
import AccionesControladasView from './components/views/AccionesControladas/Listado.vue'
import VistaRoles from './components/views/Roles/Crud.vue'
import VistaUsuarios from './components/views/Usuarios/Crud.vue'
import VistaClientes from './components/views/Clientes/Listado.vue'
import VistaPaises from './components/views/Paises/Crud.vue'
import VistaProvincias from './components/views/Provincias/Crud.vue'
import VistaAreas from './components/views/Areas/Crud.vue'
import VistaAvisos from './components/views/Avisos/Listado.vue'
import VistaVencimientos from './components/views/Avisos/Vencimientos.vue'
import VistaTipoDocumentos from './components/views/TipoDocumentos/Crud.vue'
import VistaTipoAportes from './components/views/TipoAportes/Crud.vue'
import VistaTipoTramites from './components/views/TipoTramites/Crud.vue'
import VistaTipoClientes from './components/views/TipoClientes/Crud.vue'
import VistaEstadoReq from './components/views/EstadoRequerimientos/Crud.vue'
import VistaEstadoAnses from './components/views/EstadoAnses/Crud.vue'
import VistaEstadoTramites from './components/views/EstadoTramites/Crud.vue'
import VistaTipoSociedades from './components/views/TipoSociedades/Crud.vue'
import VistaCondIva from './components/views/CondIva/Crud.vue'
import VistaColegas from './components/views/Colegas/Crud.vue'
import VistaEmpesasRef from './components/views/EmpresasReferencia/Crud.vue'
import VistaReparticionOrigen from './components/views/ReparticionOrigen/Crud.vue'
import VistaJuzgados from './components/views/Juzgados/Crud.vue'
import VistaEstadoExp from './components/views/EstadoExpedientes/Crud.vue'
import VistaDocReq from './components/views/DocRequerida/Crud.vue'
import VistaTimeGestion from './components/views/Time/CrudGestion.vue'
import VistaTimeReferencias from './components/views/Time/CrudReferencias.vue'
import VistaTimeHoras from './components/views/Time/CrudHoras.vue'
import VistaTimeImpresionesCA from './components/views/Time/impresiones/ClienteAbogado.vue'
import VistaTimeImpresionesAC from './components/views/Time/impresiones/AbogadoCliente.vue'
import VistaTimeImpresionesAR from './components/views/Time/impresiones/AbogadoResumen.vue'

import VistaInformeReqEmp from './components/views/Informes/ReqEmp/Listado.vue'
import VistaInformeExpJud from './components/views/Informes/ExpJud/Listado.vue'
import VistaInformeBeneficios from './components/views/Informes/Beneficios/Listado.vue'
import VistaInformeTramites from './components/views/Informes/Tramites/Listado.vue'
import VistaInformeTramitesHistoricos from './components/views/Informes/TramitesHistoricos/Listado.vue'
import VistaInformePensiones from './components/views/Informes/Pensiones/Listado.vue'
import VistaInformeUcadep from './components/views/Informes/Ucadep/Listado.vue'
// Routes
const router = new VueRouter({
    mode: 'history',
    routes: [
        {

          path: '/',
          component: TopMenu,
          children: [
            {
              path: '',
              alias: 'dashboard',
              component: DashboardView,
              name: 'dashboard',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: 'avisos',
              alias: 'avisos.listado',
              component: VistaAvisos,
              name: 'Avisos pendientes',
              meta: { requiresAuth: true,description: 'Listado de avisos pendientes'}
            }, 
            {
              path: 'vencimientos',
              alias: 'avisos.vencimientos',
              component: VistaVencimientos,
              name: 'Vencimientos',
              meta: { requiresAuth: true,description: 'Listado de vencimientos'}
            },                        
            {
              path: '/clientes/listado',
              alias: 'clientes',
              component: VistaClientes,
              name: 'clientes',
              meta: { requiresAuth: true,description: ''}
            }, {
              path: 'acciones',
              alias: 'acciones.listado',
              component: AccionesControladasView,
              name: 'acciones.listado',
              meta: { requiresAuth: true,description: 'Acciones controladas'}
            }, {
              path: '/usuarios/listado',
              alias: 'usuarios',
              component: VistaUsuarios,
              name: 'usuarios',
              meta: { requiresAuth: true,description: 'Usuarios del sistema'}
            }, {
              path: '/usuarios/roles',
              alias: 'usuarios.roles',
              component: VistaRoles,
              name: 'roles',
              meta: { requiresAuth: true,description: 'Administración de roles'}
            },
            {
              path: '/sistema/tipo-documentos',
              alias: 'sistema.tipo-documentos',
              component: VistaTipoDocumentos,
              name: 'tipos de documentos',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/paises',
              alias: 'sistema.paises',
              component: VistaPaises,
              name: 'paises',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/provincias',
              alias: 'sistema.provincias',
              component: VistaProvincias,
              name: 'provincias',
              meta: { requiresAuth: true,description: ''}
            },            
            {
              path: '/sistema/areas',
              alias: 'sistema.areas',
              component: VistaAreas,
              name: 'areas',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/tipo-aportes',
              alias: 'sistema.tipo-aportes',
              component: VistaTipoAportes,
              name: 'tipos de aportes',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/tipo-tramites',
              alias: 'sistema.tipo-tramites',
              component: VistaTipoTramites,
              name: 'tipos de trámites',
              meta: { requiresAuth: true,description: ''}
            },    
            {
              path: '/sistema/tipo-clientes',
              alias: 'sistema.tipo-clientes',
              component: VistaTipoClientes,
              name: 'tipos de clientes',
              meta: { requiresAuth: true,description: ''}
            },                    
            {
              path: '/sistema/estado-requerimientos',
              alias: 'sistema.estado-requerimientos',
              component: VistaEstadoReq,
              name: 'Estados de Req.',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/estado-tramites',
              alias: 'sistema.estado-tramites',
              component: VistaEstadoTramites,
              name: 'Estados de trámites',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/estado-anses',
              alias: 'sistema.estado-anses',
              component: VistaEstadoAnses,
              name: 'Estados de ANSES',
              meta: { requiresAuth: true,description: ''}
            },            
            {
              path: '/sistema/tipo-sociedades',
              alias: 'sistema.tipo-sociedades',
              component: VistaTipoSociedades,
              name: 'Tipos de sociedades',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/cond-iva',
              alias: 'sistema.cond-iva',
              component: VistaCondIva,
              name: 'Condiciones de IVA',
              meta: { requiresAuth: true,description: ''}
            },                  
            {
              path: '/sistema/colegas',
              alias: 'sistema.colegas',
              component: VistaColegas,
              name: 'Colegas',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/empresas-referencia',
              alias: 'sistema.empresas-referencia',
              component: VistaEmpesasRef,
              name: 'Empresas de referencia',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/reparticion-origen',
              alias: 'sistema.reparticion-origen',
              component: VistaReparticionOrigen,
              name: 'Reparticiones de origen',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/juzgados',
              alias: 'sistema.juzgados',
              component: VistaJuzgados,
              name: 'Juzgados',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/estados-expediente',
              alias: 'sistema.estados-expediente',
              component: VistaEstadoExp,
              name: 'Estados de expediente',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/sistema/documentacion-requerida',
              alias: 'sistema.documentacion-requerida',
              component: VistaDocReq,
              name: 'Documentación requerida',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/informes/requerimientos/:area',
              alias: 'informes.requerimientos-empresas',
              component: VistaInformeReqEmp,
              name: 'Requerimientos',
              meta: { requiresAuth: true,description: ''}
            },                                                                                
            {
              path: '/informes/exp-jud/:area',
              alias: 'informes.expedientes-judiciales',
              component: VistaInformeExpJud,
              name: 'Expedientes Judiciales',
              meta: { requiresAuth: true,description: ''}
            },   
            {
              path: '/informes/beneficios/:area',
              alias: 'informes.beneficios',
              component: VistaInformeBeneficios,
              name: 'Beneficios',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/informes/seclo/:area',
              alias: 'informes.seclo',
              component: VistaInformeBeneficios,
              name: 'Seclo',
              meta: { requiresAuth: true,description: ''}
            },    
            {
              path: '/informes/mediacion/:area',
              alias: 'informes.mediacion',
              component: VistaInformeBeneficios,
              name: 'Mediacion',
              meta: { requiresAuth: true,description: ''}
            },                          
            {
              path: '/informes/tramites/:area',
              alias: 'informes.tramites',
              component: VistaInformeTramites,
              name: 'Trámites',
              meta: { requiresAuth: true,description: ''}
            },   
            {
              path: '/informes/pensiones/:area',
              alias: 'informes.pensiones',
              component: VistaInformePensiones,
              name: 'Pensiones',
              meta: { requiresAuth: true,description: ''}
            },   
            {
              path: '/informes/tramites-historicos/:area',
              alias: 'informes.tramites-historicos',
              component: VistaInformeTramitesHistoricos,
              name: 'Trámites Históricos',
              meta: { requiresAuth: true,description: ''}
            },     
            {
              path: '/informes/ucadep',
              alias: 'informes.ucadep',
              component: VistaInformeUcadep,
              name: 'Seguimiento UCADEP',
              meta: { requiresAuth: true,description: ''}
            },
            {
              path: '/time/gestion',
              alias: 'time.gestion',
              component: VistaTimeGestion,
              name: 'Time: Gestión',
              meta: { requiresAuth: true,description: ''}
            },    
            {
              path: '/time/referencias',
              alias: 'time.referencias',
              component: VistaTimeReferencias,
              name: 'Time: Referencias',
              meta: { requiresAuth: true,description: ''}
            },   
            {
              path: '/time/horas',
              alias: 'time.horas',
              component: VistaTimeHoras,
              name: 'Time: Horas',
              meta: { requiresAuth: true,description: ''}
            },  
            {
              path: '/time/impresiones/cliente-abogado',
              alias: 'time.impresiones.cliente-abogado',
              component: VistaTimeImpresionesCA,
              name: 'Time: Impresiones por Cliente / Abogado',
              meta: { requiresAuth: true,description: ''}
            },  
            {
              path: '/time/impresiones/abogado-cliente',
              alias: 'time.impresiones.abogado-cliente',
              component: VistaTimeImpresionesAC,
              name: 'Time: Impresiones por Abogado / Cliente',
              meta: { requiresAuth: true,description: ''}
            },   
            {
              path: '/time/impresiones/abogado-resumen',
              alias: 'time.impresiones.abogado-resumen',
              component: VistaTimeImpresionesAR,
              name: 'Time: Impresiones por Abogado (Resumen)',
              meta: { requiresAuth: true,description: ''}
            },
          ]
        }, 
        {
            path: '/login',
            alias: 'login',
            name: 'login',
            component: LoginView,
            meta: { requiresGuest: true }
        },
        {
            path: '/forgot',
            alias: 'forgot',
            name: 'forgot',
            component: ForgotView,
            meta: { requiresGuest: true }
        }, 
        {
          // not found handler
          name: 'not-found',
          path: '*',
          component: NotFoundView
        }
    ]
});

router.beforeEach((to, from, next) => {

    Store.dispatch('hideAllNotifications');

    if(to.meta.requiresAuth) {
      //return Store.dispatch('setAuthUser').then(()=>{
        if(Store.state.authUser.authenticated || jwtToken.getToken()){
            return next();
        }else{
            return next({name: 'login'});
        }
      //})
      //console.log(5);
    }
    if(to.meta.requiresGuest) {
        if(Store.state.authUser.authenticated || jwtToken.getToken()) {
            return next({name: 'dashboard'});
        }
        else {
          return next();
        }
            
    }
    //next();
});

export default router;
<script>
//import accounting from 'accounting'
import moment from 'moment'
import axios from 'axios'
import Vue from 'vue'
import VueEvents from 'vue-events'
import Vuetable from 'vuetable-2/src/components/Vuetable'
//import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'
import VuetablePagination from './VuetablePagination'
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
import CustomActions from './CustomActions'
import FilterBar from './FilterBar'
import TopActions from './TopActions'
import jwtToken from '../../../helpers/jwt-token'
Vue.use(VueEvents)
Vue.component('custom-actions', CustomActions)
Vue.component('filter-bar', FilterBar)
Vue.component('top-actions', TopActions)
export default {
  components: {
    Vuetable,
    VuetablePagination,
    VuetablePaginationInfo
  },
  props: {
    apiUrl: {
      type: String,
      required: true
    },
    fields: {
      type: Array,
      required: true
    },
    loadOnStart: {
      type: Boolean,
      default: true
    },
    sortOrder: {
      type: Array,
      default() {
        return []
      }
    },
    appendParams: {
      type: Object,
      default() {
        return {}
      }
    },
    showRemoveSelected: {
      type: Boolean,
      default() {
        return false
      }
    },
    showNew: {
      type: Boolean,
      default() {
        return true
      }
    },    
    actionPerm: {
      type: String,
      required: true
    }
  },
  render(h) {
    return h(
      'div', 
      {
        class: {  }
      },
      [
        h('div',
          {
            class: { box: true, 'box-datatable': true  }
          },
          [
            h('div',
              {
                class: { 'box-header': true, 'with-border': true }
              },
              [
                h('div',
                {
                  attrs: {
                    slot: 'slot-filter-bar'
                  },
                },
                [
                  (this.$slots['slot-filter-bar'] ? this.$slots['slot-filter-bar'] : 
                  h('filter-bar',{
                    on: {
                      'vuetable:filter-set': this.onFilterSet,
                      'vuetable:filter-reset': this.onFilterReset,
                    }
                  }))  

                ]),

                h('div',
                {
                  attrs: {
                    slot: 'slot-top-actions'
                  },
                },
                [
                  (this.$slots['slot-top-actions'] ? this.$slots['slot-top-actions'] : 
                    h('top-actions',{
                      props: {
                        actionPerm: this.actionPerm,
                        removeSelected: this.showRemoveSelected,
                        showNew: this.showNew
                      },
                      on: {
                        'vuetable:create': this.onCreate,
                        'vuetable:remove-selected': this.onRemSel,
                      },                  
                    })    

                  )  

                ]),                

              ]
            ),
            h('div',
              {
                class: { 'box-body': true, 'no-padding': true, 'table-responsive': true}
              },
              [
                // No montar Vuetable hasta tener apiUrl: evita un request fantasma a '' en el mount
                (this.apiUrl ? this.renderVuetable(h) : null)
              ]
            ),
            h('div',
              {
                class: { 'box-footer': true, clearfix: true }
              },
              [
                this.renderPagination(h)
              ]
            )
          ]
        ),
        
      ]
    )
  },
  data () {
    return {
      loading: true,
      httpOptions: {
        headers: {Authorization: "Bearer " + jwtToken.getToken()}
      },
      css: {
              table: {
                tableClass: 'table table-condensed',
                ascendingIcon: 'fa fa-sort-up',
                descendingIcon: 'fa fa-sort-down',
                sortableIcon: 'fa fa-sort'
              },
              pagination: {
                wrapperClass: 'pagination pagination-sm',
                activeClass: 'active',
                disabledClass: 'disabled',
                pageClass: 'page-item',
                linkClass: 'page-item link',
                icons: {
                  first: '',
                  prev: '',
                  next: '',
                  last: '',
                },
              },
              paginationInfo: {
                infoClass: ''
              },
              icons: {
                first: 'glyphicon glyphicon-step-backward',
                prev: 'glyphicon glyphicon-chevron-left',
                next: 'glyphicon glyphicon-chevron-right',
                last: 'glyphicon glyphicon-step-forward',
              },
      },                  
    }
  },
  methods: {
    // render related functions
    renderVuetable(h) {
      return h(
        'vuetable', 
        { 
          ref: 'vuetable',
          props: {
            apiUrl: this.apiUrl,
            fields: this.fields,
            apiMode: true,
            paginationPath: "data",
            dataPath: "data.data",
            perPage: 10,
            multiSort: true,
            sortOrder: this.sortOrder,
            appendParams: this.appendParams,
            httpOptions: this.httpOptions,
            httpFetch: this.httpFetch,
            loadOnStart: this.loadOnStart,
            css: this.css.table,
            noDataTemplate: ' '
          },
          on: {
            'vuetable:load-error': this.onLoadError,
            'vuetable:cell-clicked': this.onCellClicked,
            'vuetable:pagination-data': this.onPaginationData,
            'vuetable:loading': this.onLoading,
            'vuetable:loaded': this.onLoaded
          },
          scopedSlots: this.$vnode.data.scopedSlots
        }
      )
    },
    renderPagination(h) {
      return h(
        'div',
        { class: {'vuetable-pagination': true} },
        [
          h('vuetable-pagination-info', { 
            ref: 'paginationInfo', 
            props: { 
              css: this.css.paginationInfo,
              infoTemplate: '{from} a {to} de {total} registros',
              noDataTemplate: this.noDataTemplate()
            } 
          }),
          h('vuetable-pagination', {
            ref: 'pagination',
            props: {
              css: this.css.pagination
            },
            on: {
              'vuetable-pagination:change-page': this.onChangePage
            }
          })
        ]
      )
    },
    // Cancela el request anterior para que una respuesta vieja no pise a la nueva
    httpFetch (apiUrl, httpOptions) {
      if (this.cancelSource) {
        this.cancelSource.cancel()
      }
      const source = axios.CancelToken.source()
      this.cancelSource = source
      return axios.get(apiUrl, Object.assign({}, httpOptions, { cancelToken: source.token }))
        .catch((error) => {
          if (axios.isCancel(error)) {
            // promesa pendiente: ni success ni failed, el request nuevo actualiza la tabla
            return new Promise(() => {})
          }
          throw error
        })
    },
    // ------------------
    allcap (value) {
      return value.toUpperCase()
    },
    booleanLabel (value) {
      return (value === true || value == 1)
        ? '<span class="label bg-green">SI</span>'
        : '<span class="label bg-red">NO</span>'
    },
    tagAssoc (value,attrs) {
      let _attrs = JSON.parse(attrs);
      if (typeof _attrs[value] !== 'undefined') {
        return '<span class="label ' + _attrs[value][1] + '">' + _attrs[value][0] + '</span>';
      } 
      return '';
    },
    formatNumber (value) {
      //return accounting.formatNumber(value, 2)
      return value
    },
    formatDate (value, fmt = 'DD/MM/YYYY') {
      return (value == null)
        ? ''
        : moment(value, 'YYYY-MM-DD').format(fmt)
    },
    formatJson(value,from,to) {
      let _from = from || 0;
      let _to = to || 0;

      let _out = [];
      if (value.length > _from) {
        if (value.length < _to) {
          _to = value.length;
        }

        for (let i = _from; i <= _to; i++){
          for (let key in value[i]){
              _out.push(key+": "+value[i][key]);  
          }        
        }        

      }
      return _out.join('<br>');
    },
    formatTelEmail(value,keys) {
      let _keys = keys || ['otro'];
      let _out = [];

      for (let i = 0; i <= value.length; i++) {
        if (typeof value[i] !== 'undefined') {
          _out.push('<b>' + value[i].desc + "</b>: " + value[i].val);    
        }
      }        

      return _out.join('<br>');
    }, 
    formatRole(value) {
      if (value.length > 0){
        return value[0].name
      }
      return '';
    },            
    onPaginationData (paginationData) {
        this.$refs.pagination.setPaginationData(paginationData)
        this.$refs.paginationInfo.setPaginationData(paginationData)
    },
    onChangePage (page) {
      this.$refs.vuetable.changePage(page)
    },
    onCellClicked (data, field, event) {
      //console.log('cellClicked: ', field.name)
      this.$refs.vuetable.toggleDetailRow(data.id)
    },
    onLoadError (error) {
      if (error && error.response && error.response.status === 401) {
        this.$store.dispatch('logoutRequest')
            .then(() => {
                this.$router.push({name: 'login'});
            });        
      }
    },
    onLoading () {
      this.loading = true;
    },
    onLoaded () {
      setTimeout(()=>{
        this.loading = false
      },500);    
      
      //this.$refs.paginationInfo.noDataTemplate = 'No se encontraron registros'
    },
    noDataTemplate () {
      return (this.loading ? 'Buscando...' : 'Busqueda sin resultados')
    },

    onFilterSet (filterText) {
      this.$set(this.appendParams, 'search', filterText)
      Vue.nextTick( () => {
        if (this.$refs.vuetable) {
          this.$refs.vuetable.refresh()
        } 
      })
    },
    onFilterReset () {
      this.$delete(this.appendParams, 'search')
      Vue.nextTick( () => {
        if (this.$refs.vuetable) {
          this.$refs.vuetable.refresh()
        } 
      })
    },
    onCreate () {
      this.$emit('create')
    },
    onRemSel () {
      if (this.$refs.vuetable.selectedTo.length > 0) {
        this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
          .then((dialog) => {
              this.$emit('remove-selected',_.clone(this.$refs.vuetable.selectedTo,true),dialog);
              this.$refs.vuetable.selectedTo = [];
          })
      } else {
        alert('Debe seleccionar al menos un registro');
      }
    }
  }
};
</script>
<style>
  .box-datatable .box-footer .vuetable-pagination .pagination{
    margin: 0;
  }
  .box-datatable .vuetable-th-checkbox-id{
    width: 20px;
  }
  .box-datatable .vuetable-th-checkbox-id{
    width: 30px;
  }
  .box-datatable .vuetable-th-sequence,.box-datatable .vuetable-th-id {
    width: 50px; 
  }
  @media (max-width: 500px) {
    .box-datatable .box-footer .vuetable-pagination {
      text-align: center; 
    }
    .box-datatable .box-footer .vuetable-pagination .vuetable-pagination-info{
      margin-bottom: 10px;
    }
  }
  @media (min-width: 501px) {
    .box-datatable .box-footer .vuetable-pagination .pagination{
      float: right;
    }
    .box-datatable .box-footer .vuetable-pagination .vuetable-pagination-info{
      display: inline-block;
      padding-top: 5px;
    }

  }

</style>
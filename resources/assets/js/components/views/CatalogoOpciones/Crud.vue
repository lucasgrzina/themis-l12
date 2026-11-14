<template>
<div>
    <data-table
      ref="list"
      :api-url="apiUrl"
      :fields="list.fields"
      :sort-order="list.sortOrder"
      :append-params="list.moreParams"
      :actionPerm="actionPerm"
      @create="create"
      @remove-selected="removeSelected">
      <template slot="slot-top-actions">
        <select v-model="grupo" class="form-control input-sm" style="display:inline-block;width:auto;" @change="onChangeGrupo">
          <option v-for="(label, key) in grupos" :key="key" :value="key">{{ label }}</option>
        </select>
      </template>
      <template slot="actions" slot-scope="props">
        <button-type v-can="actionPerm+':U'" type="edit-list" @click="onAction('edit', props.rowData, props.rowIndex)"/>
      </template>
    </data-table>

    <modal v-model="amModal.show" class="themis-modal" effect="fade" :backdrop="false">
      <div slot="modal-header" class="modal-header">
        <h4 class="modal-title">{{ amModal.title }} — {{ grupos[grupo] }}</h4>
      </div>
      <div slot="modal-body" class="modal-body" v-if="selectedItem" @keyup.enter="save()" @keyup.esc="closeAmModal()">
        <modal-errors :messages="amModal.errors"/>
        <div class="row">
          <div class="form-group col-sm-12" :class="{'has-error': errors.has('nombre')}">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" v-model="selectedItem.nombre" class="form-control" v-validate="'required'" data-vv-validate-on="none">
            <span class="help-block" v-show="errors.has('nombre')">{{ errors.first('nombre') }}</span>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-sm-4">
            <label for="orden">Orden</label>
            <input type="number" min="0" name="orden" v-model.number="selectedItem.orden" class="form-control">
          </div>
          <div class="form-group col-sm-8">
            <label>&nbsp;</label>
            <div class="checkbox" style="margin-top:0;">
              <label><input type="checkbox" v-model="selectedItem.vigente"> Vigente (se ofrece en el alta rápida)</label>
            </div>
          </div>
        </div>
        <p class="text-muted">La opción "Otro…" (texto libre) se agrega siempre y no hace falta cargarla.</p>
      </div>
      <div slot="modal-footer" class="modal-footer">
        <button-type type="close" @click="closeAmModal()"/>
        <button-type type="save" :promise="save"/>
      </div>
    </modal>
</div>
</template>

<script>
import { modal } from 'vue-strap'
import DataTable from '../../shared/datatable/DataTable'
import config from '../../../config'
import Api from '../../../api'

export default {
  name: 'ListadoCatalogoOpciones',
  components: { DataTable, modal },
  data () {
    return {
      grupos: {
        motivo_aviso: 'Motivos de aviso',
        tipo_contacto: 'Tipos de contacto',
        tipo_documento: 'Tipos de documento adjunto'
      },
      grupo: 'motivo_aviso',
      selectedItem: null,
      list: {
        fields: [
          { name: '__checkbox', titleClass: 'text-center', dataClass: 'text-center' },
          { name: 'id', title: 'ID', titleClass: 'text-center', dataClass: 'text-center', sortField: 'id' },
          { name: 'nombre', title: 'Nombre', sortField: 'nombre' },
          { name: 'orden', title: 'Orden', sortField: 'orden', titleClass: 'text-center', dataClass: 'text-center' },
          {
            name: 'vigente',
            title: 'Vigente',
            titleClass: 'text-center',
            dataClass: 'text-center',
            callback: 'tagAssoc|{"true": ["Sí","bg-green"],"false":["No","bg-gray"]}'
          },
          { name: '__slot:actions', titleClass: 'text-right', dataClass: 'text-right' }
        ],
        sortOrder: [{ name: 'orden', sortField: 'orden', direction: 'asc' }],
        moreParams: { grupo: 'motivo_aviso' }
      },
      amModal: { title: 'Crear/Editar opción', show: false, errors: '' },
      uri: 'catalogo-opciones/',
      actionPerm: 'opciones-de-alta',
      apiUrl: ''
    }
  },
  mounted () {
    this.apiUrl = config.serverURI + this.uri
  },
  methods: {
    onChangeGrupo () {
      this.list.moreParams.grupo = this.grupo
      this.$nextTick(() => {
        if (this.$refs.list.$refs.vuetable) {
          this.$refs.list.$refs.vuetable.refresh()
        }
      })
    },
    onAction (action, data) {
      this.$store.dispatch('hideSuccessNotification')
      if (action === 'edit') {
        this.reset(_.clone(data, true))
        this.amModal.show = true
      }
    },
    create () {
      this.reset()
      this.amModal.show = true
    },
    save () {
      this.amModal.errors = ''
      return this.$validator.validateAll().then((result) => {
        if (result) {
          this.selectedItem.grupo = this.grupo
          return Api.store(this.uri, this.selectedItem).then((resp) => {
            this.$refs.list.$refs.vuetable.refresh()
            this.closeAmModal()
            this.$store.dispatch('showSuccessNotification', resp.data.message)
          }, (resp) => {
            this.amModal.errors = resp.message
          })
        }
      })
    },
    removeSelected (ids, dialog) {
      dialog.loading(true)
      Api.delete(this.uri.concat('eliminar-seleccion'), { ids: ids }).then((resp) => {
        dialog.close()
        this.$refs.list.$refs.vuetable.refresh()
        this.$store.dispatch('showSuccessNotification', resp.data.message)
      }, () => {
        dialog.close()
      })
    },
    reset (item) {
      this.selectedItem = item || { id: 0, grupo: this.grupo, nombre: null, orden: 0, vigente: true }
      this.clearErrors()
    },
    clearErrors () {
      this.amModal.errors = ''
      this.$validator.reset()
    },
    closeAmModal () {
      this.amModal = _.assign(this.amModal, { show: false })
      this.reset()
    }
  }
}
</script>

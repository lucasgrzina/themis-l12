<template>
<div>
  <div class="box box-primary box-solid">
    <div class="box-header with-border">
      <h3 class="box-title">Parámetros de liquidación por cliente</h3>
    </div>
    <div class="box-body">
      <div class="row">
        <div class="form-group col-sm-6">
          <label>Cliente</label>
          <v-select v-model="clienteSel" :on-change="onChangeCliente" :options="clientes" label="nombre_completo"></v-select>
        </div>
        <div class="form-group col-sm-3" v-if="cliente">
          <label>Modelo de facturación</label>
          <select class="form-control" v-model="cliente.modelo_facturacion" @change="guardarFacturacion">
            <option value="horas">Por horas</option>
            <option value="presupuesto">Por presupuesto</option>
          </select>
        </div>
        <div class="form-group col-sm-3" v-if="cliente">
          <label style="display:block;">&nbsp;</label>
          <label><input type="checkbox" v-model="cliente.aplica_iva" @change="guardarFacturacion"> Aplica IVA</label>
        </div>
      </div>
    </div>
  </div>

  <template v-if="cliente">
    <!-- TARIFAS -->
    <div class="box" v-if="cliente.modelo_facturacion === 'horas'">
      <div class="box-header with-border">
        <h3 class="box-title">Tarifas (valor hora)</h3>
        <div class="box-tools pull-right">
          <button class="btn btn-primary btn-sm" @click="nuevaTarifa()"><i class="fa fa-plus"></i> Agregar</button>
        </div>
      </div>
      <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
          <thead>
            <tr><th>Categoría</th><th class="text-right">Valor hora</th><th>Moneda</th><th>Vigencia desde</th><th class="text-right">Acciones</th></tr>
          </thead>
          <tbody>
            <tr v-if="tarifaForm">
              <td><input type="text" class="form-control input-sm" v-model="tarifaForm.categoria" placeholder="(vacío = tarifa base)"></td>
              <td><input type="number" step="0.01" class="form-control input-sm text-right" v-model.number="tarifaForm.valor_hora"></td>
              <td>
                <select class="form-control input-sm" v-model="tarifaForm.moneda">
                  <option value="USD">USD</option><option value="ARS">ARS</option>
                </select>
              </td>
              <td><input type="date" class="form-control input-sm" v-model="tarifaForm.vigencia_desde"></td>
              <td class="text-right">
                <button class="btn btn-success btn-xs" @click="guardarTarifa()"><i class="fa fa-check"></i></button>
                <button class="btn btn-default btn-xs" @click="tarifaForm = null"><i class="fa fa-times"></i></button>
              </td>
            </tr>
            <tr v-for="t in tarifas" :key="t.id">
              <td>{{ t.categoria || '(Base)' }}</td>
              <td class="text-right">{{ formatNum(t.valor_hora) }}</td>
              <td>{{ t.moneda }}</td>
              <td>{{ t.vigencia_desde | fecha }}</td>
              <td class="text-right">
                <button class="btn btn-default btn-xs" @click="editarTarifa(t)"><i class="fa fa-pencil"></i></button>
                <button class="btn btn-danger btn-xs" @click="eliminar('time-tarifas/', t, cargarTarifas)"><i class="fa fa-trash"></i></button>
              </td>
            </tr>
            <tr v-if="!tarifas.length && !tarifaForm"><td colspan="5" class="text-center text-muted">Sin tarifas cargadas</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PARTICIPACIONES -->
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Participaciones de abogados</h3>
        <div class="box-tools pull-right">
          <button class="btn btn-primary btn-sm" @click="nuevaParticipacion()"><i class="fa fa-plus"></i> Agregar</button>
        </div>
      </div>
      <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
          <thead>
            <tr><th>Abogado</th><th class="text-right">Participación %</th><th>Período</th><th class="text-right">Acciones</th></tr>
          </thead>
          <tbody>
            <tr v-if="partForm">
              <td>
                <select class="form-control input-sm" v-model.number="partForm.user_id">
                  <option :value="null">Seleccione...</option>
                  <option v-for="a in abogados" :key="a.id" :value="a.id">{{ a.name }}</option>
                </select>
              </td>
              <td><input type="number" step="0.01" class="form-control input-sm text-right" v-model.number="partForm.porcentaje_pct" placeholder="Ej. 5"></td>
              <td>
                <input type="month" class="form-control input-sm" v-model="partForm.periodo_mes">
                <small class="text-muted">Vacío = por defecto (todos los meses)</small>
              </td>
              <td class="text-right">
                <button class="btn btn-success btn-xs" @click="guardarParticipacion()"><i class="fa fa-check"></i></button>
                <button class="btn btn-default btn-xs" @click="partForm = null"><i class="fa fa-times"></i></button>
              </td>
            </tr>
            <tr v-for="p in participaciones" :key="p.id">
              <td>{{ p.usuario ? p.usuario.name : '' }}</td>
              <td class="text-right">{{ formatNum(p.porcentaje * 100) }} %</td>
              <td><span v-if="p.periodo" class="label label-info">{{ p.periodo | mesAnio }}</span><span v-else class="label label-default">Por defecto</span></td>
              <td class="text-right">
                <button class="btn btn-default btn-xs" @click="editarParticipacion(p)"><i class="fa fa-pencil"></i></button>
                <button class="btn btn-danger btn-xs" @click="eliminar('time-participaciones/', p, cargarParticipaciones)"><i class="fa fa-trash"></i></button>
              </td>
            </tr>
            <tr v-if="!participaciones.length && !partForm"><td colspan="4" class="text-center text-muted">Sin participaciones cargadas</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PRESUPUESTOS -->
    <div class="box" v-if="cliente.modelo_facturacion === 'presupuesto'">
      <div class="box-header with-border">
        <h3 class="box-title">Presupuestos / abonos</h3>
        <div class="box-tools pull-right">
          <button class="btn btn-primary btn-sm" @click="nuevoPresupuesto()"><i class="fa fa-plus"></i> Agregar</button>
        </div>
      </div>
      <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
          <thead>
            <tr><th>Período</th><th>Descripción</th><th class="text-right">Subtotal $</th><th>IVA</th><th class="text-right">Acciones</th></tr>
          </thead>
          <tbody>
            <tr v-if="presupForm">
              <td><input type="month" class="form-control input-sm" v-model="presupForm.periodo_mes"></td>
              <td><input type="text" class="form-control input-sm" v-model="presupForm.descripcion"></td>
              <td><input type="number" step="0.01" class="form-control input-sm text-right" v-model.number="presupForm.subtotal"></td>
              <td><label><input type="checkbox" v-model="presupForm.aplica_iva"> IVA</label></td>
              <td class="text-right">
                <button class="btn btn-success btn-xs" @click="guardarPresupuesto()"><i class="fa fa-check"></i></button>
                <button class="btn btn-default btn-xs" @click="presupForm = null"><i class="fa fa-times"></i></button>
              </td>
            </tr>
            <tr v-for="pr in presupuestos" :key="pr.id">
              <td>{{ pr.periodo | mesAnio }}</td>
              <td>{{ pr.descripcion }}</td>
              <td class="text-right">{{ formatNum(pr.subtotal) }}</td>
              <td>{{ pr.aplica_iva ? 'Sí' : 'No' }}</td>
              <td class="text-right">
                <button class="btn btn-default btn-xs" @click="editarPresupuesto(pr)"><i class="fa fa-pencil"></i></button>
                <button class="btn btn-danger btn-xs" @click="eliminar('time-presupuestos/', pr, cargarPresupuestos)"><i class="fa fa-trash"></i></button>
              </td>
            </tr>
            <tr v-if="!presupuestos.length && !presupForm"><td colspan="5" class="text-center text-muted">Sin presupuestos cargados</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>
</div>
</template>

<script>
import Vue from 'vue'
import vSelect from 'vue-select'
import Api from '../../../../api'

export default {
  name: 'TimeParametrosCliente',
  components: { vSelect },
  data () {
    return {
      clientes: [],
      abogados: [],
      clienteSel: null,
      cliente: null,
      tarifas: [],
      participaciones: [],
      presupuestos: [],
      tarifaForm: null,
      partForm: null,
      presupForm: null
    }
  },
  mounted () {
    Api.combos('time/clientes').then(resp => { this.clientes = resp.data })
    Api.combos('time/abogados').then(resp => { this.abogados = resp.data })
  },
  methods: {
    formatNum (v) {
      let n = parseFloat(v || 0)
      return n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
    mesPrimerDia (mes) { return mes ? mes + '-01' : null },
    onChangeCliente (item) {
      this.tarifaForm = this.partForm = this.presupForm = null
      if (!item) { this.cliente = null; this.clienteSel = null; return }
      this.clienteSel = item
      Api.get('clientes/' + item.id).then(resp => {
        let c = resp.data
        this.cliente = {
          id: c.id,
          nombre_completo: c.nombre_completo,
          modelo_facturacion: c.modelo_facturacion || 'horas',
          aplica_iva: !!c.aplica_iva
        }
        this.cargarTarifas()
        this.cargarParticipaciones()
        this.cargarPresupuestos()
      })
    },
    cargarTarifas () {
      Api.get('time-tarifas?cliente_id=' + this.cliente.id + '&per_page=200').then(resp => { this.tarifas = resp.data.data })
    },
    cargarParticipaciones () {
      Api.get('time-participaciones?cliente_id=' + this.cliente.id + '&per_page=200').then(resp => { this.participaciones = resp.data.data })
    },
    cargarPresupuestos () {
      Api.get('time-presupuestos?cliente_id=' + this.cliente.id + '&per_page=200').then(resp => { this.presupuestos = resp.data.data })
    },
    guardarFacturacion () {
      Api.request('PUT', 'clientes/' + this.cliente.id + '/facturacion', {
        modelo_facturacion: this.cliente.modelo_facturacion,
        aplica_iva: this.cliente.aplica_iva
      }).then(r => { this.$store.dispatch('showSuccessNotification', r.data.message) })
    },
    // --- Tarifas ---
    nuevaTarifa () {
      this.tarifaForm = { id: 0, cliente_id: this.cliente.id, categoria: null, valor_hora: null, moneda: 'USD', vigencia_desde: null }
    },
    editarTarifa (t) {
      this.tarifaForm = { id: t.id, cliente_id: this.cliente.id, categoria: t.categoria, valor_hora: parseFloat(t.valor_hora), moneda: t.moneda, vigencia_desde: t.vigencia_desde ? t.vigencia_desde.substring(0, 10) : null }
    },
    guardarTarifa () {
      let data = Object.assign({}, this.tarifaForm)
      if (data.categoria === '') data.categoria = null
      Api.store('time-tarifas/', data).then(r => {
        this.$store.dispatch('showSuccessNotification', r.data.message)
        this.tarifaForm = null
        this.cargarTarifas()
      }, err => this.notificarError(err))
    },
    // --- Participaciones ---
    nuevaParticipacion () {
      this.partForm = { id: 0, cliente_id: this.cliente.id, user_id: null, porcentaje_pct: null, periodo_mes: null }
    },
    editarParticipacion (p) {
      this.partForm = { id: p.id, cliente_id: this.cliente.id, user_id: p.user_id, porcentaje_pct: parseFloat(p.porcentaje) * 100, periodo_mes: p.periodo ? p.periodo.substring(0, 7) : null }
    },
    guardarParticipacion () {
      let data = {
        id: this.partForm.id,
        cliente_id: this.cliente.id,
        user_id: this.partForm.user_id,
        porcentaje: (parseFloat(this.partForm.porcentaje_pct) || 0) / 100,
        periodo: this.mesPrimerDia(this.partForm.periodo_mes)
      }
      Api.store('time-participaciones/', data).then(r => {
        this.$store.dispatch('showSuccessNotification', r.data.message)
        this.partForm = null
        this.cargarParticipaciones()
      }, err => this.notificarError(err))
    },
    // --- Presupuestos ---
    nuevoPresupuesto () {
      this.presupForm = { id: 0, cliente_id: this.cliente.id, periodo_mes: null, descripcion: null, subtotal: null, aplica_iva: true }
    },
    editarPresupuesto (pr) {
      this.presupForm = { id: pr.id, cliente_id: this.cliente.id, periodo_mes: pr.periodo ? pr.periodo.substring(0, 7) : null, descripcion: pr.descripcion, subtotal: parseFloat(pr.subtotal), aplica_iva: !!pr.aplica_iva }
    },
    guardarPresupuesto () {
      let data = {
        id: this.presupForm.id,
        cliente_id: this.cliente.id,
        periodo: this.mesPrimerDia(this.presupForm.periodo_mes),
        descripcion: this.presupForm.descripcion,
        subtotal: this.presupForm.subtotal,
        aplica_iva: this.presupForm.aplica_iva
      }
      Api.store('time-presupuestos/', data).then(r => {
        this.$store.dispatch('showSuccessNotification', r.data.message)
        this.presupForm = null
        this.cargarPresupuestos()
      }, err => this.notificarError(err))
    },
    // --- común ---
    eliminar (uri, item, reload) {
      this.$dialog.confirm('¿Desea eliminar el registro?', { loader: true }).then(dialog => {
        Api.delete(uri, item).then(r => {
          dialog.close()
          this.$store.dispatch('showSuccessNotification', r.data.message)
          reload()
        }, () => { dialog.close() })
      })
    },
    notificarError (err) {
      let msg = (err && err.message) ? err.message : 'No se pudo guardar el registro'
      this.$store.dispatch('showErrorNotification', msg)
    }
  },
  filters: {
    fecha (v) { return v ? v.substring(8, 10) + '/' + v.substring(5, 7) + '/' + v.substring(0, 4) : '' },
    mesAnio (v) { return v ? v.substring(5, 7) + '/' + v.substring(0, 4) : '' }
  }
}
</script>

<style scoped>
.box-title { font-weight: bold; }
</style>

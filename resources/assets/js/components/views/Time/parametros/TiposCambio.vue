<template>
<div>
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">Tipo de cambio mensual</h3>
      <div class="box-tools pull-right">
        <button class="btn btn-primary btn-sm" @click="nuevo()"><i class="fa fa-plus"></i> Agregar</button>
      </div>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover">
        <thead>
          <tr><th>Período</th><th>Cotización</th><th class="text-right">Valor</th><th class="text-right">Acciones</th></tr>
        </thead>
        <tbody>
          <tr v-if="form">
            <td><input type="month" class="form-control input-sm" v-model="form.periodo_mes"></td>
            <td><input type="text" class="form-control input-sm" v-model="form.nombre" placeholder="MEP"></td>
            <td><input type="number" step="0.0001" class="form-control input-sm text-right" v-model.number="form.valor"></td>
            <td class="text-right">
              <button class="btn btn-success btn-xs" @click="guardar()"><i class="fa fa-check"></i></button>
              <button class="btn btn-default btn-xs" @click="form = null"><i class="fa fa-times"></i></button>
            </td>
          </tr>
          <tr v-for="t in items" :key="t.id">
            <td>{{ t.periodo | mesAnio }}</td>
            <td>{{ t.nombre }}</td>
            <td class="text-right">{{ formatNum(t.valor) }}</td>
            <td class="text-right">
              <button class="btn btn-default btn-xs" @click="editar(t)"><i class="fa fa-pencil"></i></button>
              <button class="btn btn-danger btn-xs" @click="eliminar(t)"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
          <tr v-if="!items.length && !form"><td colspan="4" class="text-center text-muted">Sin registros</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</template>

<script>
import Api from '../../../../api'

export default {
  name: 'TimeTiposCambio',
  data () {
    return { items: [], form: null }
  },
  mounted () { this.cargar() },
  methods: {
    formatNum (v) { return parseFloat(v || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 4 }) },
    cargar () { Api.get('time-tipos-cambio?per_page=500').then(resp => { this.items = resp.data.data }) },
    nuevo () { this.form = { id: 0, periodo_mes: null, nombre: 'MEP', valor: null } },
    editar (t) { this.form = { id: t.id, periodo_mes: t.periodo ? t.periodo.substring(0, 7) : null, nombre: t.nombre, valor: parseFloat(t.valor) } },
    guardar () {
      let data = { id: this.form.id, periodo: this.form.periodo_mes ? this.form.periodo_mes + '-01' : null, nombre: this.form.nombre, valor: this.form.valor }
      Api.store('time-tipos-cambio/', data).then(r => {
        this.$store.dispatch('showSuccessNotification', r.data.message)
        this.form = null
        this.cargar()
      }, err => this.$store.dispatch('showErrorNotification', (err && err.message) ? err.message : 'No se pudo guardar'))
    },
    eliminar (t) {
      this.$dialog.confirm('¿Desea eliminar el registro?', { loader: true }).then(dialog => {
        Api.delete('time-tipos-cambio/', t).then(r => { dialog.close(); this.$store.dispatch('showSuccessNotification', r.data.message); this.cargar() }, () => dialog.close())
      })
    }
  },
  filters: {
    mesAnio (v) { return v ? v.substring(5, 7) + '/' + v.substring(0, 4) : '' }
  }
}
</script>

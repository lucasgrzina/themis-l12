<template>
<div>
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">Impuestos y retenciones</h3>
      <div class="box-tools pull-right">
        <button class="btn btn-primary btn-sm" @click="nuevo()"><i class="fa fa-plus"></i> Agregar vigencia</button>
      </div>
    </div>
    <div class="box-body">
      <p class="text-muted">Tasas globales. Se toma la vigencia más reciente anterior o igual al período del informe. El IVA solo se aplica a clientes marcados con "Aplica IVA".</p>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover">
        <thead>
          <tr><th>Código</th><th>Nombre</th><th class="text-right">Tasa %</th><th>Vigencia desde</th><th class="text-right">Acciones</th></tr>
        </thead>
        <tbody>
          <tr v-if="form">
            <td>
              <select class="form-control input-sm" v-model="form.codigo">
                <option value="imp_cto_dto">imp_cto_dto</option>
                <option value="sircreb">sircreb</option>
                <option value="ret_gcias">ret_gcias</option>
                <option value="iva">iva</option>
              </select>
            </td>
            <td><input type="text" class="form-control input-sm" v-model="form.nombre"></td>
            <td><input type="number" step="0.01" class="form-control input-sm text-right" v-model.number="form.tasa_pct"></td>
            <td><input type="date" class="form-control input-sm" v-model="form.vigencia_desde"></td>
            <td class="text-right">
              <button class="btn btn-success btn-xs" @click="guardar()"><i class="fa fa-check"></i></button>
              <button class="btn btn-default btn-xs" @click="form = null"><i class="fa fa-times"></i></button>
            </td>
          </tr>
          <tr v-for="r in items" :key="r.id">
            <td>{{ r.codigo }}</td>
            <td>{{ r.nombre }}</td>
            <td class="text-right">{{ formatNum(r.tasa * 100) }} %</td>
            <td>{{ r.vigencia_desde | fecha }}</td>
            <td class="text-right">
              <button class="btn btn-default btn-xs" @click="editar(r)"><i class="fa fa-pencil"></i></button>
              <button class="btn btn-danger btn-xs" @click="eliminar(r)"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
          <tr v-if="!items.length && !form"><td colspan="5" class="text-center text-muted">Sin registros</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</template>

<script>
import Api from '../../../../api'

export default {
  name: 'TimeRetenciones',
  data () {
    return { items: [], form: null }
  },
  mounted () { this.cargar() },
  methods: {
    formatNum (v) { return parseFloat(v || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
    cargar () { Api.get('time-retenciones?per_page=500').then(resp => { this.items = resp.data.data }) },
    nuevo () { this.form = { id: 0, codigo: 'imp_cto_dto', nombre: null, tasa_pct: null, vigencia_desde: null } },
    editar (r) { this.form = { id: r.id, codigo: r.codigo, nombre: r.nombre, tasa_pct: parseFloat(r.tasa) * 100, vigencia_desde: r.vigencia_desde ? r.vigencia_desde.substring(0, 10) : null } },
    guardar () {
      let data = { id: this.form.id, codigo: this.form.codigo, nombre: this.form.nombre, tasa: (parseFloat(this.form.tasa_pct) || 0) / 100, vigencia_desde: this.form.vigencia_desde }
      Api.store('time-retenciones/', data).then(r => {
        this.$store.dispatch('showSuccessNotification', r.data.message)
        this.form = null
        this.cargar()
      }, err => this.$store.dispatch('showErrorNotification', (err && err.message) ? err.message : 'No se pudo guardar'))
    },
    eliminar (r) {
      this.$dialog.confirm('¿Desea eliminar el registro?', { loader: true }).then(dialog => {
        Api.delete('time-retenciones/', r).then(res => { dialog.close(); this.$store.dispatch('showSuccessNotification', res.data.message); this.cargar() }, () => dialog.close())
      })
    }
  },
  filters: {
    fecha (v) { return v ? v.substring(8, 10) + '/' + v.substring(5, 7) + '/' + v.substring(0, 4) : '' }
  }
}
</script>

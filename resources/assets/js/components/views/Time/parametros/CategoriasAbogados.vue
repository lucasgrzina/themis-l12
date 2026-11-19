<template>
<div>
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">Categorías de abogados</h3>
    </div>
    <div class="box-body">
      <p class="text-muted">La categoría determina qué tarifa (valor hora) del cliente se aplica a las horas de cada abogado.</p>
      <form class="form-inline">
        <div class="form-group">
          <select v-model="estado" class="form-control input-sm" @change="cargar">
            <option value="activos">Activos</option>
            <option value="inactivos">Inactivos</option>
            <option value="todos">Todos (activos e inactivos)</option>
          </select>
        </div>
      </form>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover">
        <thead>
          <tr><th>Abogado</th><th>Categoría</th><th class="text-right">Acciones</th></tr>
        </thead>
        <tbody>
          <tr v-for="a in abogados" :key="a.id">
            <td>{{ a.name }}</td>
            <td>
              <select class="form-control input-sm" v-model="a.categoria_abogado_id" style="max-width:260px;">
                <option :value="null">(Base)</option>
                <option v-for="c in categorias" :value="c.id">{{ c.nombre }}</option>
              </select>
            </td>
            <td class="text-right">
              <button class="btn btn-success btn-xs" @click="guardar(a)"><i class="fa fa-check"></i> Guardar</button>
            </td>
          </tr>
          <tr v-if="!abogados.length"><td colspan="3" class="text-center text-muted">Sin abogados</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</template>

<script>
import Api from '../../../../api'

export default {
  name: 'TimeCategoriasAbogados',
  data () {
    return { abogados: [], categorias: [], estado: 'activos' }
  },
  mounted () { this.cargar() },
  methods: {
    cargar () {
      Api.combos('time/categorias-abogado').then(resp => { this.categorias = resp.data })
      Api.combos('time/abogados?estado=' + this.estado).then(resp => {
        this.abogados = resp.data.map(u => ({ id: u.id, name: u.name, categoria_abogado_id: u.categoria_abogado_id || null }))
      })
    },
    guardar (a) {
      Api.request('PUT', 'usuarios/' + a.id + '/categoria-abogado', { categoria_abogado_id: a.categoria_abogado_id || null })
        .then(r => this.$store.dispatch('showSuccessNotification', r.data.message),
              err => this.$store.dispatch('showErrorNotification', 'No se pudo guardar'))
    }
  }
}
</script>

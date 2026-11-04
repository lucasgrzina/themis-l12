<template>
<div>
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">Categorías de abogados</h3>
    </div>
    <div class="box-body">
      <p class="text-muted">La categoría determina qué tarifa (valor hora) del cliente se aplica a las horas de cada abogado.</p>
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
              <input type="text" class="form-control input-sm" v-model="a.categoria_abogado" list="categorias-list" placeholder="Base / Semi Sr / Sr / Director" style="max-width:260px;">
            </td>
            <td class="text-right">
              <button class="btn btn-success btn-xs" @click="guardar(a)"><i class="fa fa-check"></i> Guardar</button>
            </td>
          </tr>
          <tr v-if="!abogados.length"><td colspan="3" class="text-center text-muted">Sin abogados</td></tr>
        </tbody>
      </table>
      <datalist id="categorias-list">
        <option value="Semi Sr"></option>
        <option value="Sr"></option>
        <option value="Director"></option>
        <option value="Abono"></option>
      </datalist>
    </div>
  </div>
</div>
</template>

<script>
import Api from '../../../../api'

export default {
  name: 'TimeCategoriasAbogados',
  data () {
    return { abogados: [] }
  },
  mounted () { this.cargar() },
  methods: {
    cargar () {
      Api.combos('time/abogados').then(resp => {
        this.abogados = resp.data.map(u => ({ id: u.id, name: u.name, categoria_abogado: u.categoria_abogado || '' }))
      })
    },
    guardar (a) {
      Api.request('PUT', 'usuarios/' + a.id + '/categoria-abogado', { categoria_abogado: a.categoria_abogado || null })
        .then(r => this.$store.dispatch('showSuccessNotification', r.data.message),
              err => this.$store.dispatch('showErrorNotification', 'No se pudo guardar'))
    }
  }
}
</script>

<template>
<div>
  <div class="box box-primary box-solid themis-modal">
    <div class="box-header with-border modal-header">
      <h3 class="box-title">Filtros</h3>
    </div>
    <div class="box-body">
      <fieldset class="transparente">
        <form class="row">
          <div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.desde')}">
            <label style="display:block;">Desde</label>
            <datepicker name="desde" v-model="filtros.desde" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
          </div>
          <div class="form-group col-sm-3" :class="{'has-error': errors.has('filtros.hasta')}">
            <label style="display:block;">Hasta</label>
            <datepicker name="hasta" v-model="filtros.hasta" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
          </div>
          <div class="form-group col-sm-3">
            <label>Cliente (opcional)</label>
            <v-select v-model="clienteSel" :on-change="onChangeCliente" :options="clientes" label="nombre_completo"></v-select>
          </div>
          <div class="form-group col-sm-3">
            <label class="hidden-xs" style="display:block;">&nbsp;</label>
            <button-type type="filter" @click="doFilter"/>
          </div>
        </form>
      </fieldset>
    </div>
  </div>

  <div class="box" v-if="firstFilter">
    <div class="box-header">
      <h3 class="box-title">&nbsp;</h3>
      <div class="box-tools">
        <button class="btn btn-default btn-sm" @click="exportar()"><i class="fa fa-file-excel-o"></i> Excel</button>
        <button-type type="print" @click="imprimir()"/>
      </div>
    </div>
    <div class="box-body">
      <div v-if="loading" class="text-center"><pulse-loader :loading="loading"></pulse-loader></div>
      <template v-else>
        <p v-if="result" class="text-muted">
          Período {{ result.desde | f }} al {{ result.hasta | f }} · TC MEP: {{ num(result.tc) }}
        </p>
        <div v-for="c in (result ? result.clientes : [])" :key="c.cliente_id" class="liq-cliente">
          <h4 class="liq-head">{{ c.cliente }} <small>({{ c.modelo === 'presupuesto' ? 'Presupuesto' : 'Por horas' }})</small></h4>

          <table v-if="c.modelo === 'horas'" class="table table-condensed table-bordered">
            <thead>
              <tr>
                <th>Abogado</th><th>Categoría</th><th class="text-right">Horas</th>
                <th class="text-right">Valor hora</th><th class="text-right">Bruto U$S</th>
                <th class="text-right">Part. %</th><th class="text-right">Participación $</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="a in c.abogados" :key="a.user_id">
                <td>{{ a.abogado }}</td>
                <td>{{ a.categoria || '—' }}</td>
                <td class="text-right">{{ num(a.horas) }}</td>
                <td class="text-right">{{ num(a.valor_hora) }}</td>
                <td class="text-right">{{ num(a.bruto_usd) }}</td>
                <td class="text-right">{{ num(a.pct * 100) }}%</td>
                <td class="text-right">{{ num(a.participacion) }}</td>
              </tr>
            </tbody>
          </table>

          <table v-else class="table table-condensed table-bordered">
            <thead>
              <tr><th>Descripción</th><th class="text-right">Subtotal $</th><th>IVA</th><th class="text-right">Con IVA $</th></tr>
            </thead>
            <tbody>
              <tr v-for="(it, i) in c.items" :key="i">
                <td>{{ it.descripcion || '—' }}</td>
                <td class="text-right">{{ num(it.subtotal) }}</td>
                <td>{{ it.aplica_iva ? 'Sí' : 'No' }}</td>
                <td class="text-right">{{ num(it.con_iva) }}</td>
              </tr>
            </tbody>
          </table>

          <div class="row">
            <div class="col-sm-5 col-sm-offset-7">
              <table class="table table-condensed liq-tot">
                <tbody>
                  <template v-if="c.modelo === 'horas'">
                    <tr><td>Total U$S</td><td class="text-right">{{ num(c.total_usd) }}</td></tr>
                    <tr><td>Total $ (x TC)</td><td class="text-right">{{ num(c.total_ars) }}</td></tr>
                  </template>
                  <template v-else>
                    <tr><td>Subtotal $</td><td class="text-right">{{ num(c.subtotal) }}</td></tr>
                    <tr><td>Con IVA</td><td class="text-right">{{ num(c.con_iva) }}</td></tr>
                    <tr><td>IVA</td><td class="text-right">-{{ num(c.iva_ret) }}</td></tr>
                  </template>
                  <tr><td>Imp. al cto y dto</td><td class="text-right">-{{ num(c.imp_cto_dto) }}</td></tr>
                  <tr><td>SIRCREB</td><td class="text-right">-{{ num(c.sircreb) }}</td></tr>
                  <tr><td>Ret. Ganancias</td><td class="text-right">-{{ num(c.ret_gcias) }}</td></tr>
                  <tr v-if="c.modelo === 'horas'"><td>Participaciones</td><td class="text-right">-{{ num(c.total_participaciones) }}</td></tr>
                  <tr class="liq-neto"><td>NETO CVA</td><td class="text-right">{{ num(c.neto_cva) }}</td></tr>
                </tbody>
              </table>
              <p v-if="c.participaciones_externas_pendiente" class="text-danger" style="font-size:11px;">
                * No incluye participaciones de colaboradores externos (pendiente).
              </p>
            </div>
          </div>
        </div>
        <p v-if="result && !result.clientes.length" class="text-muted">No hay datos para el período seleccionado.</p>
      </template>
    </div>
  </div>
  <div class="box" v-else>
    <div class="box-body text-center"><p>Cargue el período y haga click en "Filtrar".</p></div>
  </div>
</div>
</template>

<script>
import moment from 'moment'
import { datepicker } from 'vue-strap'
import vSelect from 'vue-select'
import Api from '../../../../api'

export default {
  name: 'TimeLiquidacion',
  components: { datepicker, vSelect },
  data () {
    return {
      firstFilter: false,
      loading: false,
      clientes: [],
      clienteSel: null,
      filtros: {
        desde: moment().startOf('month').format('DD/MM/YYYY'),
        hasta: moment().endOf('month').format('DD/MM/YYYY'),
        cliente_id: null
      },
      result: null
    }
  },
  mounted () {
    Api.combos('time/clientes').then(resp => { this.clientes = resp.data })
  },
  methods: {
    num (v) { return parseFloat(v || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
    onChangeCliente (item) { this.filtros.cliente_id = item ? item.id : null },
    doFilter () {
      this.firstFilter = true
      this.loading = true
      Api.post('time-impresiones/liquidacion', this.filtros).then(resp => {
        this.result = resp.data
        this.loading = false
      }, () => { this.result = null; this.loading = false })
    },
    queryString () {
      return Object.keys(this.filtros).map(k => this.filtros[k] != null
        ? encodeURIComponent(k) + '=' + encodeURIComponent(this.filtros[k]) : '').filter(x => x).join('&')
    },
    imprimir () { window.open(Laravel.webDomain.concat('/time/imp-liquidacion?').concat(this.queryString())) },
    exportar () { window.open(Laravel.webDomain.concat('/time/exp-liquidacion?').concat(this.queryString())) }
  },
  filters: {
    f (v) { return v ? moment(v, 'YYYY-MM-DD').format('DD/MM/YYYY') : '' }
  }
}
</script>

<style scoped>
.liq-cliente { margin-bottom: 26px; }
.liq-head { background: #2c3e50; color: #fff; padding: 8px 12px; margin: 0 0 10px 0; font-size: 15px; }
.liq-tot td { padding: 4px 8px; }
.liq-neto td { font-weight: bold; font-size: 15px; background: #eafaf1; border-top: 2px solid #27ae60; }
</style>

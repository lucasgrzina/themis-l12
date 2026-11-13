<template>
<div>
	<!-- ============ FILTROS ============ -->
	<div class="box box-primary box-solid themis-modal">
		<div class="box-header with-border modal-header">
			<h3 class="box-title">Filtros</h3>
			<div class="box-tools pull-right">
				<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
			</div>
		</div>
		<div class="box-body">
			<fieldset class="transparente" :disabled="!canFilter">
			<form class="row" @submit.prevent="doFilter">
				<div class="form-group col-sm-12">
					<label style="display:block;">Período rápido</label>
					<div class="btn-group">
						<button type="button" class="btn btn-sm btn-default" v-for="r in rangos" :key="r.key" @click="setRango(r.key)">{{ r.label }}</button>
					</div>
				</div>

				<div class="form-group col-sm-2">
					<label style="display:block;">Desde</label>
					<datepicker v-model="filtros.desde" :format="'dd/MM/yyyy'" :clear-button="false" placeholder="DD/MM/AAAA"></datepicker>
				</div>
				<div class="form-group col-sm-2">
					<label style="display:block;">Hasta</label>
					<datepicker v-model="filtros.hasta" :format="'dd/MM/yyyy'" :clear-button="false" placeholder="DD/MM/AAAA"></datepicker>
				</div>

				<div class="form-group col-sm-4" v-if="!soloPropio">
					<label>Abogados <small class="text-muted">(vacío = todos)</small></label>
					<v-select multiple v-model="sel.abogados" :options="info.abogados" label="name"></v-select>
				</div>
				<div class="form-group col-sm-4">
					<label>Clientes <small class="text-muted">(vacío = todos)</small></label>
					<v-select multiple v-model="sel.clientes" :options="clientes" label="nombre_completo"></v-select>
				</div>

				<div class="form-group col-sm-3">
					<label>Gestión</label>
					<v-select multiple v-model="sel.gestiones" :options="info.gestiones" label="nombre"></v-select>
				</div>
				<div class="form-group col-sm-2">
					<label>Facturable</label>
					<select v-model="filtros.facturar" class="form-control">
						<option value="">Todas</option>
						<option value="1">Facturables</option>
						<option value="0">No facturables</option>
					</select>
				</div>
				<div class="form-group col-sm-2">
					<label>Estado</label>
					<select v-model="filtros.estado" class="form-control">
						<option value="">Todas</option>
						<option value="vinculada">Confirmadas</option>
						<option value="pendiente">Pendientes</option>
					</select>
				</div>
				<div class="form-group col-sm-2" v-if="!soloPropio && categorias.length">
					<label>Categoría abogado</label>
					<select v-model="filtros.categoria_abogado" class="form-control">
						<option value="">Todas</option>
						<option v-for="c in categorias" :value="c">{{ c }}</option>
					</select>
				</div>
				<div class="form-group col-sm-3">
					<label>Referencia contiene</label>
					<input type="text" v-model="filtros.referencia" class="form-control" placeholder="Texto libre">
				</div>
				<div class="form-group col-sm-3">
					<label class="hidden-xs" style="display:block;">&nbsp;</label>
					<button-type type="filter" @click="doFilter" :disabled="!canFilter"/>
					<button type="button" class="btn btn-sm btn-default" @click="limpiar">Limpiar</button>
				</div>
			</form>
			</fieldset>
		</div>
	</div>

	<div v-if="!loaded" class="box"><div class="box-body text-center"><pulse-loader :loading="loading"></pulse-loader><p v-if="!loading">Cargando…</p></div></div>

	<template v-if="loaded && res">
		<div v-if="res.alcance_propio" class="callout callout-info" style="margin-bottom:10px;">
			Su nivel de acceso muestra únicamente sus propias horas.
		</div>

		<!-- ============ ALCANCE / MIGAS ============ -->
		<div class="box box-solid" v-if="hayDrill">
			<div class="box-body">
				<strong>Detalle de:</strong>
				<span class="label label-primary" v-for="a in sel.abogados" :key="'a'+a.id" style="margin-left:5px;">{{ a.name }}</span>
				<span class="label label-info" v-for="c in sel.clientes" :key="'c'+c.id" style="margin-left:5px;">{{ c.nombre_completo }}</span>
				<a href="javascript:void(0)" class="pull-right" @click="limpiarDrill">Volver al total del estudio</a>
			</div>
		</div>

		<!-- ============ KPIs ============ -->
		<div class="row">
			<div class="col-md-3 col-sm-6">
				<div class="info-box"><span class="info-box-icon bg-purple"><i class="fa fa-clock-o"></i></span>
					<div class="info-box-content">
						<span class="info-box-text">Horas totales</span>
						<span class="info-box-number">{{ fmt(res.totales.minutos) }} hs</span>
						<span class="progress-description" :class="variacion >= 0 ? 'text-green' : 'text-red'" v-if="variacion !== null">
							<i class="fa" :class="variacion >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i> {{ Math.abs(variacion) }}% vs. período anterior ({{ fmt(res.totales.minutos_prev) }} hs)
						</span>
					</div></div>
			</div>
			<div class="col-md-3 col-sm-6">
				<div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
					<div class="info-box-content">
						<span class="info-box-text">Abogados / Clientes</span>
						<span class="info-box-number">{{ res.totales.abogados }} / {{ res.totales.clientes }}</span>
						<span class="progress-description">{{ res.totales.movimientos }} movimientos</span>
					</div></div>
			</div>
			<div class="col-md-3 col-sm-6">
				<div class="info-box"><span class="info-box-icon bg-green"><i class="fa fa-check-square-o"></i></span>
					<div class="info-box-content">
						<span class="info-box-text">Facturables</span>
						<span class="info-box-number">{{ fmt(res.totales.facturables) }} hs</span>
						<span class="progress-description">{{ pctFacturable }}% del total</span>
					</div></div>
			</div>
			<div class="col-md-3 col-sm-6">
				<div class="info-box"><span class="info-box-icon bg-orange"><i class="fa fa-calendar"></i></span>
					<div class="info-box-content">
						<span class="info-box-text">Promedio por día con carga</span>
						<span class="info-box-number">{{ fmt(res.totales.promedio_dia) }} hs</span>
						<span class="progress-description">{{ res.periodo.dias }} días en el período</span>
					</div></div>
			</div>
		</div>

		<div v-if="res.totales.movimientos === 0" class="box"><div class="box-body text-center">No se encontraron horas con los filtros indicados.</div></div>

		<template v-else>
		<!-- ============ GRAFICOS ============ -->
		<div class="row">
			<div class="col-md-6" v-if="!soloPropio">
				<div class="box box-primary">
					<div class="box-header with-border"><h3 class="box-title">Horas por abogado</h3>
						<small class="text-muted"> — click en una barra para ver su detalle</small></div>
					<div class="box-body">
						<chart-canvas type="bar" :data="chAbogado" :options="optBarH" :height="altoBarras(res.por_abogado.length)" @select="drillAbogado"/>
					</div>
				</div>
			</div>
			<div :class="soloPropio ? 'col-md-12' : 'col-md-6'">
				<div class="box box-info">
					<div class="box-header with-border"><h3 class="box-title">Horas por cliente</h3>
						<small class="text-muted"> — click en una barra para ver su detalle</small></div>
					<div class="box-body">
						<chart-canvas type="bar" :data="chCliente" :options="optBarH" :height="altoBarras(Math.min(res.por_cliente.length, TOP_CLIENTES))" @select="drillCliente"/>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-8">
				<div class="box box-success">
					<div class="box-header with-border"><h3 class="box-title">Evolución diaria</h3></div>
					<div class="box-body">
						<chart-canvas type="bar" :data="chDia" :options="optDia" :height="260"/>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="box box-warning">
					<div class="box-header with-border"><h3 class="box-title">Facturable vs. no facturable</h3></div>
					<div class="box-body">
						<chart-canvas type="doughnut" :data="chFacturable" :options="optDona" :height="260"/>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="box box-danger">
					<div class="box-header with-border"><h3 class="box-title">Distribución por gestión</h3></div>
					<div class="box-body">
						<chart-canvas type="doughnut" :data="chGestion" :options="optDona" :height="300"/>
					</div>
				</div>
			</div>
			<div class="col-md-6" v-if="res.por_referencia.length">
				<div class="box box-default">
					<div class="box-header with-border"><h3 class="box-title">Por referencia</h3></div>
					<div class="box-body">
						<chart-canvas type="bar" :data="chReferencia" :options="optBarH" :height="altoBarras(Math.min(res.por_referencia.length, TOP_CLIENTES))"/>
					</div>
				</div>
			</div>
		</div>

		<!-- ============ HEATMAP ============ -->
		<div class="box box-solid" v-if="!soloPropio && res.matriz.abogados.length > 1 && res.matriz.clientes.length > 1">
			<div class="box-header with-border"><h3 class="box-title">Mapa de calor abogado × cliente</h3>
				<small class="text-muted"> — horas; más oscuro = más dedicación</small></div>
			<div class="box-body table-responsive">
				<table class="table table-bordered heat">
					<thead><tr>
						<th></th>
						<th v-for="c in res.matriz.clientes" class="text-center" :title="c">{{ corto(c) }}</th>
					</tr></thead>
					<tbody>
						<tr v-for="(a, i) in res.matriz.abogados">
							<th>{{ a }}</th>
							<td v-for="(v, j) in res.matriz.valores[i]" class="text-center" :style="celda(v)" :title="a + ' → ' + res.matriz.clientes[j] + ': ' + fmt(v) + ' hs'">{{ v ? fmt(v) : '' }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- ============ TABLA DETALLE ============ -->
		<div class="box">
			<div class="box-header with-border">
				<h3 class="box-title">Abogado × cliente (de mayor a menor)</h3>
				<div class="box-tools"><button type="button" class="btn btn-sm btn-success" @click="exportar()"><i class="fa fa-file-excel-o"></i> Exportar a Excel</button></div>
			</div>
			<div class="box-body table-responsive no-padding">
				<table class="table table-hover">
					<thead><tr>
						<th v-if="!soloPropio">Abogado</th><th>Cliente</th>
						<th class="text-center">Horas</th><th class="text-center">% del total</th><th class="text-center">Movimientos</th>
					</tr></thead>
					<tbody>
						<tr v-for="d in res.detalle" :key="d.user_id + '-' + d.cliente_id">
							<td v-if="!soloPropio">{{ d.abogado }}</td><td>{{ d.cliente }}</td>
							<td class="text-center" style="background:#eee;"><strong>{{ fmt(d.minutos) }}</strong></td>
							<td class="text-center">{{ pct(d.minutos) }}%</td>
							<td class="text-center">{{ d.movimientos }}</td>
						</tr>
					</tbody>
					<tfoot><tr>
						<td :colspan="soloPropio ? 1 : 2" class="text-right"><strong>TOTAL</strong></td>
						<td class="text-center" style="background:#ddd;"><strong>{{ fmt(res.totales.minutos) }}</strong></td>
						<td class="text-center">100%</td><td class="text-center">{{ res.totales.movimientos }}</td>
					</tr></tfoot>
				</table>
			</div>
		</div>
		</template>
	</template>
</div>
</template>
<script>
import moment from 'moment'
import { datepicker } from 'vue-strap'
import vSelect from 'vue-select'
import Api from '../../../../api'
import abrirFirmado from '../../../../helpers/abrirFirmado'
import ChartCanvas from '../../../shared/charts/ChartCanvas.vue'

const PALETA = ['#605ca8','#00c0ef','#00a65a','#f39c12','#dd4b39','#3c8dbc','#d81b60','#39cccc','#ff851b','#001f3f','#01ff70','#85144b']
const TOP_CLIENTES = 15

export default {
	name: 'ImpresionesAnalisis',
	components: { datepicker, vSelect, ChartCanvas },
	data() {
		return {
			TOP_CLIENTES,
			canFilter: false,
			loading: false,
			loaded: false,
			res: null,
			rangos: [
				{ key: 'mes', label: 'Este mes' }, { key: 'mes_ant', label: 'Mes anterior' },
				{ key: 'trim', label: 'Trimestre' }, { key: 'anio', label: 'Este año' },
				{ key: 'ult30', label: 'Últimos 30 días' }
			],
			info: { abogados: [], clientes: [], gestiones: [] },
			sel: { abogados: [], clientes: [], gestiones: [] },
			filtros: {
				desde: moment().startOf('month').format('DD/MM/YYYY'),
				hasta: moment().endOf('month').format('DD/MM/YYYY'),
				facturar: '', estado: '', categoria_abogado: '', referencia: ''
			}
		}
	},
	computed: {
		soloPropio() { return false }, // el permiso time-analisis-de-horas:R da acceso a todo el estudio
		clientes() { return _.sortBy(this.info.clientes, o => o.nombre_completo) },
		categorias() { return _.uniq(this.info.abogados.map(a => a.categoria_abogado).filter(Boolean)) },
		hayDrill() { return this.sel.abogados.length > 0 || this.sel.clientes.length > 0 },
		variacion() {
			const t = this.res.totales
			return t.minutos_prev > 0 ? Math.round((t.minutos - t.minutos_prev) * 100 / t.minutos_prev) : null
		},
		pctFacturable() { return this.pct(this.res.totales.facturables) },
		maxCelda() { return Math.max(1, ...this.res.matriz.valores.map(f => Math.max(...f))) },

		chAbogado() {
			const r = this.res.por_abogado
			return this.barData(r.map(x => x.nombre), r.map(x => x.minutos), '#605ca8')
		},
		chCliente() {
			const r = this.res.por_cliente.slice(0, TOP_CLIENTES)
			return this.barData(r.map(x => x.nombre), r.map(x => x.minutos), '#00c0ef')
		},
		chReferencia() {
			const r = this.res.por_referencia.slice(0, TOP_CLIENTES)
			return this.barData(r.map(x => x.nombre), r.map(x => x.minutos), '#39cccc')
		},
		chGestion() {
			const r = this.res.por_gestion
			return { labels: r.map(x => x.nombre), datasets: [{ data: r.map(x => this.hrs(x.minutos)), backgroundColor: r.map((x, i) => PALETA[i % PALETA.length]) }] }
		},
		chFacturable() {
			const t = this.res.totales
			return { labels: ['Facturables', 'No facturables'], datasets: [{ data: [this.hrs(t.facturables), this.hrs(t.no_facturables)], backgroundColor: ['#00a65a', '#d2d6de'] }] }
		},
		chDia() {
			let acum = 0
			const r = this.res.por_dia
			return {
				labels: r.map(x => moment(x.fecha).format('DD/MM')),
				datasets: [
					{ type: 'line', label: 'Acumulado (hs)', data: r.map(x => this.hrs(acum += x.minutos)), borderColor: '#605ca8', backgroundColor: 'transparent', tension: .25, yAxisID: 'y2', pointRadius: 2 },
					{ type: 'bar', label: 'Horas del día', data: r.map(x => this.hrs(x.minutos)), backgroundColor: '#00a65a', yAxisID: 'y' }
				]
			}
		},
		optBarH() {
			return {
				indexAxis: 'y',
				plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => this.fmtH(c.parsed.x) + ' hs' } } },
				scales: { x: { beginAtZero: true, ticks: { callback: v => v + ' h' } } }
			}
		},
		optDia() {
			return {
				plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + this.fmtH(c.parsed.y) } } },
				scales: {
					y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Horas del día' } },
					y2: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Acumulado' } }
				}
			}
		},
		optDona() {
			return {
				plugins: {
					legend: { position: 'bottom' },
					tooltip: { callbacks: { label: c => c.label + ': ' + this.fmtH(c.parsed) + ' hs' } }
				}
			}
		}
	},
	mounted() {
		Promise.all([
			Api.combos('time/abogados').then(r => { this.info.abogados = r.data }),
			Api.combos('time/clientes/1').then(r => { this.info.clientes = r.data }),
			Api.request('GET', 'time-impresiones/analisis/gestiones').then(r => { this.info.gestiones = r.data })
		]).then(() => { this.canFilter = true; this.doFilter() })
		  .catch(() => { this.canFilter = true })
	},
	methods: {
		hrs(min) { return Math.round(min / 60 * 100) / 100 },
		fmt(min) { min = Math.round(min); return Math.floor(min / 60) + ':' + ('0' + (min % 60)).slice(-2) },
		fmtH(h) { return this.fmt(h * 60) },
		pct(min) { return this.res.totales.minutos ? Math.round(min * 1000 / this.res.totales.minutos) / 10 : 0 },
		corto(s) { return s.length > 16 ? s.substring(0, 15) + '…' : s },
		altoBarras(n) { return Math.max(160, n * 30 + 50) },
		barData(labels, minutos, color) {
			return { labels, datasets: [{ data: minutos.map(m => this.hrs(m)), backgroundColor: color }] }
		},
		celda(v) {
			if (!v) return {}
			const a = 0.12 + 0.88 * (v / this.maxCelda)
			return { background: 'rgba(96,92,168,' + a.toFixed(2) + ')', color: a > .55 ? '#fff' : '#333' }
		},
		setRango(k) {
			const m = moment()
			const r = {
				mes: [m.clone().startOf('month'), m.clone().endOf('month')],
				mes_ant: [m.clone().subtract(1, 'month').startOf('month'), m.clone().subtract(1, 'month').endOf('month')],
				trim: [m.clone().startOf('quarter'), m.clone().endOf('quarter')],
				anio: [m.clone().startOf('year'), m.clone().endOf('year')],
				ult30: [m.clone().subtract(29, 'days'), m.clone()]
			}[k]
			this.filtros.desde = r[0].format('DD/MM/YYYY')
			this.filtros.hasta = r[1].format('DD/MM/YYYY')
			this.doFilter()
		},
		drillAbogado(i) {
			if (this.soloPropio) return
			const x = this.res.por_abogado[i]
			this.sel.abogados = this.info.abogados.filter(a => a.id === x.id)
			this.doFilter()
		},
		drillCliente(i) {
			const x = this.res.por_cliente[i]
			this.sel.clientes = this.info.clientes.filter(c => c.id === x.id)
			this.doFilter()
		},
		limpiarDrill() { this.sel.abogados = []; this.sel.clientes = []; this.doFilter() },
		limpiar() {
			this.sel = { abogados: [], clientes: [], gestiones: [] }
			Object.assign(this.filtros, { facturar: '', estado: '', categoria_abogado: '', referencia: '' })
			this.doFilter()
		},
		params() {
			const p = Object.assign({}, this.filtros)
			p.user_ids = this.sel.abogados.map(a => a.id)
			p.cliente_ids = this.sel.clientes.map(c => c.id)
			p.gestion_ids = this.sel.gestiones.map(g => g.id)
			return p
		},
		doFilter() {
			if (!this.filtros.desde || !this.filtros.hasta) return
			this.loading = true
			Api.post('time-impresiones/analisis', this.params()).then(r => {
				this.res = r.data
				this.loaded = true
				this.loading = false
			}, () => { this.loading = false })
		},
		exportar() {
			abrirFirmado('exp-analisis', this.params(), true)
		}
	}
}
</script>
<style scoped>
	.heat th { white-space: nowrap; font-size: 12px; }
	.heat td { min-width: 56px; }
</style>

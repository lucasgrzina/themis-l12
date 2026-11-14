<template>
<modal v-model="show" class="themis-modal alta-rapida" effect="fade" :backdrop="false" large>
  <div slot="modal-header" class="modal-header">
    <h4 class="modal-title">Alta rápida de cliente</h4>
    <ul class="ar-steps" v-if="step <= 4">
      <li v-for="(s, i) in steps" :key="i" :class="{active: step === i + 1, done: step > i + 1}">
        <span class="ar-badge">{{ i + 1 }}</span> {{ s }}
      </li>
    </ul>
  </div>

  <div slot="modal-body" class="modal-body" v-if="show">
    <div class="alert alert-danger" v-if="errores.length">
      <div v-for="(e, i) in errores" :key="i">{{ e }}</div>
    </div>

    <!-- 1. Cliente -->
    <div v-show="step === 1">
      <div class="row">
        <div class="form-group col-sm-12">
          <label class="radio-inline"><input type="radio" value="H" v-model="form.personeria"> Persona humana</label>
          <label class="radio-inline"><input type="radio" value="J" v-model="form.personeria"> Persona jurídica</label>
        </div>
        <div class="form-group col-sm-8">
          <label>{{ form.personeria === 'J' ? 'Razón social' : 'Nombre completo' }}</label>
          <input type="text" class="form-control" v-model="form.nombre_completo" maxlength="150">
        </div>
        <div class="form-group col-sm-4">
          <label>Fecha 1ra entrevista</label>
          <datepicker v-model="form.fecha_entrevista" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
        </div>
      </div>
      <div class="row">
        <div class="form-group col-sm-4">
          <label>CUIT/CUIL</label>
          <input type="text" class="form-control" v-model="form.cuit" v-mask="['##-#######-#', '##-########-#']" :disabled="form.sinCuit">
          <div class="checkbox" style="margin-bottom:0;">
            <label><input type="checkbox" v-model="form.sinCuit" @change="onSinCuit"> No tiene CUIT/CUIL</label>
          </div>
        </div>
        <template v-if="form.sinCuit">
          <div class="form-group col-sm-4">
            <label>Tipo doc.</label>
            <select class="form-control" v-model="form.tipo_doc_id">
              <option :value="null"></option>
              <option v-for="o in info.tipo_doc" :key="o.id" :value="o.id">{{ o.nombre }}</option>
            </select>
          </div>
          <div class="form-group col-sm-4">
            <label>Nro. doc.</label>
            <input type="text" class="form-control" v-model="form.nro_doc" maxlength="20">
          </div>
        </template>
      </div>

      <label>Contactos <small class="text-muted">(al menos un teléfono o e-mail)</small></label>
      <div class="row ar-contacto" v-for="(c, i) in form.contactos" :key="'c' + i">
        <div class="form-group col-sm-3">
          <select class="form-control" v-model="c.kind">
            <option value="T">Teléfono</option>
            <option value="E">E-mail</option>
          </select>
        </div>
        <div class="form-group col-sm-4">
          <input :type="c.kind === 'E' ? 'email' : 'tel'" class="form-control" v-model="c.val" :placeholder="c.kind === 'E' ? 'nombre@dominio.com' : 'Número'">
        </div>
        <div class="form-group col-sm-4">
          <select class="form-control" v-model="c.tipo">
            <option v-for="o in info.tipos_contacto" :key="o.id" :value="o.nombre">{{ o.nombre }}</option>
            <option :value="OTRO">Otro…</option>
          </select>
          <input v-if="c.tipo === OTRO" type="text" class="form-control ar-otro" v-model="c.tipoOtro" placeholder="Descripción" maxlength="60">
        </div>
        <div class="col-sm-1 text-right">
          <button type="button" class="btn btn-sm btn-default" @click="quitarContacto(i)" v-if="form.contactos.length > 1" title="Quitar"><i class="fa fa-trash"></i></button>
        </div>
      </div>
      <button type="button" class="btn btn-sm btn-default" @click="agregarContacto"><i class="fa fa-plus"></i> Agregar contacto</button>
    </div>

    <!-- 2. Observación y aviso -->
    <div v-show="step === 2">
      <div class="form-group">
        <label>Observación inicial <small class="text-muted">(opcional, queda en Movimientos)</small></label>
        <textarea class="form-control" rows="3" v-model="form.observacion"></textarea>
      </div>

      <label>Destinatarios del aviso y del e-mail <small class="text-muted">(opcional; se crea un aviso y un mail por destinatario)</small></label>
      <div class="ar-dest">
        <div class="ar-areas">
          <span class="text-muted">Agregar todos los de un área:</span>
          <button type="button" class="btn btn-xs btn-default" v-for="a in info.areas" :key="a.id" @click="agregarArea(a)">
            <i class="fa fa-plus"></i> {{ a.nombre }}
          </button>
        </div>
        <input type="text" class="form-control input-sm" v-model="filtroUsuarios" placeholder="Buscar usuario…">
        <ul class="ar-lista">
          <li v-for="u in usuariosFiltrados" :key="u.id">
            <label><input type="checkbox" :value="u.id" v-model="form.destinatarios"> {{ u.name }}</label>
          </li>
          <li v-if="usuariosFiltrados.length === 0" class="text-muted">Sin resultados</li>
        </ul>
        <div class="text-muted">
          {{ form.destinatarios.length }} seleccionado(s)
          <a href="#" @click.prevent="form.destinatarios = []" v-if="form.destinatarios.length">· quitar todos</a>
        </div>
      </div>

      <div v-if="form.destinatarios.length" style="margin-top:12px;">
        <div class="row">
          <div class="form-group col-sm-7">
            <label>Motivo del aviso</label>
            <select class="form-control" v-model="form.motivo">
              <option :value="null"></option>
              <option v-for="o in info.motivos" :key="o.id" :value="o.nombre">{{ o.nombre }}</option>
              <option :value="OTRO">Otro…</option>
            </select>
            <input v-if="form.motivo === OTRO" type="text" class="form-control ar-otro" v-model="form.motivoOtro" placeholder="Escriba el motivo">
          </div>
          <div class="form-group col-sm-5">
            <label>Fecha y hora del aviso</label>
            <datetime v-model="form.fecha_aviso"
                      input-class="form-control"
                      type="datetime" zone="America/Buenos_Aires" value-zone="America/Buenos_Aires"
                      :minute-step="30"></datetime>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Adjuntos -->
    <div v-show="step === 3">
      <p class="text-muted">
        Los archivos se guardan en la solapa <strong>Docs</strong> del cliente y se envían adjuntos por e-mail a los destinatarios.
        Máximo {{ limites.max_adjuntos }} archivos y {{ limites.max_adjuntos_mb }} MB en total.
      </p>
      <file-upload
        ref="upload"
        v-model="files"
        :multiple="true"
        :maximum="limites.max_adjuntos"
        :size="limites.max_adjuntos_mb * 1024 * 1024"
        :post-action="urlUploads"
        @input-file="inputFile"
        class="btn btn-primary">
        <i class="fa fa-paperclip"></i> Seleccionar archivos
      </file-upload>

      <table class="table table-striped" style="margin-top:15px;" v-if="adjuntos.length || subiendo.length">
        <thead><tr><th>Archivo</th><th style="width:40%">Tipo de documento</th><th style="width:60px"></th></tr></thead>
        <tbody>
          <tr v-for="f in subiendo" :key="'u' + f.id">
            <td colspan="2">{{ f.name }} <span class="text-muted" v-if="!f.error">— subiendo {{ f.progress }}%</span><span class="text-red" v-else>— {{ f.error }}</span></td>
            <td></td>
          </tr>
          <tr v-for="(a, i) in adjuntos" :key="'a' + i">
            <td>{{ a.nombre_real }} <small class="text-muted">({{ tamanio(a.size) }})</small></td>
            <td>
              <select class="form-control input-sm" v-model="a.tipo">
                <option :value="null">Seleccione…</option>
                <option v-for="o in info.tipos_documento" :key="o.id" :value="o.nombre">{{ o.nombre }}</option>
                <option :value="OTRO">Otro…</option>
              </select>
              <input v-if="a.tipo === OTRO" type="text" class="form-control input-sm ar-otro" v-model="a.tipoOtro" placeholder="Nombre del documento" maxlength="150">
            </td>
            <td class="text-right"><button type="button" class="btn btn-xs btn-default" @click="quitarAdjunto(i)" title="Quitar"><i class="fa fa-trash"></i></button></td>
          </tr>
        </tbody>
      </table>
      <div class="text-muted" v-if="adjuntos.length">Total: {{ tamanio(totalBytes) }}</div>
    </div>

    <!-- 4. Confirmación -->
    <div v-show="step === 4">
      <dl class="dl-horizontal ar-resumen">
        <dt>Cliente</dt><dd>{{ form.nombre_completo }} <span class="label" :class="form.personeria === 'J' ? 'bg-red' : 'bg-yellow'">{{ form.personeria === 'J' ? 'Jurídica' : 'Humana' }}</span></dd>
        <dt>{{ form.sinCuit ? 'Documento' : 'CUIT/CUIL' }}</dt><dd>{{ form.sinCuit ? documentoTexto : form.cuit }}</dd>
        <dt>Entrevista</dt><dd>{{ form.fecha_entrevista }}</dd>
        <dt>Contactos</dt><dd><div v-for="(c, i) in contactosValidos" :key="'r' + i">{{ c.val }} <small class="text-muted">({{ tipoContacto(c) }})</small></div></dd>
        <dt>Observación</dt><dd>{{ form.observacion || '—' }}</dd>
        <dt>Avisos / mails</dt>
        <dd v-if="form.destinatarios.length">{{ form.destinatarios.length }} destinatario(s): {{ nombresDestinatarios }}<br><small class="text-muted">Motivo: {{ motivoTexto }} · {{ form.fecha_aviso | fechaCorta }}</small></dd>
        <dd v-else>— (sin destinatarios: no se crean avisos ni se envían mails)</dd>
        <dt>Documentos</dt><dd>{{ adjuntos.length ? adjuntos.length + ' archivo(s), ' + tamanio(totalBytes) : '—' }}</dd>
      </dl>
      <p class="text-muted" v-if="form.destinatarios.length === 0 && adjuntos.length">Los documentos se guardan en Docs, pero no se enviarán por e-mail porque no hay destinatarios.</p>
    </div>

    <!-- 5. Resultado -->
    <div v-if="step === 5 && resultado">
      <div class="alert alert-success">
        <strong>Cliente creado.</strong> {{ resultado.nombre_completo }} (ID {{ resultado.id }}).
      </div>
      <ul>
        <li>{{ resultado.alta_rapida.avisos }} aviso(s) creado(s).</li>
        <li>{{ resultado.alta_rapida.documentos }} documento(s) guardado(s) en Docs.</li>
        <li v-if="!resultado.alta_rapida.mails.habilitado">Envío de mails deshabilitado en este entorno.</li>
        <li v-else>{{ resultado.alta_rapida.mails.enviados }} mail(s) enviado(s)<span v-if="resultado.alta_rapida.mails.fallidos > 0"> — <strong class="text-red">{{ resultado.alta_rapida.mails.fallidos }} fallaron</strong> (ver log)</span>.</li>
      </ul>
    </div>
  </div>

  <div slot="modal-footer" class="modal-footer">
    <template v-if="step <= 4">
      <button type="button" class="btn btn-default pull-left" @click="cerrar" :disabled="guardando">Cancelar</button>
      <button type="button" class="btn btn-default" v-if="step > 1" @click="anterior" :disabled="guardando">&laquo; Anterior</button>
      <button type="button" class="btn btn-primary" v-if="step < 4" @click="siguiente" :disabled="subiendo.length > 0">Siguiente &raquo;</button>
      <button type="button" class="btn btn-success" v-if="step === 4" @click="confirmar" :disabled="guardando">
        <i class="fa" :class="guardando ? 'fa-spinner fa-spin' : 'fa-check'"></i> Confirmar alta
      </button>
    </template>
    <template v-else>
      <button type="button" class="btn btn-default" @click="cerrar">Cerrar</button>
      <button type="button" class="btn btn-primary" @click="abrirFicha">Abrir ficha para completar</button>
    </template>
  </div>
</modal>
</template>

<script>
import moment from 'moment'
import { modal, datepicker } from 'vue-strap'
import FileUpload from 'vue-upload-component'
import config from '../../../config'
import Api from '../../../api'

const OTRO = '__otro__'
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

export default {
  name: 'AltaRapidaCliente',
  components: { modal, datepicker, FileUpload },
  props: {
    value: { type: Boolean, default: false }
  },
  data () {
    return {
      OTRO,
      steps: ['Cliente', 'Observación y aviso', 'Documentos', 'Confirmar'],
      step: 1,
      info: { tipo_doc: [], areas: [], usuarios: [], motivos: [], tipos_contacto: [], tipos_documento: [] },
      limites: { max_adjuntos: 10, max_adjuntos_mb: 20 },
      urlUploads: config.serverURI.concat('upload/docs'),
      form: this.nuevoForm(),
      files: [],
      adjuntos: [],
      filtroUsuarios: '',
      errores: [],
      guardando: false,
      resultado: null,
      cargado: false
    }
  },
  computed: {
    show: {
      get () { return this.value },
      set (v) { this.$emit('input', v) }
    },
    subiendo () {
      return this.files.filter(f => !f.success)
    },
    totalBytes () {
      return this.adjuntos.reduce((t, a) => t + (a.size || 0), 0)
    },
    usuariosFiltrados () {
      const q = this.filtroUsuarios.trim().toLowerCase()
      return this.info.usuarios.filter(u => !q || u.name.toLowerCase().indexOf(q) !== -1)
    },
    contactosValidos () {
      return this.form.contactos.filter(c => c.val && c.val.trim() !== '')
    },
    nombresDestinatarios () {
      return this.info.usuarios.filter(u => this.form.destinatarios.indexOf(u.id) !== -1).map(u => u.name).join(', ')
    },
    motivoTexto () {
      return this.form.motivo === OTRO ? (this.form.motivoOtro || '').trim() : this.form.motivo
    },
    documentoTexto () {
      const t = this.info.tipo_doc.find(o => o.id === this.form.tipo_doc_id)
      return (t ? t.nombre + ' ' : '') + (this.form.nro_doc || '')
    }
  },
  filters: {
    fechaCorta (v) {
      return v ? moment(v).format('DD/MM/YYYY HH:mm') : ''
    }
  },
  watch: {
    value (open) {
      if (open) {
        this.reset()
        this.cargarCombos()
      }
    }
  },
  methods: {
    nuevoForm () {
      return {
        personeria: 'H',
        nombre_completo: '',
        cuit: '',
        sinCuit: false,
        tipo_doc_id: null,
        nro_doc: '',
        fecha_entrevista: moment().format('DD/MM/YYYY'),
        contactos: [{ kind: 'T', val: '', tipo: null, tipoOtro: '' }],
        observacion: '',
        destinatarios: [],
        motivo: null,
        motivoOtro: '',
        fecha_aviso: moment().toISOString()
      }
    },
    reset () {
      this.step = 1
      this.form = this.nuevoForm()
      this.files = []
      this.adjuntos = []
      this.filtroUsuarios = ''
      this.errores = []
      this.guardando = false
      this.resultado = null
      this.aplicarTipoContactoDefault()
    },
    cargarCombos () {
      if (this.cargado) {
        this.aplicarTipoContactoDefault()
        return
      }
      Api.combos('alta-rapida').then((resp) => {
        this.info = resp.data
        this.limites = resp.data.limites || this.limites
        this.cargado = true
        this.aplicarTipoContactoDefault()
      })
    },
    aplicarTipoContactoDefault () {
      const def = this.info.tipos_contacto.length ? this.info.tipos_contacto[0].nombre : OTRO
      this.form.contactos.forEach(c => { if (!c.tipo) c.tipo = def })
    },
    onSinCuit () {
      if (this.form.sinCuit) {
        this.form.cuit = ''
      } else {
        this.form.tipo_doc_id = null
        this.form.nro_doc = ''
      }
    },
    agregarContacto () {
      const def = this.info.tipos_contacto.length ? this.info.tipos_contacto[0].nombre : OTRO
      this.form.contactos.push({ kind: 'T', val: '', tipo: def, tipoOtro: '' })
    },
    quitarContacto (i) {
      this.form.contactos.splice(i, 1)
    },
    tipoContacto (c) {
      return c.tipo === OTRO ? ((c.tipoOtro || '').trim() || 'Otro') : c.tipo
    },
    agregarArea (area) {
      this.info.usuarios.forEach(u => {
        if (u.area_ids.indexOf(area.id) !== -1 && this.form.destinatarios.indexOf(u.id) === -1) {
          this.form.destinatarios.push(u.id)
        }
      })
    },
    tamanio (bytes) {
      if (!bytes) return '0 KB'
      return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB'
    },
    inputFile (newFile, oldFile) {
      // Subida automática al elegir los archivos
      if (newFile && !oldFile && !this.$refs.upload.active) {
        this.$refs.upload.active = true
      }
      if (newFile && oldFile && newFile.success && !oldFile.success) {
        const total = this.totalBytes + (newFile.size || 0)
        if (this.adjuntos.length >= this.limites.max_adjuntos || total > this.limites.max_adjuntos_mb * 1048576) {
          this.errores = ['No se puede adjuntar "' + newFile.name + '": supera el límite de archivos o de tamaño total.']
        } else {
          this.errores = []
          this.adjuntos.push({
            nombre_archivo: newFile.response.data.doc,
            nombre_real: newFile.name,
            size: newFile.size,
            tipo: null,
            tipoOtro: ''
          })
        }
        this.$nextTick(() => { this.$refs.upload.remove(newFile) })
      }
      if (newFile && newFile.error) {
        this.errores = ['No se pudo subir "' + newFile.name + '" (' + newFile.error + ').']
      }
    },
    quitarAdjunto (i) {
      this.adjuntos.splice(i, 1)
    },
    validarPaso (n) {
      const e = []
      const f = this.form
      if (n === 1) {
        if (!f.nombre_completo.trim()) e.push(f.personeria === 'J' ? 'Ingrese la razón social.' : 'Ingrese el nombre completo.')
        if (!f.sinCuit && !f.cuit) e.push('Ingrese el CUIT/CUIL o marque "No tiene CUIT/CUIL".')
        if (!f.sinCuit && f.cuit && !/^\d{2}-\d{7,8}-\d$/.test(f.cuit)) e.push('El CUIT/CUIL debe tener formato XX-XXXXXXXX-X.')
        if (f.sinCuit && (!f.tipo_doc_id || !f.nro_doc.trim())) e.push('Sin CUIT/CUIL, ingrese tipo y número de documento.')
        if (!f.fecha_entrevista) e.push('Ingrese la fecha de entrevista.')
        if (this.contactosValidos.length === 0) e.push('Ingrese al menos un teléfono o un e-mail.')
        this.contactosValidos.forEach(c => {
          if (c.kind === 'E' && !EMAIL_RE.test(c.val.trim())) e.push('El e-mail "' + c.val + '" no es válido.')
          if (c.tipo === OTRO && !(c.tipoOtro || '').trim()) e.push('Describa el tipo del contacto "' + c.val + '".')
        })
      }
      if (n === 2 && f.destinatarios.length) {
        if (!f.motivo) e.push('Seleccione el motivo del aviso.')
        if (f.motivo === OTRO && !(f.motivoOtro || '').trim()) e.push('Escriba el motivo del aviso.')
        if (!f.fecha_aviso) e.push('Ingrese la fecha del aviso.')
      }
      if (n === 3) {
        this.adjuntos.forEach(a => {
          if (!a.tipo) e.push('Seleccione el tipo de documento de "' + a.nombre_real + '".')
          if (a.tipo === OTRO && !(a.tipoOtro || '').trim()) e.push('Escriba el nombre del documento "' + a.nombre_real + '".')
        })
      }
      this.errores = e
      return e.length === 0
    },
    siguiente () {
      if (this.validarPaso(this.step)) {
        this.step++
      }
    },
    anterior () {
      this.errores = []
      this.step--
    },
    payload () {
      const f = this.form
      const contactos = this.contactosValidos.map(c => ({ kind: c.kind, val: c.val.trim(), desc: this.tipoContacto(c) }))
      return {
        cliente: {
          personeria: f.personeria,
          nombre_completo: f.nombre_completo.trim(),
          cuit: f.sinCuit ? '' : f.cuit,
          tipo_doc_id: f.sinCuit ? f.tipo_doc_id : null,
          nro_doc: f.sinCuit ? f.nro_doc.trim() : '',
          fecha_entrevista: f.fecha_entrevista,
          telefonos: contactos.filter(c => c.kind === 'T').map(c => ({ val: c.val, desc: c.desc })),
          emails: contactos.filter(c => c.kind === 'E').map(c => ({ val: c.val, desc: c.desc }))
        },
        observacion: f.observacion.trim(),
        destinatarios: f.destinatarios,
        aviso: { motivo: this.motivoTexto, fecha: f.fecha_aviso },
        documentos: this.adjuntos.map(a => ({
          nombre: a.tipo === OTRO ? a.tipoOtro.trim() : a.tipo,
          nombre_archivo: a.nombre_archivo,
          nombre_real: a.nombre_real
        }))
      }
    },
    confirmar () {
      for (let n = 1; n <= 3; n++) {
        if (!this.validarPaso(n)) {
          this.step = n
          return
        }
      }
      this.guardando = true
      this.errores = []
      Api.request('POST', 'clientes/alta-rapida', this.payload()).then((resp) => {
        this.guardando = false
        this.resultado = resp.data.data
        this.step = 5
        this.$emit('created', this.resultado)
      }, (err) => {
        this.guardando = false
        this.errores = [(err && err.message) || 'No se pudo completar el alta.']
      })
    },
    cerrar () {
      if (this.guardando) return
      this.show = false
    },
    abrirFicha () {
      const data = this.resultado
      this.show = false
      this.$emit('open', data)
    }
  }
}
</script>

<style>
.alta-rapida .ar-steps { list-style: none; margin: 10px 0 0; padding: 0; display: flex; gap: 6px; flex-wrap: wrap; }
.alta-rapida .ar-steps li { color: #999; font-size: 13px; }
.alta-rapida .ar-steps li.active { color: #0c4b95; font-weight: 600; }
.alta-rapida .ar-steps li.done { color: #00a65a; }
.alta-rapida .ar-steps li:not(:last-child)::after { content: '›'; margin-left: 8px; color: #ccc; }
.alta-rapida .ar-badge { display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center; border-radius: 50%; background: #ddd; color: #fff; font-size: 12px; }
.alta-rapida .ar-steps li.active .ar-badge { background: #0c4b95; }
.alta-rapida .ar-steps li.done .ar-badge { background: #00a65a; }
.alta-rapida .ar-otro { margin-top: 5px; }
.alta-rapida .ar-contacto { margin-bottom: 0; }
.alta-rapida .ar-dest { border: 1px solid #ddd; border-radius: 3px; padding: 10px; }
.alta-rapida .ar-areas { margin-bottom: 8px; }
.alta-rapida .ar-areas .btn { margin: 0 4px 4px 0; }
.alta-rapida .ar-lista { list-style: none; margin: 8px 0; padding: 0; max-height: 180px; overflow-y: auto; columns: 2; }
.alta-rapida .ar-lista label { font-weight: normal; cursor: pointer; }
.alta-rapida .ar-resumen dt { text-align: left; }
@media (max-width: 767px) { .alta-rapida .ar-lista { columns: 1; } }
</style>

import Api from '../api'

/**
 * Abre una impresion/exportacion de Time (ruta web sin sesion) mediante una URL
 * firmada y de corta vida que emite la API (ver TimeImpresionesAPIController@urlFirmada).
 *
 * @param {string} tipo    imp-abogado-resumen | imp-cliente-abogado | imp-abogado-cliente |
 *                         imp-liquidacion | exp-liquidacion | exp-analisis
 * @param {object} params  filtros (se omiten null / '' / undefined)
 * @param {boolean} descarga true para archivos (Excel): no abre ventana nueva
 */
export default function abrirFirmado(tipo, params, descarga = false) {
	// Se abre la ventana ya, en el click, para que el navegador no la bloquee.
	const win = descarga ? null : window.open('', '_blank')

	const limpios = {}
	Object.keys(params || {}).forEach(k => {
		const v = params[k]
		if (v !== null && v !== undefined && v !== '' && k !== 'page') limpios[k] = v
	})

	return Api.post('time-impresiones/url-firmada', Object.assign({ tipo }, limpios)).then(r => {
		const url = new URL(Laravel.webDomain).origin + r.data.url
		if (win) win.location = url
		else window.location.href = url
	}, () => {
		if (win) win.close()
	})
}

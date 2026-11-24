const API = import.meta.env.VITE_API_URL || (import.meta.env.DEV ? '/api' : new URL('api', document.baseURI).pathname.replace(/\/$/, ''))
let csrf = ''

async function pedir(ruta, opciones = {}) {
  let respuesta
  try {
    respuesta = await fetch(`${API}${ruta}`, {
      credentials: 'include',
      ...opciones,
      headers: { ...(opciones.headers || {}), ...(opciones.method && opciones.method !== 'GET' ? { 'X-CSRF-Token': csrf } : {}) }
    })
  } catch {
    throw new Error('No se encuentra la API. Revisá que Apache y MySQL estén iniciados.')
  }

  let datos
  try { datos = await respuesta.json() } catch { throw new Error('La API no devolvió JSON válido.') }
  if (!respuesta.ok) throw new Error(datos.error || 'Ocurrió un error')
  return datos
}

function post(ruta, datos) {
  return pedir(ruta, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) })
}

export async function prepararSesion() {
  csrf = (await pedir('/csrf.php')).csrf
}
export async function verUsuario() { return pedir('/me.php') }
export async function registrar(alias, clave) {
  const usuario = await post('/registro.php', { alias, clave })
  csrf = usuario.csrf
  return usuario
}
export async function ingresar(alias, clave) {
  const usuario = await post('/login.php', { alias, clave })
  csrf = usuario.csrf
  return usuario
}
export async function salir() {
  await post('/logout.php', {})
  csrf = ''
  await prepararSesion()
}

export const crearPartida = nombre_equipo => post('/partidas.php', { nombre_equipo, formacion: '4-3-3' })
export const sortearPlantel = () => pedir('/sorteo.php')
export const traerJugadores = (plantelId, posicion) => pedir(`/jugadores.php?${new URLSearchParams({ plantel_id: plantelId, posicion })}`)
export const traerEquipo = partidaId => pedir(`/equipo.php?partida_id=${partidaId}`)
export const guardarJugador = (partida_id, plantel_jugador_id, slot) => post('/guardar_jugador.php', { partida_id, plantel_jugador_id, slot })
export const quitarJugador = (partida_id, slot) => post('/quitar_jugador.php', { partida_id, slot })
export const traerTorneo = partidaId => pedir(`/torneo.php?partida_id=${partidaId}`)
export const jugarRonda = (partida_id, indice) => post('/jugar_ronda.php', { partida_id, indice })
export const traerRanking = juegoId => pedir(`/ranking.php?juegoId=${juegoId}`)
export const traerPuntajes = () => pedir('/puntajes.php')
export const borrarPuntaje = id => pedir(`/puntajes.php?id=${id}`, { method: 'DELETE' })

export function conectarRanking(juegoId, alCambio, alEstado) {
  let socket
  let reintento
  let cerrado = false
  const url = import.meta.env.VITE_WS_URL || `${location.protocol === 'https:' ? 'wss:' : 'ws:'}//${location.hostname}:4100`

  function conectar() {
    alEstado('Conectando…')
    socket = new WebSocket(url)
    socket.onopen = () => { alEstado('En vivo'); alCambio() }
    socket.onmessage = evento => {
      try {
        const aviso = JSON.parse(evento.data)
        if (aviso.tipo === 'rankingActualizado' && aviso.juegoId === juegoId) alCambio()
      } catch { /* Un aviso ilegible no cambia el ranking. */ }
    }
    socket.onclose = () => {
      if (!cerrado) { alEstado('Reconectando…'); reintento = setTimeout(conectar, 3000) }
    }
    socket.onerror = () => socket.close()
  }
  conectar()
  return () => { cerrado = true; clearTimeout(reintento); socket?.close() }
}

import './style.css'
import { conectarRanking, traerRanking } from './services/api.js'

const juego = document.querySelector('#juego')
const lista = document.querySelector('#lista')
const mensaje = document.querySelector('#mensaje')
const estado = document.querySelector('#estado')
let cerrarConexion = null

async function cargar() {
  const juegoId = Number(juego.value)
  if (!Number.isInteger(juegoId) || juegoId < 1) {
    lista.replaceChildren()
    mensaje.textContent = 'Elegí un ID de juego válido.'
    return
  }
  try {
    const ranking = await traerRanking(juegoId)
    lista.replaceChildren(...ranking.map((fila, indice) => {
      const li = document.createElement('li')
      const nombre = document.createElement('strong')
      const puntos = document.createElement('span')
      nombre.textContent = `${indice + 1}. ${fila.alias}`
      puntos.textContent = `${fila.puntos} pts`
      li.append(nombre, puntos)
      return li
    }))
    mensaje.textContent = ranking.length ? '' : 'Todavía no hay resultados para este juego.'
  } catch (error) {
    lista.replaceChildren()
    mensaje.textContent = error.message
  }
}

function cambiarJuego() {
  cerrarConexion?.()
  cargar()
  const id = Number(juego.value)
  if (Number.isInteger(id) && id > 0) cerrarConexion = conectarRanking(id, cargar, texto => { estado.textContent = texto })
}

juego.addEventListener('change', cambiarJuego)
window.addEventListener('pagehide', () => cerrarConexion?.())
cambiarJuego()

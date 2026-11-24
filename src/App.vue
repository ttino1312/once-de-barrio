<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import {
  borrarPuntaje,
  conectarRanking,
  crearPartida,
  guardarJugador,
  jugarRonda,
  ingresar,
  prepararSesion,
  quitarJugador,
  registrar,
  salir,
  sortearPlantel,
  traerEquipo,
  traerJugadores,
  traerPuntajes,
  traerRanking,
  traerTorneo,
  verUsuario
} from './services/api.js'

const slots = [
  { id: 'ARQ', nombre: 'ARQ', tipo: 'ARQ', lugar: 'arq' },
  { id: 'LD', nombre: 'LD', tipo: 'DEF', lugar: 'ld' },
  { id: 'DFC1', nombre: 'DFC', tipo: 'DEF', lugar: 'dfc1' },
  { id: 'DFC2', nombre: 'DFC', tipo: 'DEF', lugar: 'dfc2' },
  { id: 'LI', nombre: 'LI', tipo: 'DEF', lugar: 'li' },
  { id: 'MC1', nombre: 'MC', tipo: 'MED', lugar: 'mc1' },
  { id: 'MC2', nombre: 'MC', tipo: 'MED', lugar: 'mc2' },
  { id: 'MCO', nombre: 'MCO', tipo: 'MED', lugar: 'mco' },
  { id: 'ED', nombre: 'ED', tipo: 'DEL', lugar: 'ed' },
  { id: 'DC', nombre: 'DC', tipo: 'DEL', lugar: 'dc' },
  { id: 'EI', nombre: 'EI', tipo: 'DEL', lugar: 'ei' }
]

const partidaId = ref(null)
const nombreEquipo = ref('Mi equipo')
const sorteo = ref(null)
const candidatos = ref([])
const equipo = ref([])
const slotActivo = ref(null)
const mensaje = ref('Creá una partida para empezar.')
const cargando = ref(false)
const torneo = ref(null)
const jugando = ref(false)
const usuario = ref(null)
const alias = ref('')
const clave = ref('')
const ranking = ref([])
const puntajesAdmin = ref([])
const estadoVivo = ref('Conectando…')
let desconectar = null

const equipoPorSlot = computed(() => Object.fromEntries(equipo.value.map(j => [j.slot, j])))
const completo = computed(() => equipo.value.length === 11)
const mundialEmpezo = computed(() => (torneo.value?.jugados.length || 0) > 0 || torneo.value?.estado === 'amistoso_anterior')

async function cargarTorneo() {
  torneo.value = partidaId.value ? await traerTorneo(partidaId.value) : null
}

function mostrarError(error) {
  mensaje.value = error instanceof Error ? error.message : 'Ocurrió un error inesperado.'
}

async function cargarRanking() {
  try { ranking.value = await traerRanking(1) } catch (error) { mostrarError(error) }
}

async function autenticar(tipo) {
  try {
    usuario.value = tipo === 'registro' ? await registrar(alias.value.trim(), clave.value) : await ingresar(alias.value.trim(), clave.value)
    clave.value = ''
    mensaje.value = `Hola, ${usuario.value.alias}. Ya podés crear tu equipo.`
    await recuperarPartida()
    if (usuario.value.rol === 'admin') puntajesAdmin.value = await traerPuntajes()
  } catch (error) { mostrarError(error) }
}

async function cerrarSesion() {
  try {
    await salir()
    usuario.value = null
    partidaId.value = null
    equipo.value = []
    torneo.value = null
    puntajesAdmin.value = []
    mensaje.value = 'Sesión cerrada.'
  } catch (error) { mostrarError(error) }
}

async function recuperarPartida() {
  const guardada = Number(localStorage.getItem(`onceDeBarrioPartida:${usuario.value.id}`))
  if (!guardada) return
  try {
    equipo.value = await traerEquipo(guardada)
    partidaId.value = guardada
    await cargarTorneo()
    mensaje.value = 'Se recuperó tu última partida.'
  } catch {
    localStorage.removeItem(`onceDeBarrioPartida:${usuario.value.id}`)
  }
}

async function retirarPuntaje(id) {
  if (!confirm('¿Retirar este resultado del ranking?')) return
  try {
    await borrarPuntaje(id)
    puntajesAdmin.value = await traerPuntajes()
    await cargarRanking()
    mensaje.value = 'Resultado retirado del ranking.'
  } catch (error) { mostrarError(error) }
}

async function nuevaPartida() {
  try {
    const partida = await crearPartida(nombreEquipo.value.trim() || 'Mi equipo')
    partidaId.value = Number(partida.id)
    localStorage.setItem(`onceDeBarrioPartida:${usuario.value.id}`, partidaId.value)
    equipo.value = []
    sorteo.value = null
    candidatos.value = []
    slotActivo.value = null
    await cargarTorneo()
    mensaje.value = 'Partida creada. Elegí un lugar de la cancha y sorteá un plantel.'
  } catch (error) {
    mostrarError(error)
  }
}

function seleccionarSlot(slot) {
  if (!partidaId.value) {
    mensaje.value = 'Primero creá una partida.'
    return
  }
  if (mundialEmpezo.value) {
    mensaje.value = 'El once queda fijo una vez que empieza el Mundial.'
    return
  }

  slotActivo.value = slot
  candidatos.value = []
  mensaje.value = `Lugar elegido: ${slot.nombre}. Ahora sorteá un plantel.`
}

async function sortear() {
  if (!partidaId.value || !slotActivo.value || mundialEmpezo.value) {
    mensaje.value = 'Creá una partida y elegí un lugar primero.'
    return
  }

  try {
    cargando.value = true
    sorteo.value = await sortearPlantel()
    candidatos.value = await traerJugadores(sorteo.value.plantel_id, slotActivo.value.tipo)
    mensaje.value = candidatos.value.length
      ? 'Elegí uno de los jugadores disponibles.'
      : 'Ese plantel no tiene jugadores para este lugar. Volvé a sortear.'
  } catch (error) {
    mostrarError(error)
  } finally {
    cargando.value = false
  }
}

async function elegir(jugador) {
  try {
    await guardarJugador(partidaId.value, jugador.plantel_jugador_id, slotActivo.value.id)
    equipo.value = await traerEquipo(partidaId.value)
    await cargarTorneo()
    candidatos.value = []
    mensaje.value = `${jugador.nombre} ${jugador.apellido} quedó en ${slotActivo.value.nombre}.`
  } catch (error) {
    mostrarError(error)
  }
}

async function sacar(slotId) {
  try {
    await quitarJugador(partidaId.value, slotId)
    equipo.value = await traerEquipo(partidaId.value)
    await cargarTorneo()
    mensaje.value = 'Jugador quitado.'
  } catch (error) {
    mostrarError(error)
  }
}

async function jugarPartido() {
  if (!completo.value) {
    mensaje.value = `Te faltan ${11 - equipo.value.length} jugadores para jugar.`
    return
  }

  if (!torneo.value?.siguiente || jugando.value) return
  try {
    jugando.value = true
    const resultado = await jugarRonda(partidaId.value, torneo.value.siguiente.indice)
    await cargarTorneo()
    if (resultado.termino) {
      await cargarRanking()
      if (usuario.value.rol === 'admin') puntajesAdmin.value = await traerPuntajes()
    }
    mensaje.value = `${resultado.ronda}: ${resultado.goles_favor} - ${resultado.goles_contra} contra ${resultado.rival}. ${resultado.termino ? `Mundial terminado: ${resultado.puntos} puntos para el ranking.` : 'Seguís en carrera.'}`
  } catch (error) {
    mostrarError(error)
    await cargarTorneo().catch(() => {})
  } finally {
    jugando.value = false
  }
}

onMounted(async () => {
  try {
    await prepararSesion()
    try { usuario.value = await verUsuario(); await recuperarPartida() } catch { mensaje.value = 'Registrate o iniciá sesión para jugar.' }
    await cargarRanking()
    if (usuario.value?.rol === 'admin') puntajesAdmin.value = await traerPuntajes()
    desconectar = conectarRanking(1, cargarRanking, estado => { estadoVivo.value = estado })
  } catch (error) { mostrarError(error) }
})
onUnmounted(() => desconectar?.())
</script>

<template>
  <main>
    <header>
      <h1>Once de Barrio</h1>
      <p>Armá tu 4-3-3 con planteles históricos.</p>
      <a class="ayuda-juego" href="./manual.html">Cómo jugar · Manual de usuario →</a>
    </header>
    <section v-if="!usuario" class="acceso">
      <div><h2>Entrá a jugar</h2><p>Usá un alias y una contraseña. Tus resultados quedan guardados con tu cuenta.</p></div>
      <form @submit.prevent="autenticar('login')">
        <label for="alias">Alias</label>
        <input id="alias" v-model="alias" autocomplete="username" maxlength="30" required />
        <label for="clave">Contraseña</label>
        <input id="clave" v-model="clave" type="password" autocomplete="current-password" minlength="8" required />
        <div class="botones-acceso"><button type="submit">Ingresar</button><button type="button" class="boton-claro" @click="autenticar('registro')">Registrarme</button></div>
      </form>
    </section>
    <div v-else class="barra-usuario"><span>Jugando como <strong>{{ usuario.alias }}</strong><small v-if="usuario.rol === 'admin'"> · Admin</small></span><button class="boton-claro" @click="cerrarSesion">Cerrar sesión</button></div>
    <p class="mensaje" role="status" aria-live="polite">{{ mensaje }}</p>
    <template v-if="usuario">
    <section class="inicio">
      <label for="nombre-equipo">Tu equipo</label>
      <input id="nombre-equipo" v-model="nombreEquipo" maxlength="80" placeholder="Nombre del equipo" />
      <button @click="nuevaPartida">Nueva partida</button>
      <span v-if="partidaId">Partida #{{ partidaId }}</span>
    </section>
    <div class="juego">
      <section class="cancha">
        <div class="titulo-cancha"><h2>Tu once titular</h2><span>4–3–3 · {{ equipo.length }} de 11</span></div>
        <div class="slots">
          <button
            v-for="slot in slots"
            :key="slot.id"
            class="slot"
            :class="[slot.lugar, { activo: slotActivo?.id === slot.id, ocupado: equipoPorSlot[slot.id] }]"
            :disabled="mundialEmpezo"
            @click="seleccionarSlot(slot)"
          >
            <svg class="camiseta" viewBox="0 0 64 64" aria-hidden="true">
              <path d="M22 8 9 15 3 29l12 6 5-8v29h24V27l5 8 12-6-6-14-13-7c-2 6-18 6-20 0Z" />
              <text x="32" y="38" text-anchor="middle">{{ equipoPorSlot[slot.id]?.dorsal || '+' }}</text>
            </svg>
            <span>{{ slot.nombre }}</span>
            <small v-if="equipoPorSlot[slot.id]">{{ equipoPorSlot[slot.id].nombre }} {{ equipoPorSlot[slot.id].apellido }}</small>
            <small v-else>Elegir jugador</small>
          </button>
        </div>
        <section v-if="partidaId && torneo" class="mundial" aria-label="Mundial">
          <div class="mundial-encabezado"><div><span class="ceja">Tu camino al título</span><h2>Mundial de 7 partidos</h2></div><span v-if="torneo.valoracion !== null" class="media-once">Media del once {{ torneo.valoracion }}</span></div>
          <p class="mundial-ayuda">Tres partidos de grupo. Con 4 puntos pasás a octavos; después, cada derrota te elimina. Un equipo fuerte tiene más chances, pero no asegura el título.</p>
          <ol class="rondas">
            <li v-for="(ronda, indice) in torneo.rondas" :key="ronda.nombre" :class="{ jugada: torneo.jugados[indice], proxima: torneo.siguiente?.indice === indice }">
              <span class="numero-ronda">{{ indice + 1 }}</span><span class="datos-ronda"><strong>{{ ronda.nombre }}</strong><small>vs. {{ ronda.rival }}</small></span>
              <strong v-if="torneo.jugados[indice]" class="marcador">{{ torneo.jugados[indice].goles_favor }} – {{ torneo.jugados[indice].goles_contra }}</strong><span v-else class="pendiente">{{ torneo.siguiente?.indice === indice ? 'Próximo' : '—' }}</span>
            </li>
          </ol>
          <p v-if="torneo.jugados.length >= 3" class="puntos-grupo">Fase de grupos: {{ torneo.puntosGrupo }} puntos</p>
          <p v-if="torneo.estado === 'campeon'" class="cierre-mundial campeon">🏆 ¡Campeón del mundo! {{ torneo.puntos }} puntos en el ranking.</p>
          <p v-else-if="torneo.estado === 'eliminado'" class="cierre-mundial">Quedaste eliminado. Sumaste {{ torneo.puntos }} puntos al ranking. Podés crear otra partida.</p>
          <p v-else-if="torneo.estado === 'amistoso_anterior'" class="cierre-mundial">Esta partida ya tiene un amistoso guardado. Creá una nueva para jugar el Mundial.</p>
          <button v-else class="secundario" :disabled="!completo || !torneo.siguiente || jugando" @click="jugarPartido">{{ jugando ? 'Jugando...' : !completo ? 'Completá tu once para jugar' : `Jugar ${torneo.siguiente.nombre} vs. ${torneo.siguiente.rival}` }}</button>
        </section>
      </section>
      <aside>
        <h2>Draft</h2>
        <p class="ayuda-draft">{{ slotActivo ? `Buscá un jugador para ${slotActivo.nombre}.` : 'El próximo crack de tu equipo empieza acá.' }}</p>
        <button class="sortear" :disabled="cargando || !slotActivo || mundialEmpezo" @click="sortear">
          {{ cargando ? 'Sorteando...' : 'Sortear plantel' }}
        </button>
        <div v-if="!candidatos.length && !sorteo" class="draft-vacio">
          <svg viewBox="0 0 48 48" aria-hidden="true"><rect x="6" y="6" width="36" height="36" rx="6" /><circle cx="16" cy="16" r="2" /><circle cx="32" cy="32" r="2" /><circle cx="24" cy="24" r="2" /></svg>
          <h3>{{ slotActivo ? 'Probá tu suerte' : 'Elegí una posición' }}</h3>
          <p>{{ slotActivo ? 'Sorteá una selección y un Mundial para descubrir los jugadores disponibles.' : 'Tocá un lugar de la cancha. Después sorteá un plantel y elegí a tu jugador.' }}</p>
        </div>
        <p v-if="sorteo" class="plantel-sorteado"><strong>{{ sorteo.seleccion }}</strong><span>Mundial {{ sorteo.anio }}</span></p>
        <div v-if="candidatos.length" class="candidatos">
          <article v-for="jugador in candidatos" :key="jugador.plantel_jugador_id">
            <span class="valoracion">{{ jugador.valoracion }}</span>
            <div class="datos-jugador"><strong>{{ jugador.nombre }} {{ jugador.apellido }}</strong><br><small>{{ jugador.posicion }} · Dorsal {{ jugador.dorsal }}</small></div>
            <button :disabled="mundialEmpezo" @click="elegir(jugador)">Elegir</button>
          </article>
        </div>
        <div v-if="equipo.length" class="equipo-lista">
          <h3>Mi equipo ({{ equipo.length }}/11)</h3>
          <p v-for="jugador in equipo" :key="jugador.slot">
            <span class="puesto-lista">{{ jugador.slot }}</span>
            <span class="nombre-lista">{{ jugador.nombre }} {{ jugador.apellido }}<small>{{ jugador.seleccion }} · {{ jugador.anio }}</small></span>
            <button :aria-label="`Quitar a ${jugador.nombre}`" :disabled="mundialEmpezo" @click="sacar(jugador.slot)">×</button>
          </p>
        </div>
      </aside>
    </div>
    </template>
    <section class="ranking">
      <div class="ranking-titulo"><div><h2>Ranking</h2><p>Mejor Mundial de cada jugador · {{ estadoVivo }}</p></div><a href="./visor.html" target="_blank" rel="noopener">Abrir visor ↗</a></div>
      <p v-if="!ranking.length" class="ranking-vacio">Todavía no hay resultados. El primero puede ser el tuyo.</p>
      <ol v-else><li v-for="fila in ranking" :key="fila.usuarioId"><strong>{{ fila.alias }}</strong><span>{{ fila.puntos }} pts</span></li></ol>
    </section>
    <section v-if="usuario?.rol === 'admin'" class="admin">
      <h2>Resultados recientes</h2>
      <p>Si ves uno inválido, podés retirarlo del ranking.</p>
      <ul><li v-for="fila in puntajesAdmin" :key="fila.id"><span>{{ fila.alias }} · {{ fila.puntos }} pts</span><button @click="retirarPuntaje(fila.id)">Retirar</button></li></ul>
    </section>
    <footer>Santino De Lucca</footer>
  </main>
</template>

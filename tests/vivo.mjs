import WebSocket from 'ws'

const cliente = new WebSocket('ws://127.0.0.1:4100')
const recibido = new Promise((resolve, reject) => {
  const espera = setTimeout(() => reject(new Error('No llegó el aviso')), 3000)
  cliente.on('message', dato => { clearTimeout(espera); resolve(JSON.parse(dato.toString())) })
  cliente.on('error', reject)
})
await new Promise(resolve => cliente.once('open', resolve))
const respuesta = await fetch('http://127.0.0.1:4101/avisar', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ tipo: 'rankingActualizado', juegoId: 1 })
})
if (respuesta.status !== 204) throw new Error(`Notificación HTTP ${respuesta.status}`)
const aviso = await recibido
if (aviso.tipo !== 'rankingActualizado' || aviso.juegoId !== 1) throw new Error('Aviso incorrecto')
cliente.close()
console.log('WebSocket: OK')

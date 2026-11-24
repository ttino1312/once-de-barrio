import http from 'node:http'
import { WebSocketServer, WebSocket } from 'ws'

const servidor = http.createServer((req, res) => {
  // Solo PHP, desde la misma computadora, puede emitir un aviso.
  if (req.method !== 'POST' || req.url !== '/avisar' || req.socket.remoteAddress !== '127.0.0.1' || req.headers.origin) {
    res.writeHead(403).end()
    return
  }
  let cuerpo = ''
  req.on('data', trozo => {
    cuerpo += trozo
    if (cuerpo.length > 200) req.destroy()
  })
  req.on('end', () => {
    try {
      const aviso = JSON.parse(cuerpo)
      if (aviso.tipo !== 'rankingActualizado' || !Number.isInteger(aviso.juegoId) || aviso.juegoId < 1) throw new Error('Aviso inválido')
      const mensaje = JSON.stringify({ tipo: aviso.tipo, juegoId: aviso.juegoId })
      for (const cliente of conexiones.clients) if (cliente.readyState === WebSocket.OPEN) cliente.send(mensaje)
      res.writeHead(204).end()
    } catch { res.writeHead(400).end() }
  })
})

const conexiones = new WebSocketServer({ port: 4100 })
conexiones.on('connection', cliente => {
  // Los navegadores solo escuchan. Sus mensajes no generan actualizaciones.
  cliente.on('message', () => {})
})
servidor.listen(4101, '127.0.0.1')
console.log('Avisos en ws://localhost:4100')

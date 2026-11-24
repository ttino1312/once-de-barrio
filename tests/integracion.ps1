$ErrorActionPreference = 'Stop'
$api = 'http://127.0.0.1/once-de-barrio-conectado/api'
$sesion = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$alias = 'prueba' + (Get-Random -Minimum 100000 -Maximum 999999)
$adminAlias = 'admin' + (Get-Random -Minimum 100000 -Maximum 999999)
$usuarioId = 0
$adminId = 0
$partidaId = 0

function Pedir($ruta, $metodo = 'GET', $datos = $null, $csrf = '') {
    $opciones = @{ Uri = "$api/$ruta"; Method = $metodo; WebSession = $sesion; SkipHttpErrorCheck = $true }
    if ($null -ne $datos) {
        $opciones.ContentType = 'application/json'
        $opciones.Body = $datos | ConvertTo-Json -Compress
    }
    if ($csrf) { $opciones.Headers = @{ 'X-CSRF-Token' = $csrf } }
    $respuesta = Invoke-WebRequest @opciones
    return @{ Estado = [int] $respuesta.StatusCode; Datos = $respuesta.Content | ConvertFrom-Json; Contenido = $respuesta.Content }
}

function Comprobar($condicion, $mensaje) {
    if (-not $condicion) { throw $mensaje }
}

try {
    $sinSesion = Pedir 'me.php'
    Comprobar ($sinSesion.Estado -eq 401) 'GET me sin sesión debe dar 401'
    $csrf = (Pedir 'csrf.php').Datos.csrf
    $registro = Pedir 'registro.php' 'POST' @{ alias = $alias; clave = 'clave-prueba-2026' } $csrf
    Comprobar ($registro.Estado -eq 201) 'El registro debe devolver 201'
    $usuarioId = [int] $registro.Datos.id
    $csrf = $registro.Datos.csrf
    $me = Pedir 'me.php'
    Comprobar ($me.Datos.alias -eq $alias) 'La sesión debe reconocer al usuario registrado'
    $juegoInexistente = Pedir 'ranking.php?juegoId=999999'
    Comprobar ($juegoInexistente.Estado -eq 404) 'Un juego inexistente debe dar 404'
    $juegoVacio = Pedir 'ranking.php?juegoId=2'
    Comprobar ($juegoVacio.Estado -eq 200 -and $juegoVacio.Contenido.Trim() -eq '[]') 'Un juego sin resultados debe devolver []'
    $sinCsrf = Pedir 'partidas.php' 'POST' @{ nombre_equipo = 'Equipo sin token'; formacion = '4-3-3' }
    Comprobar ($sinCsrf.Estado -eq 403) 'Crear partida sin CSRF debe dar 403'
    $partida = Pedir 'partidas.php' 'POST' @{ nombre_equipo = 'Equipo de prueba'; formacion = '4-3-3' } $csrf
    Comprobar ($partida.Estado -eq 201) 'Crear partida debe dar 201'
    $partidaId = [int] $partida.Datos.id
    $incompleta = Pedir 'jugar_ronda.php' 'POST' @{ partida_id = $partidaId; indice = 0 } $csrf
    Comprobar ($incompleta.Estado -eq 400) 'No se juega sin once jugadores'
    $golFalso = Pedir 'puntajes.php' 'POST' @{ juegoId = 1; partidaId = $partidaId; golesFavor = 4; golesContra = 2 } $csrf
    Comprobar ($golFalso.Estado -eq 410) 'El navegador no puede cargar goles inventados'
    $sinPermiso = Pedir 'puntajes.php?id=1' 'DELETE' $null $csrf
    Comprobar ($sinPermiso.Estado -eq 403) 'Un jugador no puede retirar resultados'
    $mysql = 'C:\xampp\mysql\bin\mysql.exe'
    $insert = "INSERT INTO equipo_partida (partida_id, plantel_jugador_id, slot) VALUES ($partidaId,1,'ARQ'),($partidaId,2,'LD'),($partidaId,3,'DFC1'),($partidaId,4,'DFC2'),($partidaId,5,'LI'),($partidaId,6,'MC1'),($partidaId,7,'MC2'),($partidaId,8,'MCO'),($partidaId,9,'ED'),($partidaId,10,'DC'),($partidaId,11,'EI')"
    & $mysql -u root -D once_de_barrio_conectado -e $insert
    Comprobar ($LASTEXITCODE -eq 0) 'No se pudo preparar el once de prueba'
    $torneo = Pedir "torneo.php?partida_id=$partidaId"
    Comprobar ($torneo.Estado -eq 200 -and $torneo.Datos.siguiente.indice -eq 0) 'El Mundial debe arrancar por Grupo 1'
    $guardado = Pedir 'jugar_ronda.php' 'POST' @{ partida_id = $partidaId; indice = 0; goles_favor = 99; usuarioId = 999999 } $csrf
    Comprobar ($guardado.Estado -eq 201 -and $guardado.Datos.ronda -eq 'Grupo 1' -and $guardado.Datos.goles_favor -le 3) 'El servidor calcula el resultado e ignora goles e identidad falsos'
    $repetido = Pedir 'jugar_ronda.php' 'POST' @{ partida_id = $partidaId; indice = 0 } $csrf
    Comprobar ($repetido.Estado -eq 409) 'No debe repetirse la misma ronda'
    $bloqueado = Pedir 'quitar_jugador.php' 'POST' @{ partida_id = $partidaId; slot = 'ARQ' } $csrf
    Comprobar ($bloqueado.Estado -eq 409) 'El once queda fijo después del primer partido'
    $torneo = Pedir "torneo.php?partida_id=$partidaId"
    while ($null -ne $torneo.Datos.siguiente) {
        $guardado = Pedir 'jugar_ronda.php' 'POST' @{ partida_id = $partidaId; indice = [int] $torneo.Datos.siguiente.indice } $csrf
        Comprobar ($guardado.Estado -eq 201) 'Cada partido debe guardarse'
        $torneo = Pedir "torneo.php?partida_id=$partidaId"
    }
    Comprobar ($torneo.Datos.estado -in @('campeon', 'eliminado') -and $torneo.Datos.jugados.Count -ge 3 -and $torneo.Datos.jugados.Count -le 7) 'El Mundial debe terminar entre el tercer y séptimo partido'
    Comprobar ($null -ne $torneo.Datos.puntos) 'El puntaje final debe quedar guardado'
    $ranking = Pedir 'ranking.php?juegoId=1'
    Comprobar (@($ranking.Datos | Where-Object { $_.usuarioId -eq $usuarioId -and $_.puntos -eq $torneo.Datos.puntos }).Count -eq 1) 'El ranking debe incluir el Mundial terminado'
    $finalRepetido = Pedir 'jugar_ronda.php' 'POST' @{ partida_id = $partidaId; indice = 6 } $csrf
    Comprobar ($finalRepetido.Estado -eq 409) 'No debe jugarse después de terminar'
    $idPuntaje = & $mysql -u root -N -D once_de_barrio_conectado -e "SELECT id FROM puntajes WHERE partida_id=$partidaId"
    $salida = Pedir 'logout.php' 'POST' @{} $csrf
    Comprobar ($salida.Estado -eq 200) 'Cerrar sesión debe funcionar'
    $csrf = (Pedir 'csrf.php').Datos.csrf
    $admin = Pedir 'registro.php' 'POST' @{ alias = $adminAlias; clave = 'clave-admin-2026' } $csrf
    Comprobar ($admin.Estado -eq 201) 'La segunda cuenta debe registrarse'
    $adminId = [int] $admin.Datos.id
    & $mysql -u root -D once_de_barrio_conectado -e "UPDATE usuarios SET rol='admin' WHERE id=$adminId"
    Comprobar ($LASTEXITCODE -eq 0) 'No se pudo asignar admin desde el servidor'
    $salida = Pedir 'logout.php' 'POST' @{} $admin.Datos.csrf
    $csrf = (Pedir 'csrf.php').Datos.csrf
    $login = Pedir 'login.php' 'POST' @{ alias = $adminAlias; clave = 'clave-admin-2026' } $csrf
    Comprobar ($login.Estado -eq 200 -and $login.Datos.rol -eq 'admin') 'El administrador debe volver a iniciar sesión'
    $retirado = Pedir "puntajes.php?id=$idPuntaje" 'DELETE' $null $login.Datos.csrf
    Comprobar ($retirado.Estado -eq 200) 'El administrador debe poder retirar el resultado'
    $ranking = Pedir 'ranking.php?juegoId=1'
    Comprobar (@($ranking.Datos | Where-Object { $_.usuarioId -eq $usuarioId }).Count -eq 0) 'El resultado retirado no debe aparecer en el ranking'
    Write-Output 'Integración: OK'
} finally {
    if ($usuarioId) {
        $mysql = 'C:\xampp\mysql\bin\mysql.exe'
        & $mysql -u root -D once_de_barrio_conectado -e "DELETE FROM puntajes WHERE usuario_id=$usuarioId; DELETE FROM resultados WHERE partida_id=$partidaId; DELETE FROM equipo_partida WHERE partida_id=$partidaId; DELETE FROM partidas WHERE usuario_id=$usuarioId; DELETE FROM usuarios WHERE id=$usuarioId"
    }
    if ($adminId) {
        & $mysql -u root -D once_de_barrio_conectado -e "DELETE FROM usuarios WHERE id=$adminId"
    }
}

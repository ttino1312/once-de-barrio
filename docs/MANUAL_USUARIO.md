# Manual de usuario - Once de Barrio

Guía para empezar sin conocimientos técnicos.

## Qué es Once de Barrio

Es un juego de fútbol en el que armás un equipo con jugadores de selecciones históricas y lo llevás a un Mundial de hasta siete partidos. Elegís los once titulares; el sistema simula los resultados. No tenés que mover futbolistas con el teclado.

El objetivo es llegar a la final, ganar el título y conseguir el mejor puntaje posible. El resultado de cada campaña terminada queda asociado a tu cuenta y puede aparecer en el ranking.

Draft significa elegir jugadores para formar tu equipo. Un plantel es el grupo de jugadores de una selección en un Mundial, por ejemplo Argentina 2022. La valoración es el número que representa la calidad de un jugador. Un equipo fuerte tiene más posibilidades de ganar, pero también interviene el azar.

## Entrar por primera vez

Abrí el juego desde el enlace que te dé quien lo instaló. En la computadora que funciona como servidor se usa http://127.0.0.1/once-de-barrio-conectado/. No abras index.html con doble clic: el juego necesita el servidor y la base de datos funcionando.

En «Entrá a jugar», escribí un alias y una contraseña. El alias debe tener entre 3 y 30 caracteres: letras, números o guion bajo, sin espacios. Usá una contraseña de entre 8 y 72 caracteres; con caracteres especiales puede alcanzarse antes el límite que controla el servidor.

Si es tu primera vez, presioná «Registrarme». Si ya tenés cuenta, usá el mismo alias y contraseña y presioná «Ingresar». Si el alias ya existe, elegí otro o ingresá con tu cuenta. No se puede elegir ser administrador desde este formulario.

## Crear tu equipo paso a paso

1. Después de ingresar, escribí un nombre en «Tu equipo» y presioná «Nueva partida». Aparece el número de la partida y una cancha vacía.

2. Tocá una camiseta vacía de la cancha. Por ejemplo, ARQ es el arquero. El lugar seleccionado queda marcado.

3. Presioná «Sortear plantel» en el panel «Draft». Se muestra una selección y un año de Mundial, junto con los jugadores disponibles para esa posición.

4. Compará los nombres y sus valoraciones. Presioná «Elegir» en el jugador que quieras incorporar. Su nombre aparece en la cancha y en «Mi equipo».

5. Repetí la selección de posición, el sorteo y la elección hasta que el contador diga «11 de 11». Si un jugador ya fue elegido o el lugar está ocupado, el sistema avisa el problema.

6. Antes de jugar el primer partido podés quitar un jugador con la × de «Mi equipo» y elegir otro para ese lugar. Para reemplazarlo, quitá primero el que ocupa esa posición. Si el sorteo no trae candidatos para un lugar, volvé a sortear.

La formación es fija: un arquero, cuatro defensores, tres mediocampistas y tres delanteros. No hace falta conocer todas las siglas: seguí las camisetas y elegí entre los candidatos que ofrece el juego.

## Cómo leer las posiciones

ARQ: arquero. LD y LI: laterales derecho e izquierdo. DFC: defensores centrales; hay dos lugares.

MC: mediocampistas centrales; hay dos lugares. MCO: mediocampista ofensivo. ED y EI: extremos derecho e izquierdo. DC: delantero centro.

La «Media del once» resume la valoración de tus once titulares. Es una referencia para las probabilidades de los partidos, no una garantía de victoria.

## Jugar el Mundial

Cuando estén los once jugadores, bajá hasta «Mundial de 7 partidos» y presioná el botón «Jugar» de la próxima ronda. El resultado se calcula y se guarda en el servidor. Esperá el marcador y el mensaje antes de seguir.

Primero se juegan Grupo 1, Grupo 2 y Grupo 3. Cada victoria suma 3 puntos y cada empate 1. Con 4 puntos o más en esos tres partidos pasás a octavos. Si no llegás, termina la campaña.

Después vienen octavos, cuartos, semifinal y final. En estas rondas el sistema resuelve un ganador: si perdés, quedás eliminado. Si ganás la final, sos campeón.

Una vez jugado el primer partido, el once queda fijo. Ya no se pueden agregar ni quitar futbolistas. El botón para jugar queda deshabilitado si faltan jugadores o si ya terminó el Mundial.

Al terminar la campaña, el ranking recibe 3 puntos por cada victoria, 1 por cada empate y 10 puntos extra si saliste campeón. Ejemplo: siete victorias dan 21 puntos, más 10 por el título: 31 puntos. Los goles no suman puntos adicionales en este modo.

Podés crear otra partida para volver a intentarlo. El ranking conserva tu mejor campaña; no suma todas tus partidas. Crear una nueva partida cambia cuál se recupera automáticamente como tu última partida.

## Ver el ranking en vivo

El ranking muestra el alias y el mejor puntaje de cada jugador, de mayor a menor. Si dos personas tienen el mismo puntaje, queda primero quien lo consiguió antes. Una campaña todavía en curso no entra al ranking.

Presioná «Abrir visor» para ver solo la clasificación en otra pestaña. No hace falta iniciar sesión para consultar esta pantalla. Dejala abierta mientras otra persona termina una campaña: debería actualizarse sin presionar F5.

El visor permite cambiar el «ID del juego». El 1 corresponde a Once de Barrio. El 2 es un juego de prueba sin resultados. No mezcla puntos entre juegos. Un juego existente sin partidas muestra un mensaje de ranking vacío; un ID que no existe muestra un error.

«En vivo» significa que está conectado al servicio de avisos. «Reconectando…» indica que se perdió esa conexión: espera e intenta volver. Cuando se recupera, consulta otra vez el ranking. Los resultados guardados siguen en la base aunque se corte el aviso.

## Salir y continuar después

Usá «Cerrar sesión» cuando termines, especialmente si compartís la computadora. Para volver, ingresá con la misma cuenta.

En el mismo navegador, el juego intenta recuperar la última partida de tu cuenta. Los jugadores y resultados están en la base del servidor; el navegador conserva solamente una referencia para encontrar esa última partida.

Si usás otro navegador, cambiás de equipo o borrás los datos del navegador, esa referencia puede no estar. Tu puntaje guardado sigue en el ranking, pero esta versión no ofrece una lista para recuperar todas las partidas anteriores.

## Si algo no funciona

No se encuentra la API / no conecta a la base: pedile a quien instaló el juego que compruebe Apache y MySQL en XAMPP. Si el juego está en otra computadora, usá su dirección de red; 127.0.0.1 siempre apunta a la computadora desde la que navegás.

No puedo jugar: comprobá que diga «11 de 11», que haya una próxima ronda y que la campaña no haya terminado. Si ya empezó el Mundial, no se puede cambiar el equipo.

La sesión venció: recargá la página e ingresá de nuevo si te lo pide. No des por guardado un resultado si aparece un error; comprobá el estado de la partida y el ranking.

Mi último resultado no aparece: la campaña tiene que haber terminado. Si fue peor que tu récord, el ranking sigue mostrando el mejor. Si el visor está reconectando, esperá o recargalo.

Olvidé mi contraseña: esta versión no tiene recuperación automática. Consultá a quien administra la instalación. No compartas tu contraseña ni la muestres en el video.

## Uso del administrador

El administrador entra con una cuenta cuyo permiso ya fue asignado en el servidor. Además de jugar, ve «Resultados recientes». Para quitar un puntaje inválido, presiona «Retirar» y confirma.

La operación retira ese puntaje. Si la persona tiene otra campaña válida, puede aparecer ese otro puntaje como su mejor resultado. Los jugadores comunes no pueden hacer esta operación.

El rol se configura por fuera del formulario público. Las instrucciones de instalación y de asignación del permiso están en el README del proyecto.

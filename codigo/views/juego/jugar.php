<?php
use yii\helpers\Html;
use yii\helpers\Url;

// Detección del tipo de juego
$esSlot = ($model->tipo === 'Slot');
$esRuleta = ($model->tipo === 'Ruleta');
$esCartas = ($model->tipo === 'Cartas');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($model->nombre) ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <style>
        body {
            background-color: #1a1a1a;
            color: white;
            font-family: 'Arial', sans-serif;
            height: 100vh; display: flex; flex-direction: column; overflow: hidden;
        }
        .top-bar {
            background: #000; padding: 5px 20px; height: 50px; flex-shrink: 0;
            display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #d4af37;
        }

        /* --- ESTILOS COMUNES --- */
        .panel-apuesta {
            background: rgba(0,0,0,0.8); padding: 10px 15px; border-radius: 10px; color: gold;
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;
        }
        .input-dinero {
            background: transparent; border: none; border-bottom: 2px solid gold; color: white;
            font-size: 1.3rem; width: 90px; text-align: center;
        }

        /* --- ESTILOS CARTAS / BLACKJACK --- */
        .mesa-blackjack {
            flex-grow: 1; background-color: #0e6b38; /* Tapete verde */
            display: flex; flex-direction: column; justify-content: space-between; padding: 20px;
            border-top: 5px solid #4a2c0f; /* Borde madera */
        }
        .zona-mano { text-align: center; min-height: 150px; }
        .titulo-mano { font-size: 0.9rem; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; }
        .contador-puntos { background: #000; color: gold; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; margin-left: 10px; }

        .cartas-container { display: flex; justify-content: center; gap: 10px; }
        
        /* DISEÑO DE LA CARTA CSS */
        .carta {
            background: white; width: 70px; height: 100px; border-radius: 5px;
            color: #333; font-weight: bold; font-family: 'Courier New', monospace;
            position: relative; display: flex; justify-content: center; align-items: center;
            font-size: 1.5rem; box-shadow: 2px 2px 5px rgba(0,0,0,0.5);
            transition: transform 0.3s;
        }
        .carta.rojo { color: #d63031; }
        .carta.negro { color: #2d3436; }
        .carta:hover { transform: translateY(-10px); }
        .carta-palo-top { position: absolute; top: 2px; left: 5px; font-size: 0.8rem; }
        .carta-palo-bot { position: absolute; bottom: 2px; right: 5px; font-size: 0.8rem; transform: rotate(180deg); }

        .controles-bj { display: flex; justify-content: center; gap: 20px; padding-bottom: 20px; }

        /* Estilos Slot y Ruleta (Resumidos para no ocupar) */
        .ruleta-container { display: flex; flex-grow: 1; overflow: hidden; }
        .zona-rueda { width: 40%; display: flex; justify-content: center; align-items: center; background: radial-gradient(circle, #023020 0%, #000 100%); }
        .zona-tablero { width: 60%; padding: 10px 20px 40px 20px; overflow-y: auto; background-color: #0e6b38; }
        .slot-machine-container { flex-grow: 1; display: flex; justify-content: center; align-items: center; background: radial-gradient(circle, #2c3e50 0%, #000 100%); }
        .slot-machine { background: #333; border: 8px solid #d4af37; border-radius: 20px; padding: 30px; text-align: center; width: 90%; max-width: 600px; }
        .reel-container { display: flex; justify-content: space-around; background: #fff; padding: 10px; border-radius: 10px; margin-bottom: 20px; }
        .reel { font-size: 4rem; width: 80px; height: 100px; display: flex; align-items: center; justify-content: center; border-right: 2px solid #ddd; color: #333; }
        .tablero-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; margin-bottom: 20px; }
        .casilla-numero { padding: 10px; text-align: center; font-weight: bold; font-size: 1.1rem; cursor: pointer; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; transition: 0.2s; }
        .bg-red { background-color: #d63031; color: white; }
        .bg-black { background-color: #2d3436; color: white; }
        .bg-green { background-color: #00b894; color: white; grid-column: span 3; }
        .apuestas-externas { display: flex; gap: 5px; margin-bottom: 10px; }
        .btn-apuesta { flex: 1; padding: 8px; font-weight: bold; text-transform: uppercase; border: 1px solid white; font-size: 0.9rem; }
    </style>
</head>
<body>

    <div class="top-bar">
        <div>
            <h4 style="margin:0; color: #d4af37;"><?= Html::encode($model->nombre) ?></h4>
            <small class="text-muted">Saldo: <span id="saldo-global"><?= number_format($saldo, 2) ?></span> €</small>
        </div>
        <a href="<?= Url::to(['juego/lobby']) ?>" class="btn btn-outline-light btn-sm">❌ Salir</a>
    </div>

    <?php if ($esCartas): ?>
    <div class="mesa-blackjack">
        
        <div class="zona-mano">
            <div class="titulo-mano">Croupier <span id="puntos-dealer" class="contador-puntos">0</span></div>
            <div class="cartas-container" id="mano-dealer">
                <div class="carta" style="background:#ddd; color:#ddd;">?</div>
            </div>
        </div>

        <div class="text-center">
            <h2 id="mensaje-bj" class="text-white" style="text-shadow: 2px 2px 4px #000;">Haz tu apuesta para empezar</h2>
        </div>

        <div class="zona-mano">
            <div class="cartas-container" id="mano-jugador"></div>
            <div class="titulo-mano mt-2">Tu Mano <span id="puntos-jugador" class="contador-puntos">0</span></div>
        </div>

        <div class="controles-bj">
            <div id="panel-inicio">
                <input type="number" id="apuesta-bj" class="input-dinero" value="10.00" style="background: rgba(0,0,0,0.5); border-radius: 5px;">
                <button class="btn btn-warning btn-lg font-weight-bold ml-2" onclick="iniciarBJ()">REPARTIR</button>
            </div>
            
            <div id="panel-juego" style="display: none;">
                <button class="btn btn-success btn-lg mr-2" onclick="pedirCarta()">PEDIR (+)</button>
                <button class="btn btn-danger btn-lg" onclick="plantarse()">PLANTARSE (✋)</button>
            </div>
            
            <div id="panel-reinicio" style="display: none;">
                <button class="btn btn-light btn-lg" onclick="reiniciarInterfaz()">JUGAR OTRA VEZ</button>
            </div>
        </div>
    </div>

    <script>
        // Función para pintar una carta HTML
        function crearCartaHTML(c) {
            let color = (c.palo === '♥' || c.palo === '♦') ? 'rojo' : 'negro';
            return `<div class="carta ${color}">
                        <span class="carta-palo-top">${c.palo}</span>
                        ${c.valor}
                        <span class="carta-palo-bot">${c.palo}</span>
                    </div>`;
        }

        function renderMano(divId, cartas) {
            let html = '';
            cartas.forEach(c => { html += crearCartaHTML(c); });
            $('#' + divId).html(html);
        }

        // 1. INICIAR
        function iniciarBJ() {
            let apuesta = $('#apuesta-bj').val();
            
            $.post('<?= Url::to(['juego/api-blackjack-iniciar', 'id' => $model->id]) ?>', 
                { cantidadApuesta: apuesta, _csrf: '<?= Yii::$app->request->getCsrfToken() ?>' }, 
                function(res) {
                    if(res.success) {
                        $('#panel-inicio').hide();
                        $('#panel-juego').show();
                        $('#mensaje-bj').text('Tu turno...');
                        $('#saldo-global').text(parseFloat(res.nuevoSaldo).toFixed(2));
                        
                        renderMano('mano-jugador', res.manoJugador);
                        renderMano('mano-dealer', res.manoDealer);
                        $('#puntos-jugador').text(res.puntosJugador);
                        $('#puntos-dealer').text('?');
                    } else {
                        alert(res.mensaje);
                    }
            });
        }

        // 2. PEDIR CARTA
        function pedirCarta() {
            $.post('<?= Url::to(['juego/api-blackjack-pedir']) ?>', 
                { _csrf: '<?= Yii::$app->request->getCsrfToken() ?>' }, 
                function(res) {
                    if(res.success) {
                        renderMano('mano-jugador', res.manoJugador);
                        $('#puntos-jugador').text(res.puntosJugador);
                        
                        if(res.terminado) {
                            $('#panel-juego').hide();
                            $('#panel-reinicio').show();
                            $('#mensaje-bj').html('<span class="text-danger">' + res.mensaje + '</span>');
                        }
                    }
            });
        }

        // 3. PLANTARSE
        function plantarse() {
            $.post('<?= Url::to(['juego/api-blackjack-plantarse']) ?>', 
                { _csrf: '<?= Yii::$app->request->getCsrfToken() ?>' }, 
                function(res) {
                    if(res.success) {
                        renderMano('mano-dealer', res.manoDealer);
                        $('#puntos-dealer').text(res.puntosDealer);
                        $('#saldo-global').text(parseFloat(res.nuevoSaldo).toFixed(2));
                        
                        $('#panel-juego').hide();
                        $('#panel-reinicio').show();

                        let colorMsg = res.victoria ? 'text-success' : 'text-danger';
                        $('#mensaje-bj').html('<span class="'+colorMsg+'">' + res.mensaje + '</span>');
                    }
            });
        }

        function reiniciarInterfaz() {
            $('#panel-reinicio').hide();
            $('#panel-inicio').show();
            $('#mano-jugador').html('');
            $('#mano-dealer').html('<div class="carta" style="background:#ddd; color:#ddd;">?</div>');
            $('#mensaje-bj').text('Haz tu apuesta para empezar');
            $('#puntos-jugador').text('0');
            $('#puntos-dealer').text('0');
        }
    </script>
    
    <?php elseif ($esSlot): ?>
    <div class="slot-machine-container">
        <div class="slot-machine">
            <div class="reel-container">
                <div class="reel" id="reel1">🍒</div>
                <div class="reel" id="reel2">🍒</div>
                <div class="reel" id="reel3">🍒</div>
            </div>
            <div id="mensaje-slot" class="text-warning h4">¡Suerte!</div>
            <hr>
            <button id="btn-girar-slot" class="btn btn-warning btn-lg px-5 font-weight-bold">GIRAR (1.00 €)</button>
        </div>
    </div>
    <script>
        $('#btn-girar-slot').click(function() {
            let btn = $(this); btn.addClass('disabled').text('GIRANDO...'); $('.reel').addClass('blur').text('❓');
            $.ajax({
                url: '<?= Url::to(['juego/api-girar-slot', 'id' => $model->id]) ?>', type: 'POST', data: { cantidadApuesta: 1.00, _csrf: '<?= Yii::$app->request->getCsrfToken() ?>' },
                success: function(res) {
                    setTimeout(function() {
                        $('.reel').removeClass('blur'); btn.removeClass('disabled').text('GIRAR (1.00 €)');
                        if(res.success) {
                            $('#reel1').text(res.rodillos[0]); $('#reel2').text(res.rodillos[1]); $('#reel3').text(res.rodillos[2]);
                            $('#saldo-global').text(parseFloat(res.nuevoSaldo).toFixed(2));
                            if(res.esVictoria) $('#mensaje-slot').html('<span class="text-success">¡GANASTE ' + res.premio + '€!</span>');
                            else $('#mensaje-slot').text('Inténtalo de nuevo...');
                        } else { alert(res.mensaje); }
                    }, 1000);
                }
            });
        });
    </script>

    <?php elseif ($esRuleta): ?>
    <div class="ruleta-container">
        <div class="zona-rueda">
            <div id="rueda-visual" style="font-size: 15rem; cursor: default;">🎡</div>
            <div id="resultado-overlay" style="position: absolute; font-size: 5rem; font-weight: bold; text-shadow: 0 0 10px black; display: none;">0</div>
        </div>
        <div class="zona-tablero">
            <div class="panel-apuesta mb-4">
                <div><label>TU APUESTA (€):</label><input type="number" id="cantidad-apuesta" class="input-dinero" value="1.00" min="0.10" step="0.50"></div>
                <div id="mensaje-ruleta" class="text-warning font-weight-bold">Selecciona una ficha...</div>
            </div>
            <div class="apuestas-externas">
                <button class="btn btn-danger btn-apuesta" onclick="apostar('color', 'rojo')">🔴 ROJO (x2)</button>
                <button class="btn btn-dark btn-apuesta" onclick="apostar('color', 'negro')">⚫ NEGRO (x2)</button>
            </div>
            <div class="apuestas-externas">
                <button class="btn btn-light btn-apuesta text-dark" onclick="apostar('paridad', 'par')">PARES (x2)</button>
                <button class="btn btn-light btn-apuesta text-dark" onclick="apostar('paridad', 'impar')">IMPARES (x2)</button>
            </div>
            <hr class="border-light">
            <div class="tablero-grid">
                <div class="casilla-numero bg-green" onclick="apostar('numero', 0)">0</div>
                <?php 
                $rojos = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
                for ($i = 1; $i <= 36; $i++) {
                    $claseColor = in_array($i, $rojos) ? 'bg-red' : 'bg-black';
                    echo "<div class='casilla-numero $claseColor' onclick='apostar(\"numero\", $i)'>$i</div>";
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        let girando = false;
        function apostar(tipo, valor) {
            if (girando) return;
            let cantidad = parseFloat($('#cantidad-apuesta').val());
            if (isNaN(cantidad) || cantidad <= 0) { alert("Introduce una cantidad válida."); return; }
            girando = true; $('#mensaje-ruleta').text('Girando... ¡No va más!'); $('#rueda-visual').css('transform', 'rotate(720deg)'); $('#resultado-overlay').hide();
            $.ajax({
                url: '<?= Url::to(['juego/api-girar-ruleta', 'id' => $model->id]) ?>', type: 'POST',
                data: { tipoApuesta: tipo, valorApuesta: valor, cantidadApuesta: cantidad, _csrf: '<?= Yii::$app->request->getCsrfToken() ?>' },
                success: function(res) {
                    setTimeout(function() { 
                        girando = false; $('#rueda-visual').css('transform', 'rotate(0deg)');
                        if (res.success) {
                            let colorTexto = (res.color === 'rojo') ? 'red' : (res.color === 'negro' ? 'gray' : 'green');
                            $('#resultado-overlay').text(res.numero).css('color', colorTexto).fadeIn();
                            $('#saldo-global').text(parseFloat(res.nuevoSaldo).toFixed(2));
                            if (res.esVictoria) $('#mensaje-ruleta').html('<span class="text-success" style="font-size:1.2rem">🎉 ¡GANAS ' + res.premio + '€! 🎉</span>');
                            else $('#mensaje-ruleta').html('<span class="text-white">Salió el ' + res.numero + ' (' + res.color + '). Pierdes.</span>');
                        } else { alert(res.mensaje); $('#mensaje-ruleta').text('Error en la apuesta.'); }
                    }, 1000);
                }, error: function() { girando = false; alert("Error de conexión"); }
            });
        }
    </script>
    <?php endif; ?>

</body>
</html>
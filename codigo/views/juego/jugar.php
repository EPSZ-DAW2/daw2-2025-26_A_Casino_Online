<?php
use yii\helpers\Html;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jugando a: <?= Html::encode($model->nombre) ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #1a1a1a; /* Fondo oscuro casino */
            color: white;
            height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            overflow: hidden; /* Evitar scroll */
        }
        .header-juego {
            background: #000;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
        }
        .area-juego {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: radial-gradient(circle, #2c3e50 0%, #000000 100%);
        }
        .iframe-simulado {
            width: 80%;
            height: 80%;
            background: white;
            border: 5px solid #d4af37; /* Borde dorado */
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
            box-shadow: 0 0 50px rgba(255, 215, 0, 0.3);
        }
        .barra-saldo {
            background: #222;
            border-top: 3px solid #d4af37;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Courier New', monospace;
        }
        .cifra-saldo {
            color: #2ecc71; /* Verde dinero */
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header-juego">
        <div>
            <h4 style="margin:0; color: #d4af37;"><?= Html::encode($model->nombre) ?></h4>
            <small class="text-muted"><?= Html::encode($model->proveedor) ?></small>
        </div>
        <a href="index.php?r=juego/lobby" class="btn btn-outline-light btn-sm">❌ Salir al Lobby</a>
    </div>

    <div class="area-juego">
        <div class="iframe-simulado">
            <div class="text-center">
                <?php if($model->url_caratula): ?>
                    <?= Html::img('@web/' . $model->url_caratula, ['width' => '150px']) ?><br><br>
                <?php endif; ?>
                <h1>GAME LOADED</h1>
                <p>Simulación del motor de juego...</p>
                <button class="btn btn-warning btn-lg" onclick="alert('¡Has ganado 10€! (Simulación)')">GIRAR / JUGAR</button>
            </div>
        </div>
    </div>

    <div class="barra-saldo">
        <div>
            <span class="text-muted">SALDO ACTUAL:</span>
            <span class="cifra-saldo"><?= number_format($saldo, 2) ?> €</span>
        </div>
        <div>
            <button class="btn btn-success">💰 DEPOSITAR</button>
        </div>
    </div>

</body>
</html>
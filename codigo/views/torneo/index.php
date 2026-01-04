<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Torneos y Competición';
?>

<div class="torneo-index">
    <div class="text-center my-5">
        <h1 class="display-4">Torneos y Competición 🏆</h1>
        <p class="lead">Demuestra tu habilidad y gana grandes premios</p>
        <p>
            <?= Html::a('Crear Nuevo Torneo (Admin)', ['create'], ['class' => 'btn btn-outline-success']) ?>
        </p>
    </div>

    <div class="row">
        <?php foreach ($dataProvider->models as $torneo): ?>
            <?php 
                // Lógica PHP: Calcular estados
                $ahora = time();
                $inicio = strtotime($torneo->fecha_inicio);
                $fin = strtotime($torneo->fecha_fin);
                
                $yaEmpezo = $ahora >= $inicio;
                $yaTermino = $ahora > $fin;
            ?>

            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0" style="background-color: #f8f9fa;">
                    <div class="card-header bg-dark text-white text-center py-3">
                        <h5 class="m-0 font-weight-bold"><?= Html::encode($torneo->titulo) ?></h5>
                        <small class="text-white-50">
                            <?= $torneo->juego ? $torneo->juego->nombre : 'Juego General' ?>
                        </small>
                    </div>

                    <div class="card-body text-center bg-white">
                        <h2 class="text-warning font-weight-bold display-4" style="font-size: 2.5rem;">
                            <?= number_format($torneo->bolsa_premios, 0) ?>€ <span style="font-size: 1rem; color: #666;">GTD</span>
                        </h2>
                        <p class="card-text text-muted mb-4">
                            Entrada: <strong><?= $torneo->coste_entrada == 0 ? 'GRATIS' : $torneo->coste_entrada . '€' ?></strong>
                        </p>
                        
                        <div class="countdown-box mb-3"
                             data-inicio="<?= $torneo->fecha_inicio ?>"
                             data-fin="<?= $torneo->fecha_fin ?>">
                             <div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top-0 pb-4 px-4">
                        <?php if ($yaTermino): ?>
                            <button class="btn btn-secondary btn-block btn-lg" disabled style="opacity: 0.6;">
                                🏁 Torneo Finalizado
                            </button>
                            <a href="<?= Url::to(['torneo/view', 'id' => $torneo->id]) ?>" class="btn btn-link btn-block text-muted mt-2">
                                Ver Resultados
                            </a>

                        <?php elseif ($yaEmpezo): ?>
                            <a href="<?= Url::to(['torneo/jugar', 'id' => $torneo->id]) ?>" class="btn btn-danger btn-block btn-lg shadow">
                                🔥 ¡Jugar Ahora!
                            </a>
                            <a href="<?= Url::to(['torneo/view', 'id' => $torneo->id]) ?>" class="btn btn-outline-danger btn-block btn-sm mt-2">
                                Ver Ranking en Vivo
                            </a>

                        <?php else: ?>
                            <a href="<?= Url::to(['torneo/unirse', 'id' => $torneo->id]) ?>" 
                               class="btn btn-primary btn-block btn-lg shadow-sm"
                               onclick="return confirm('¿Confirmar pago de <?= $torneo->coste_entrada ?>€?')">
                               Pre-Inscribirse
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
// SCRIPT JAVASCRIPT CON ESTILOS DE ALTO CONTRASTE
$script = <<< JS
    function actualizarContadores() {
        const ahora = new Date().getTime();
        
        document.querySelectorAll('.countdown-box').forEach(function(caja) {
            const fechaInicio = new Date(caja.getAttribute('data-inicio')).getTime();
            const fechaFin = new Date(caja.getAttribute('data-fin')).getTime();
            
            // 1. FINALIZADO (Caja Gris Oscura - Letra Blanca)
            if (ahora > fechaFin) {
                caja.innerHTML = '<div class="alert alert-dark m-0 p-2 font-weight-bold">🔴 FINALIZADO</div>';
                return;
            }
            
            // 2. EN CURSO (Caja Roja - Letra Roja Oscura)
            if (ahora >= fechaInicio && ahora <= fechaFin) {
                caja.innerHTML = '<div class="alert alert-danger m-0 p-2 font-weight-bold">🔥 EN CURSO - ¡CORRE!</div>';
                return;
            }
            
            // 3. CUENTA ATRÁS (Caja Azul - Letra Azul Oscura)
            const distancia = fechaInicio - ahora;
            const dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
            const horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((distancia % (1000 * 60)) / 1000);
            
            // Usamos 'alert-primary' que en Bootstrap suele ser azul clarito con letras azul oscuro (muy legible)
            caja.innerHTML = 
                '<div class="alert alert-primary m-0 p-2">' +
                '<small class="d-block text-muted text-uppercase" style="font-size: 0.7rem;">Comienza en:</small>' +
                '<span class="h5 font-weight-bold">' + dias + 'd ' + horas + 'h ' + minutos + 'm ' + segundos + 's</span>' +
                '</div>';
        });
    }

    setInterval(actualizarContadores, 1000);
    actualizarContadores();
JS;

$this->registerJs($script);
?>
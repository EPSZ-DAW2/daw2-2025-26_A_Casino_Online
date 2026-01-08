<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Torneos y Competición';
?>

<div class="torneo-index">
    <div class="text-center my-5">
        <h1 class="display-4">Torneos y Competición 🏆</h1>
        <p class="lead">Demuestra tu habilidad y gana grandes premios</p>
        
        <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->puedeGestionarUsuarios()): ?>
            <p>
                <?= Html::a('➕ Crear Nuevo Torneo', ['create'], ['class' => 'btn btn-success btn-lg shadow']) ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php foreach ($dataProvider->models as $torneo): ?>
            <?php 
                // Lógica de estados
                $ahora = time();
                $inicio = strtotime($torneo->fecha_inicio);
                $fin = strtotime($torneo->fecha_fin);
            ?>

            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow border-0" style="transition: transform 0.2s;">
                    
                    <div class="card-header bg-dark text-white text-center py-3 position-relative">
                        <h5 class="m-0 font-weight-bold"><?= Html::encode($torneo->titulo) ?></h5>
                        
                        <div class="mt-2">
                            <?php if ($torneo->estado === 'Cancelado'): ?>
                                <span class="badge bg-danger">CANCELADO</span>
                            <?php elseif ($torneo->estado === 'Finalizado'): ?>
                                <span class="badge bg-secondary">FINALIZADO</span>
                            <?php elseif ($torneo->estado === 'En Curso'): ?>
                                <span class="badge bg-danger spinner-grow-sm">🔴 EN VIVO</span>
                            <?php else: ?>
                                <span class="badge bg-success">ABIERTO</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body text-center bg-light">
                        <h6 class="text-muted text-uppercase mb-3">
                            <?= $torneo->juegoAsociado ? $torneo->juegoAsociado->nombre : 'Multijuego' ?>
                        </h6>

                        <h3 class="display-5 font-weight-bold text-primary mb-3">
                            <?= number_format($torneo->bolsa_premios, 0) ?> €
                        </h3>
                        <small class="text-muted d-block mb-3">Bolsa de Premios</small>

                        <ul class="list-unstyled mb-4 text-start small mx-auto" style="max-width: 200px;">
                            <li class="mb-2">📅 <strong>Inicio:</strong> <?= Yii::$app->formatter->asDatetime($torneo->fecha_inicio, 'short') ?></li>
                            <li class="mb-2">🏁 <strong>Fin:</strong> <?= Yii::$app->formatter->asDatetime($torneo->fecha_fin, 'short') ?></li>
                            <li>🎟️ <strong>Entrada:</strong> <?= $torneo->coste_entrada > 0 ? $torneo->coste_entrada . ' €' : 'GRATIS' ?></li>
                        </ul>

                        <?= Html::a('Ver Ranking y Detalles', ['view', 'id' => $torneo->id], ['class' => 'btn btn-outline-primary w-100 rounded-pill']) ?>
                    </div>

                    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->puedeGestionarUsuarios()): ?>
                        <div class="card-footer bg-white border-top text-center">
                            <div class="btn-group w-100" role="group">
                                <?= Html::a('✏ Editar', ['update', 'id' => $torneo->id], [
                                    'class' => 'btn btn-outline-secondary btn-sm'
                                ]) ?>
                                
                                <?= Html::a('🗑 Borrar', ['delete', 'id' => $torneo->id], [
                                    'class' => 'btn btn-outline-danger btn-sm',
                                    'data' => [
                                        'confirm' => '¿Estás seguro de BORRAR este torneo? Se eliminarán también las participaciones.',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    <?php endif; ?>

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
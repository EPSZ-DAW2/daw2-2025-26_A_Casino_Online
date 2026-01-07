<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Usuario $usuario */
/** @var app\models\Logro[] $logros */

$this->title = 'Mi Sala de Trofeos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gamificacion-index">

    <div class="text-center mb-5">
        <h1 class="display-4 text-warning">🏆
            <?= Html::encode($this->title) ?> 🏆
        </h1>
        <p class="lead">Bienvenido, <strong>
                <?= Html::encode($usuario->nick) ?>
            </strong>. Aquí están tus hazañas.</p>

        <!-- Barra de progreso simulada para el siguiente nivel VIP -->
        <div class="card p-3 mb-4 shadow-sm">
            <h5>Nivel VIP: <span class="badge bg-primary">
                    <?= Html::encode($usuario->nivel_vip ?: 'Bronce') ?>
                </span></h5>
            <div class="progress" style="height: 25px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar"
                    style="width: <?= ($usuario->puntos_progreso % 100) ?>%;"
                    aria-valuenow="<?= $usuario->puntos_progreso ?>" aria-valuemin="0" aria-valuemax="1000">
                    <?= $usuario->puntos_progreso ?> Puntos XP
                </div>
            </div>
            <small class="text-muted mt-2">¡Sigue jugando para subir de nivel!</small>
        </div>
    </div>

    <!-- Muro de Logros (Grid de Tarjetas) -->
    <div class="row row-cols-1 row-cols-md-3 g-4">

        <?php if (empty($logros)): ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    Aún no has desbloqueado ningún logro. ¡Empieza a jugar para ganar trofeos!
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($logros as $logro): ?>
                <div class="col">
                    <div class="card h-100 border-warning mb-3 shadow">
                        <div class="card-header bg-warning text-dark text-center">
                            <strong>
                                <?= Html::encode($logro->nombre) ?>
                            </strong>
                        </div>
                        <div class="card-body text-center">
                            <!-- Icono trofeo (Placeholder si no hay imagen real) -->
                            <div style="font-size: 4rem;">
                                <?= $logro->icono_trofeo ? Html::img($logro->icono_trofeo, ['class' => 'img-fluid', 'style' => 'max-height:100px']) : '🥇' ?>
                            </div>
                            <p class="card-text mt-3">
                                <?= Html::encode($logro->descripcion) ?>
                            </p>
                        </div>
                        <div class="card-footer text-muted text-center">
                            Desbloqueado
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

</div>
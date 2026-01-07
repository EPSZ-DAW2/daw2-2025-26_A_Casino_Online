<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Juego[] $juegosDestacados */

$this->title = 'Royal Casino - Inicio';
?>
<div class="site-index">

    <div class="p-5 mb-4 bg-dark text-white rounded-3 shadow" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-bottom: 4px solid #d4af37;">
        <div class="container-fluid py-4 text-center">
            <h1 class="display-4 fw-bold text-warning">🎰 ¡Bienvenido a la Suerte!</h1>
            <p class="fs-4">Los mejores juegos, torneos en vivo y premios instantáneos.</p>
            <?php if (Yii::$app->user->isGuest): ?>
                <a class="btn btn-warning btn-lg px-5 fw-bold" href="<?= Url::to(['site/signup']) ?>">¡REGÍSTRATE AHORA!</a>
            <?php else: ?>
                <a class="btn btn-outline-warning btn-lg px-5" href="<?= Url::to(['juego/lobby']) ?>">Ir al Casino</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-5 text-center">
        <div class="col-12">
            <div class="card bg-danger text-white border-0 shadow-lg overflow-hidden">
                <div class="card-body">
                    <h3 class="text-uppercase" style="letter-spacing: 2px;">🔥 Gran Jackpot Acumulado 🔥</h3>
                    <h1 class="display-3 fw-bold text-warning" style="text-shadow: 2px 2px 4px #000;">1,245,390.50 €</h1>
                    <small>Actualizándose en tiempo real...</small>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-warning pb-2">
        <h3 class="text-uppercase m-0">🌟 Últimas Novedades</h3>
        <?= Html::a('Ver Catálogo Completo (Lobby) ➡️', ['/juego/lobby'], ['class' => 'btn btn-outline-dark btn-sm']) ?>
    </div>
    
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        
        <?php if (count($juegosDestacados) > 0): ?>
            
            <?php foreach ($juegosDestacados as $juego): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 game-card">
                        
                        <div style="height: 180px; background-color: #000; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                            <?php if ($juego->url_caratula): ?>
                                <?= Html::img('@web/' . $juego->url_caratula, [
                                    'alt' => $juego->nombre,
                                    'style' => 'width: 100%; height: 100%; object-fit: cover;'
                                ]) ?>
                            <?php else: ?>
                                <span style="font-size: 3rem;">
                                    <?= ($juego->tipo == 'Slot') ? '🍒' : (($juego->tipo == 'Ruleta') ? '🎡' : '🃏') ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="card-body text-center bg-light">
                            <h5 class="card-title fw-bold text-truncate"><?= Html::encode($juego->nombre) ?></h5>
                            <p class="card-text text-muted small"><?= Html::encode($juego->proveedor) ?></p>
                            
                            <?php if ($juego->en_mantenimiento): ?>
                                <button class="btn btn-secondary btn-sm w-100 disabled">Mantenimiento</button>
                            <?php else: ?>
                                <?= Html::a('Jugar Ahora', ['juego/jugar', 'id' => $juego->id], ['class' => 'btn btn-dark btn-sm w-100']) ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-transparent border-0 text-center pb-3">
                             <small class="text-success fw-bold">RTP: <?= $juego->rtp ?>%</small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            
            <div class="col-12 text-center py-5">
                <h4 class="text-muted">No hay juegos destacados en este momento.</h4>
                <p>Ve al panel de administración para crear tu primer juego.</p>
                <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->puedeGestionarJuegos()): ?>
                    <?= Html::a('➕ Crear Juego Nuevo', ['/juego/create'], ['class' => 'btn btn-primary']) ?>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>
    
    <div class="text-center mt-5">
        <?= Html::a('Explorar todos los Juegos', ['/juego/lobby'], ['class' => 'btn btn-outline-dark btn-lg']) ?>
    </div>
</div>
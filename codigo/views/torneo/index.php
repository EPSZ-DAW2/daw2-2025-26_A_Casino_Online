<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Torneos y Competición';
?>

<div class="torneo-index">
    <h1 class="text-center my-4"><?= Html::encode($this->title) ?> 🏆</h1>

    <p class="text-center">
        <?= Html::a('Crear Nuevo Torneo (Admin)', ['create'], ['class' => 'btn btn-outline-success']) ?>
    </p>

    <div class="row">
        <?php foreach ($dataProvider->models as $torneo): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm" style="border: 1px solid #d4af37;">
                    <div class="card-header bg-dark text-white text-center">
                        <h5 class="m-0"><?= Html::encode($torneo->titulo) ?></h5>
                        <small class="text-muted">
                            Juego: <?= $torneo->juego ? $torneo->juego->nombre : 'Juego General' ?>
                        </small>
                    </div>

                    <div class="card-body text-center">
                        <h2 class="text-warning font-weight-bold">
                            <?= number_format($torneo->bolsa_premios, 0) ?>€ GTD
                        </h2>
                        <p class="card-text">
                            <strong>Entrada (Buy-in):</strong> 
                            <?= $torneo->coste_entrada == 0 ? '<span class="badge badge-success">GRATIS</span>' : $torneo->coste_entrada . '€' ?>
                        </p>
                        <p>
                            <small>📅 Inicio: <?= Yii::$app->formatter->asDatetime($torneo->fecha_inicio, 'short') ?></small>
                        </p>
                    </div>

                    <div class="card-footer bg-white border-0">
                        <a href="<?= Url::to(['torneo/unirse', 'id' => $torneo->id]) ?>" 
                           class="btn btn-primary btn-block btn-lg"
                           onclick="return confirm('¿Confirmar pago de <?= $torneo->coste_entrada ?>€?')">
                           Unirse al Torneo
                        </a>
                        
                        <a href="<?= Url::to(['torneo/view', 'id' => $torneo->id]) ?>" class="btn btn-link btn-block btn-sm">
                           Ver Ranking y Detalles
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
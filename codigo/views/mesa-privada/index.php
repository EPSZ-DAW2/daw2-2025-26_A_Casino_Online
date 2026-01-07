<?php

use app\models\MesaPrivada;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Lobby de Mesas Privadas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mesa-privada-index">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <?= Html::encode($this->title) ?>
        </h1>
        <p>
            <?= Html::a('Crear Mesa Nueva', ['create'], ['class' => 'btn btn-success btn-lg']) ?>
        </p>
    </div>

    <!-- Grid personalizado para mostrar las mesas -->
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($dataProvider->getModels() as $mesa): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <span>Mesa #
                            <?= $mesa->id ?>
                        </span>
                        <span class="badge bg-success">
                            <?= $mesa->estado_mesa ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">
                            <?= Html::encode($mesa->tipo_juego ?: 'Estandar') ?>
                        </h5>
                        <p class="card-text">
                            <strong>Anfitrión:</strong>
                            <?= Html::encode($mesa->anfitrion->nick) ?><br>
                            <?php if ($mesa->contrasena_acceso): ?>
                                <span class="text-danger"><i class="bi bi-lock-fill"></i> Privada (Requiere clave)</span>
                            <?php else: ?>
                                <span class="text-success"><i class="bi bi-unlock-fill"></i> Pública</span>
                            <?php endif; ?>
                        </p>
                        <?= Html::a('Entrar a la Sala', ['join', 'id' => $mesa->id], ['class' => 'btn btn-primary w-100']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
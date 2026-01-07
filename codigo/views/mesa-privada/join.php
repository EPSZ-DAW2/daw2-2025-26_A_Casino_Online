<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\MesaPrivada $model */

$this->title = 'Acceso a Mesa Privada';
?>
<div class="mesa-privada-join card p-5 shadow" style="max-width: 500px; margin: 50px auto;">

    <div class="text-center mb-4">
        <h1 class="h3 mb-3 font-weight-normal">🔒 Esta mesa es privada</h1>
        <p>Introduce la contraseña para unirte a la partida de <strong>
                <?= Html::encode($model->anfitrion->nick) ?>
            </strong>.</p>
    </div>

    <?= Html::beginForm() ?>

    <div class="form-group mb-3">
        <label for="password_mesa">Contraseña de acceso:</label>
        <?= Html::passwordInput('password_mesa', '', ['class' => 'form-control', 'required' => true, 'autofocus' => true]) ?>
    </div>

    <div class="d-grid gap-2">
        <?= Html::submitButton('Desbloquear', ['class' => 'btn btn-primary btn-lg']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?= Html::endForm() ?>

</div>
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\MesaPrivada $model */

$this->title = 'Crear Mesa Privada';
$this->params['breadcrumbs'][] = ['label' => 'Lobby', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mesa-privada-create card p-4 shadow-sm" style="max-width: 600px; margin: 0 auto;">

    <h2 class="mb-4 text-center">
        <?= Html::encode($this->title) ?>
    </h2>

    <?php $form = ActiveForm::begin(); ?>

    <!-- Tipo de Juego -->
    <?= $form->field($model, 'tipo_juego')->dropDownList([
        'Poker Texas Hold\'em' => 'Poker Texas Hold\'em',
        'Blackjack' => 'Blackjack',
        'Ruleta Europea' => 'Ruleta Europea'
    ], ['prompt' => 'Selecciona un juego...']) ?>

    <!-- Contraseña -->
    <?= $form->field($model, 'contrasena_acceso')->passwordInput(['maxlength' => true, 'placeholder' => '(Opcional) Deja en blanco para mesa abierta']) ?>
    <small class="form-text text-muted mb-3">Si estableces una contraseña, solo tus amigos con la clave podrán
        entrar.</small>

    <div class="form-group text-center mt-4">
        <?= Html::submitButton('Crear y Entrar', ['class' => 'btn btn-success btn-lg px-5']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
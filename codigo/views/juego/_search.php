<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\JuegoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="juego-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'nombre') ?>

    <?= $form->field($model, 'proveedor') ?>

    <?= $form->field($model, 'tipo') ?>

    <?= $form->field($model, 'tematica') ?>

    <?php // echo $form->field($model, 'rtp') ?>

    <?php // echo $form->field($model, 'url_caratula') ?>

    <?php // echo $form->field($model, 'activo') ?>

    <?php // echo $form->field($model, 'es_nuevo') ?>

    <?php // echo $form->field($model, 'en_mantenimiento') ?>

    <?php // echo $form->field($model, 'tasa_pago_actual') ?>

    <?php // echo $form->field($model, 'estado_racha') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

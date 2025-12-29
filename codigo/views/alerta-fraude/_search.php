<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AlertaFraudeSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="alerta-fraude-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'id_usuario') ?>

    <?= $form->field($model, 'tipo') ?>

    <?= $form->field($model, 'nivel_riesgo') ?>

    <?= $form->field($model, 'estado') ?>

    <?php // echo $form->field($model, 'detalles_tecnicos') ?>

    <?php // echo $form->field($model, 'fecha_detectada') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

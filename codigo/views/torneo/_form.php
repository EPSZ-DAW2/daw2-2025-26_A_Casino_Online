<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Torneo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="torneo-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'titulo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_juego_asociado')->textInput() ?>

    <?= $form->field($model, 'fecha_inicio')->textInput() ?>

    <?= $form->field($model, 'fecha_fin')->textInput() ?>

    <?= $form->field($model, 'coste_entrada')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bolsa_premios')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estado')->dropDownList([ 'Abierto' => 'Abierto', 'En Curso' => 'En Curso', 'Finalizado' => 'Finalizado', 'Cancelado' => 'Cancelado', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

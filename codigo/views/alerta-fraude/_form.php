<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AlertaFraude $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="alerta-fraude-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_usuario')->textInput() ?>

    <?= $form->field($model, 'tipo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nivel_riesgo')->dropDownList([ 'Alto' => 'Alto', 'Medio' => 'Medio', 'Bajo' => 'Bajo', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'estado')->dropDownList([ 'Pendiente' => 'Pendiente', 'Investigando' => 'Investigando', 'Resuelto' => 'Resuelto', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'detalles_tecnicos')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'fecha_detectada')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

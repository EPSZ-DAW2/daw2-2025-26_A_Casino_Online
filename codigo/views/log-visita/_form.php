<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\LogVisita $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="log-visita-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_usuario')->textInput() ?>

    <?= $form->field($model, 'direccion_ip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dispositivo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fecha_hora')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

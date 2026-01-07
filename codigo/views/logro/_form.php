<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Logro $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="logro-form">

    <?php $form = ActiveForm::begin(); ?>

    <!-- Campo Nombre: Fundamental para identificar el logro -->
    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

    <!-- Campo Descripción: Explica al usuario cómo ganar este trofeo -->
    <?= $form->field($model, 'descripcion')->textarea(['rows' => 6]) ?>

    <!-- Campo Icono: Ruta al recurso gráfico (ej: /img/trofeos/oro.png) -->
    <!-- En un sistema real aquí pondríamos un FileInput para subir la imagen -->
    <?= $form->field($model, 'icono_trofeo')->textInput(['maxlength' => true, 'placeholder' => 'Ej: trofeo_oro.png']) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
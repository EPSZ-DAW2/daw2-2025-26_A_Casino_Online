<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Usuario $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="usuario-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <h3>Datos de Acceso</h3>
            <?= $form->field($model, 'nick')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
            
            <div class="alert alert-info">
                Dejar en blanco si no se desea cambiar la contraseña.
            </div>
            <?= $form->field($model, 'password_hash')->passwordInput(['maxlength' => true])->label('Contraseña') ?>
            
            <?= $form->field($model, 'rol')->dropDownList([ 
                'jugador' => 'Jugador', 
                'admin' => 'Administrador' 
            ]) ?>
        </div>

        <div class="col-md-6">
            <h3>Gestión y Estado</h3>
            
            <?= $form->field($model, 'nivel_vip')->dropDownList([ 
                'Bronce' => 'Bronce', 
                'Plata' => 'Plata', 
                'Oro' => 'Oro' 
            ]) ?>

            <?= $form->field($model, 'puntos_progreso')->textInput(['type' => 'number']) ?>

            <?= $form->field($model, 'estado_cuenta')->dropDownList([ 
                'Activo' => 'Activo', 
                'Bloqueado' => 'Bloqueado (Ban)' 
            ], ['prompt' => 'Seleccione Estado...']) ?>

            <?= $form->field($model, 'estado_verificacion')->dropDownList([ 
                'Pendiente' => 'Pendiente', 
                'Verificado' => 'Verificado', 
                'Rechazado' => 'Rechazado' 
            ]) ?>

            <?= $form->field($model, 'notas_internas')->textarea(['rows' => 3, 'placeholder' => 'Anotaciones del admin sobre este usuario...']) ?>
        </div>
    </div>

    <?php if (!$model->isNewRecord): ?>
    <hr>
    <h3>Documentación (KYC)</h3>
    <div class="row">
        <div class="col-md-6">
            <strong>DNI:</strong><br>
            <?= $model->foto_dni ? Html::img('@web/' . $model->foto_dni, ['width' => '200px']) : 'No subido' ?>
        </div>
        <div class="col-md-6">
            <strong>Selfie:</strong><br>
            <?= $model->foto_selfie ? Html::img('@web/' . $model->foto_selfie, ['width' => '200px']) : 'No subido' ?>
        </div>
    </div>
    <hr>
    <?php endif; ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar Cambios', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

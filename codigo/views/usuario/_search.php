<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\UsuarioSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="usuario-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'nick') ?>

    <?= $form->field($model, 'email') ?>

    <?= $form->field($model, 'password_hash') ?>

    <?= $form->field($model, 'auth_key') ?>

    <?php // echo $form->field($model, 'password_reset_token') ?>

    <?php // echo $form->field($model, 'access_token') ?>

    <?php // echo $form->field($model, 'rol') ?>

    <?php // echo $form->field($model, 'nombre') ?>

    <?php // echo $form->field($model, 'apellido') ?>

    <?php // echo $form->field($model, 'telefono') ?>

    <?php // echo $form->field($model, 'fecha_registro') ?>

    <?php // echo $form->field($model, 'avatar_url') ?>

    <?php // echo $form->field($model, 'nivel_vip') ?>

    <?php // echo $form->field($model, 'puntos_progreso') ?>

    <?php // echo $form->field($model, 'estado_cuenta') ?>

    <?php // echo $form->field($model, 'estado_verificacion') ?>

    <?php // echo $form->field($model, 'foto_dni') ?>

    <?php // echo $form->field($model, 'foto_selfie') ?>

    <?php // echo $form->field($model, 'notas_internas') ?>

    <?php // echo $form->field($model, 'codigo_referido_propio') ?>

    <?php // echo $form->field($model, 'id_padrino') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

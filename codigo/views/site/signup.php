<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Registrarse en el Casino';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Por favor completa los siguientes campos para registrarte:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

            <?= $form->field($model, 'nick')->textInput(['autofocus' => true]) ?>

            <?= $form->field($model, 'email') ?>

            <?php if ($model->referral_code): ?>
                <div class="alert alert-success p-2 small">
                    <i class="bi bi-person-check-fill"></i> Invitado por:
                    <strong><?= \yii\helpers\Html::encode($model->referral_code) ?></strong>
                </div>
            <?php endif; ?>
            <?= $form->field($model, 'referral_code')->hiddenInput()->label(false) ?>

            <?= $form->field($model, 'password')->passwordInput() ?>

            <?= $form->field($model, 'password_repeat')->passwordInput()->label('Repetir Contraseña') ?>

            <div class="form-group">
                <?= Html::submitButton('Registrarse', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\AlertaFraude;

/** @var yii\web\View $this */
/** @var app\models\AlertaFraude $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="alerta-fraude-form">

    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'id_usuario')->textInput(['type' => 'number', 'placeholder' => 'Escribe el ID (ej: 100)']) ?>

    <?= $form->field($model, 'tipo')->dropDownList([
        AlertaFraude::TIPO_BOT => '🤖 Posible Bot',
        AlertaFraude::TIPO_COLUSION => '🤝 Colusión (Trampa en equipo)',
        AlertaFraude::TIPO_CHIP_DUMPING => '💸 Chip Dumping (Pasar dinero)',
        AlertaFraude::TIPO_PATRON_ANOMALO => '📈 Patrón Anómalo',
    ], ['prompt' => 'Selecciona el tipo de fraude...']) ?>

    <?= $form->field($model, 'nivel_riesgo')->dropDownList([
        AlertaFraude::RIESGO_BAJO => '🟢 Riesgo Bajo',
        AlertaFraude::RIESGO_MEDIO => '🟠 Riesgo Medio',
        AlertaFraude::RIESGO_ALTO => '🔴 Riesgo Alto',
    ], ['prompt' => 'Selecciona el nivel de gravedad...']) ?>

    <?= $form->field($model, 'estado')->dropDownList(['Pendiente' => 'Pendiente', 'Investigando' => 'Investigando', 'Resuelto' => 'Resuelto',], ['prompt' => '']) ?>

    <?= $form->field($model, 'detalles_tecnicos')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'fecha_detectada')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
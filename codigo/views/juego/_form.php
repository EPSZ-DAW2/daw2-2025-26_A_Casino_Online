<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Juego */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="juego-form" style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; background: #f9f9f9;">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'tipo')->dropDownList([ 
                'Slot' => 'Slot', 
                'Ruleta' => 'Ruleta', 
                'Cartas' => 'Cartas' 
            ], ['prompt' => 'Selecciona Tipo...']) ?>
            
            <?= $form->field($model, 'proveedor')->textInput(['maxlength' => true]) ?>
        </div>
        
        <div class="col-md-6">
            <?= $form->field($model, 'tematica')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'rtp')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
    </div>

    <hr>

    <h3>Imagen del Juego</h3>
    <?= $form->field($model, 'archivoImagen')->fileInput() ?>
    
    <?php if ($model->url_caratula): ?>
        <div class="form-group">
            <label>Imagen Actual:</label><br>
            <?= Html::img('@web/' . $model->url_caratula, ['width' => '150px']) ?>
        </div>
    <?php endif; ?>

    <hr>

    <h3>Estado</h3>
    <?= $form->field($model, 'activo')->checkbox() ?>
    <?= $form->field($model, 'en_mantenimiento')->checkbox() ?>
    <?= $form->field($model, 'es_nuevo')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar Juego', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
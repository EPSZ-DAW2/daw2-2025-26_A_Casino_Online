<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Juego;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\Torneo */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="torneo-form card shadow p-4">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'titulo')->textInput(['maxlength' => true, 'placeholder' => 'Ej: Gran Torneo de Poker Viernes']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?php 
                // 1. Buscamos todos los juegos activos
                $juegosDisponibles = Juego::find()->all();
                
                // 2. Los convertimos en un array [id => nombre]
                $listaJuegos = ArrayHelper::map($juegosDisponibles, 'id', 'nombre');
            ?>
            
            <?= $form->field($model, 'id_juego_asociado')->dropDownList(
                $listaJuegos, 
                ['prompt' => 'Selecciona el juego del torneo...']
            )->label('Juego Asociado') ?>
        </div>
        
        <div class="col-md-3">
            <?= $form->field($model, 'coste_entrada')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'bolsa_premios')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?php 
                // Truco para formatear la fecha si ya existe (al editar) para que el input HTML5 la lea
                // El input type="datetime-local" necesita formato "YYYY-MM-DDTHH:MM" (con una T en medio)
                $valInicio = $model->fecha_inicio ? date('Y-m-d\TH:i', strtotime($model->fecha_inicio)) : '';
            ?>
            <?= $form->field($model, 'fecha_inicio')->textInput(['type' => 'datetime-local', 'value' => $valInicio]) ?>
        </div>
        
        <div class="col-md-6">
            <?php 
                $valFin = $model->fecha_fin ? date('Y-m-d\TH:i', strtotime($model->fecha_fin)) : '';
            ?>
            <?= $form->field($model, 'fecha_fin')->textInput(['type' => 'datetime-local', 'value' => $valFin]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'estado')->dropDownList([ 
                'Abierto' => 'Abierto (Inscripciones On)', 
                'En Curso' => 'En Curso (Jugando)', 
                'Finalizado' => 'Finalizado', 
                'Cancelado' => 'Cancelado'
            ]) ?>
        </div>
    </div>

    <div class="form-group mt-4 text-center">
        <?= Html::submitButton('💾 Guardar Torneo', ['class' => 'btn btn-success btn-lg px-5']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
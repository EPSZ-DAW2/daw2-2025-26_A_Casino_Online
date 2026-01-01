<?php

use app\models\AlertaFraude;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\AlertaFraudeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Alerta Fraudes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alerta-fraude-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Alerta Fraude', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function ($model) {
            if ($model->nivel_riesgo == 'Alto') {
                return ['class' => 'table-danger']; // Rojo Bootstrap
            } elseif ($model->nivel_riesgo == 'Medio') {
                return ['class' => 'table-warning']; // Amarillo Bootstrap
            }
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'id_usuario',
            'tipo',
            'nivel_riesgo',
            'estado',
            //'detalles_tecnicos:ntext',
            //'fecha_detectada',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete} {banear}', // Añadimos {banear} al template
                'buttons' => [
                    'banear' => function ($url, $model) {
                        return \yii\helpers\Html::a('⛔ Banear', ['banear', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'title' => 'Bloquear cuenta de usuario permanentemente',
                            'style' => 'margin-left: 5px;',
                            'data' => [
                                'confirm' => '¿Estás 100% seguro de que quieres BANEAR a este usuario?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>


</div>

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
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AlertaFraude $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>

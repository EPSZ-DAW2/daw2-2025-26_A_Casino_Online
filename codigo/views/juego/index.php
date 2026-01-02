<?php

use app\models\Juego;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\JuegoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Juegos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="juego-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Juego', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'url_caratula',
                'format' => 'html',
                'label' => 'Carátula',
                'value' => function ($data) {
                    if($data->url_caratula){
                        return Html::img('@web/' . $data->url_caratula, ['width' => '70px']);
                    }
                    return "Sin imagen";
                },
            ],

            'id',
            'nombre',
            'proveedor',
            'tipo',
            'tematica',
            //'rtp',
            //'url_caratula:url',
            //'activo',
            //'es_nuevo',
            //'en_mantenimiento',
            //'tasa_pago_actual',
            //'estado_racha',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Juego $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>

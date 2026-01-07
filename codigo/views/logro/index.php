<?php

use app\models\Logro;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Gestión de Logros (Gamificación)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="logro-index">

    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <p>
        <?= Html::a('Crear Nuevo Logro', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <!-- GridView para listar los logros existentes -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'nombre',
            'descripcion:ntext',
            [
                'attribute' => 'icono_trofeo',
                'format' => 'raw',
                'value' => function ($model) {
                        // Si hay icono, mostramos una pequeña previsualización (simulada con texto o img si existiera url real)
                        return $model->icono_trofeo ? Html::encode($model->icono_trofeo) : '<span class="text-muted">(Sin icono)</span>';
                    },
            ],

            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, Logro $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    }
            ],
        ],
    ]); ?>

</div>
<?php
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Panel de Control de Transacciones (Admin)';
?>
<div class="transaccion-admin container mt-4">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card shadow mt-4">
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    'id',
                    [
                        'attribute' => 'id_usuario',
                        'label' => 'Usuario',
                        'value' => function($model) {
                            return $model->usuario ? $model->usuario->nick : 'Desconocido';
                        }
                    ],
                    'tipo_operacion',
                    'cantidad:currency',
                    'metodo_pago',
                    'referencia_externa', // Aquí verás el número de tarjeta o tlf que añadimos antes
                    [
                        'attribute' => 'estado',
                        'format' => 'raw',
                        'value' => function($model) {
                            $class = $model->estado == 'Completado' ? 'success' : ($model->estado == 'Pendiente' ? 'warning' : 'danger');
                            return "<span class='badge bg-$class'>$model->estado</span>";
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{aprobar} {rechazar}',
                        'visibleButtons' => [
                            'aprobar' => function ($model) { return $model->estado === 'Pendiente'; },
                            'rechazar' => function ($model) { return $model->estado === 'Pendiente'; },
                        ],
                        'buttons' => [
                            'aprobar' => function ($url, $model) {
                                return Html::a('Aprobar', ['cambiar-estado', 'id' => $model->id, 'estado' => 'Completado'], [
                                    'class' => 'btn btn-sm btn-success',
                                    'data-method' => 'post'
                                ]);
                            },
                            'rechazar' => function ($url, $model) {
                                return Html::a('Rechazar', ['cambiar-estado', 'id' => $model->id, 'estado' => 'Rechazado'], [
                                    'class' => 'btn btn-sm btn-danger',
                                    'data-method' => 'post'
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
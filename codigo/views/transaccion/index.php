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
                    // TIPO: Deposito, Retirada, Apuesta o Premio.
                    'tipo_operacion',
                    // CANTIDAD: Formateado automáticamente a la moneda configurada (€).
                    'cantidad:currency',
                    // MÉTODO: Identifica si es Tarjeta, Bizum o Transferencia.
                    'metodo_pago',
                    // REFERENCIA EXTERNA: Muestra el dato sensible capturado (ej. nº tarjeta o tlf) para auditoría.
                    'referencia_externa', 

                    // ESTADO VISUAL: Uso de Badges dinámicos para identificar rápidamente el estado de la operación.
                    [
                        'attribute' => 'estado',
                        'format' => 'raw',
                        'value' => function($model) {
                            // Verde para éxito, Amarillo para atención (Pendiente), Rojo para fallo.
                            $class = $model->estado == 'Completado' ? 'success' : ($model->estado == 'Pendiente' ? 'warning' : 'danger');
                            return "<span class='badge bg-$class'>$model->estado</span>";
                        }
                    ],

                    // COLUMNA DE ACCIONES: Botones interactivos para que el Admin tome decisiones sobre transacciones pendientes.
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{aprobar} {rechazar}', // Solo mostramos botones de control
                        // LÓGICA DE VISIBILIDAD: Los botones solo aparecen si la transacción está 'Pendiente'.
                        'visibleButtons' => [
                            'aprobar' => function ($model) { return $model->estado === 'Pendiente'; },
                            'rechazar' => function ($model) { return $model->estado === 'Pendiente'; },
                        ],
                        'buttons' => [
                            // Botón para aprobar retiradas e ingresar el dinero al circuito real
                            'aprobar' => function ($url, $model) {
                                return Html::a('Aprobar', ['cambiar-estado', 'id' => $model->id, 'estado' => 'Completado'], [
                                    'class' => 'btn btn-sm btn-success',
                                    'data-method' => 'post' // Seguridad para evitar cambios vía GET simple
                                ]);
                            },
                            // Botón para denegar operaciones sospechosas o con datos erróneos
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
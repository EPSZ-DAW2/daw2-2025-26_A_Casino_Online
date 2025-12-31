<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Mis Accesos y Seguridad';
?>
<div class="log-visita-mis-visitas container">

    <h1 class="text-primary"><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">
        Aquí puedes revisar el historial de conexiones a tu cuenta.
        Si ves un acceso sospechoso desde un dispositivo que no reconoces, cambia tu contraseña inmediatamente.
    </p>

    <div class="card shadow-sm">
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => '',
                'tableOptions' => ['class' => 'table table-hover table-striped'],
                'columns' => [
                    // Columna 1: Icono
                    [
                        'label' => 'Dispositivo',
                        'format' => 'raw',
                        'value' => function ($model) {
                            // Validación de seguridad por si el campo viene vacío
                            $device = strtolower($model->dispositivo ?? '');
                            if (strpos($device, 'movil') !== false || strpos($device, 'android') !== false || strpos($device, 'iphone') !== false) {
                                return '<span style="font-size:1.5em; color: orange;">📱</span>';
                            } else {
                                return '<span style="font-size:1.5em; color: #17a2b8;">💻</span>';
                            }
                        },
                        'contentOptions' => ['style' => 'text-align: center; width: 80px;'],
                    ],

                    // Columna 2: Fecha (CORRECTO)
                    [
                        'attribute' => 'fecha_hora',
                        'label' => 'Fecha',
                        'format' => ['date', 'php:d/m/Y H:i:s'],
                    ],

                    // Columna 3: IP (CORREGIDO: direccion_ip)
                    [
                        'attribute' => 'direccion_ip', // <--- AQUÍ ESTABA EL ERROR
                        'label' => 'IP',
                    ],

                    // Columna 4: Botón Ver Detalles (Lo que íbamos a añadir hoy)
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return \yii\helpers\Html::a('Ver Detalles', $url, [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                ]);
                            },
                        ],
                        // Importante: redirigir a una vista tuya
                        'urlCreator' => function ($action, $model, $key, $index) {
                            return \yii\helpers\Url::to(['log-visita/view', 'id' => $model->id]);
                        }
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
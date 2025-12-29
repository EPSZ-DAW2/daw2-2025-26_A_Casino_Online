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
                // Quitamos el 'filterModel' para que quede más limpio visualmente para el usuario
                'summary' => '', // Ocultamos el texto "Mostrando 1-10 de..."
                'tableOptions' => ['class' => 'table table-hover table-striped'], // Estilo visual limpio
                'columns' => [
                    // Columna 1: Icono visual (Cumpliendo requisito W5)
                    [
                        'label' => 'Dispositivo',
                        'format' => 'raw',
                        'value' => function ($model) {
                            // Lógica simple para iconos: Si dice "Movil" o "Android/iPhone" ponemos icono de móvil
                            $device = strtolower($model->dispositivo);
                            if (strpos($device, 'movil') !== false || strpos($device, 'android') !== false || strpos($device, 'iphone') !== false) {
                                return '<span style="font-size:1.5em; color: orange;">📱</span> <small>Móvil</small>';
                            } else {
                                return '<span style="font-size:1.5em; color: #17a2b8;">💻</span> <small>PC/Laptop</small>';
                            }
                        },
                        'contentOptions' => ['style' => 'text-align: center; width: 120px;'],
                    ],

                    // Columna 2: Fecha y Hora (Más legible)
                    [
                        'attribute' => 'fecha_hora',
                        'label' => '¿Cuándo?',
                        'format' => ['date', 'php:d/m/Y H:i:s'], // Formato amigable español
                    ],

                    // Columna 3: Dirección IP
                    [
                        'attribute' => 'ip',
                        'label' => 'Desde la IP',
                        'value' => function ($model) {
                            return $model->ip; // Aquí podrías ocultar parte de la IP si quisieras privacidad: "192.168.1.***"
                        }
                    ],

                    // Columna 4: Detalles Técnicos (Navegador completo)
                    [
                        'attribute' => 'dispositivo',
                        'label' => 'Detalle Técnico',
                        'format' => 'ntext',
                        'contentOptions' => ['class' => 'text-muted small'], // Texto más pequeño y gris
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
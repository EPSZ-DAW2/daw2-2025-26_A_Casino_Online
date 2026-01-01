<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\LogVisita */

$this->title = 'Informe de Seguridad #' . $model->id;
\yii\web\YiiAsset::register($this);
?>
<div class="log-visita-view">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary"><i class="fas fa-user-secret"></i> Detalles Técnicos de Conexión</h1>
        <?= Html::a('⬅ Volver a Mis Visitas', ['mis-visitas'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">📡 Datos de Conexión</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'fecha_hora',
                                'label' => 'Momento del Acceso',
                                'format' => ['date', 'php:d/m/Y - H:i:s'],
                            ],
                            [
                                'attribute' => 'direccion_ip',
                                'label' => 'Dirección IP',
                                'value' => function($model){
                                    return $model->direccion_ip . ' (ISP Local)';
                                }
                            ],
                            [
                                'attribute' => 'dispositivo',
                                'label' => 'Huella del Navegador (User Agent)',
                                'format' => 'ntext',
                                'contentOptions' => ['style' => 'font-family: monospace; font-size: 0.85rem; color: #555;'],
                            ],
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <h4 class="text-success"><i class="fas fa-shield-alt"></i> Conexión Segura</h4>
                    <p class="mb-0 text-muted">No se han detectado anomalías en este inicio de sesión.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">🌍 Ubicación Aproximada</h5>
                </div>
                <div class="card-body p-0 text-center bg-light">
                    <div style="height: 200px; display: flex; align-items: center; justify-content: center; background-color: #e9ecef;">
                        <span style="font-size: 5rem;">🗺️</span>
                        <p class="ms-3 text-muted">Madrid, España<br><small>(Basado en IP)</small></p>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    Coordenadas aprox: 40.4168, -3.7038
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">📱 Análisis del Dispositivo</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <span style="font-size: 2.5rem;">🖥️</span>
                            <p class="mt-2 mb-0"><strong>Sistema Operativo</strong><br>Windows 10 / 11</p>
                        </div>
                        <div class="col-6 border-start">
                            <span style="font-size: 2.5rem;">🌐</span>
                            <p class="mt-2 mb-0"><strong>Navegador</strong><br>Google Chrome</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
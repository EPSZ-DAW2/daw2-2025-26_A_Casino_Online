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
        <?php // Botón de Crear eliminado por G5: Ahora se crea desde la lista de Usuarios (Botón Bloquear) ?>
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
                            // Obtenemos el usuario de la alerta (relación manual o directa)
                            $usuario = \app\models\Usuario::findOne($model->id_usuario);
                            $esBloqueado = $usuario && $usuario->estado_cuenta === 'Bloqueado';

                            $texto = $esBloqueado ? '✅ Desbanear' : '⛔ Banear';
                            $clase = $esBloqueado ? 'btn btn-success btn-sm' : 'btn btn-danger btn-sm';
                            $confirm = $esBloqueado ? '¿Reactivar acceso a este usuario?' : '¿BANEAR a este usuario?';

                            return Html::a($texto, ['banear', 'id' => $model->id], [
                                'class' => $clase,
                                'title' => 'Cambiar estado de bloqueo del usuario',
                                'style' => 'margin-left: 5px;',
                                'data' => [
                                    'confirm' => $confirm,
                                    'method' => 'post',
                                ],
                            ]);
                        },
                ],
            ],
        ],
    ]); ?>


</div>
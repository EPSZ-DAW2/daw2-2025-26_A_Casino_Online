<?php

use app\models\Usuario;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\UsuarioSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Gestión de Usuarios (G1)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="usuario-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Crear Nuevo Usuario', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // Datos principales
            'id',
            'nick',
            'email:email',

            //'password_hash',
            //'auth_key',
            //'password_reset_token',
            //'access_token',
            //'rol',
            //'nombre',
            //'apellido',
            //'telefono',
            //'fecha_registro',
            //'avatar_url:url',
            //'nivel_vip',
            //'puntos_progreso',
            //'estado_cuenta',
            //'estado_verificacion',
            //'foto_dni',
            //'foto_selfie',
            //'notas_internas:ntext',
            //'codigo_referido_propio',
            //'id_padrino',
    
            // Columna Rol con filtro
            [
                'attribute' => 'rol',
                'filter' => ['jugador' => 'Jugador', 'admin' => 'Admin'],
                'value' => function ($model) {
                        return ucfirst($model->rol);
                    },
            ],

            // Columna VIP (Requisito G1)
            [
                'attribute' => 'nivel_vip',
                'filter' => ['Bronce' => 'Bronce', 'Plata' => 'Plata', 'Oro' => 'Oro'],
                'contentOptions' => function ($model) {
                        // Ponemos colores según el nivel VIP
                        if ($model->nivel_vip == 'Oro')
                            return ['style' => 'background-color:#FFF8DC; font-weight:bold; color:#B8860B'];
                        if ($model->nivel_vip == 'Plata')
                            return ['style' => 'background-color:#F0F8FF; font-weight:bold; color:#708090'];
                        return [];
                    },
            ],

            // Estado de la cuenta (Para ver baneados rápidamente)
            [
                'attribute' => 'estado_cuenta',
                'filter' => ['Activo' => 'Activo', 'Bloqueado' => 'Bloqueado'],
                'contentOptions' => function ($model) {
                        return $model->estado_cuenta == 'Bloqueado' ? ['class' => 'bg-danger text-white'] : [];
                    },
            ],

            // Verificación (KYC)
            [
                'attribute' => 'estado_verificacion',
                'filter' => ['Pendiente' => 'Pendiente', 'Verificado' => 'Verificado', 'Rechazado' => 'Rechazado'],
            ],

            // Botones de acción
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Usuario $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    },
                'template' => '{view} {update} {delete} {bloquear}', // Añadimos botón bloquear
                'buttons' => [
                    'bloquear' => function ($url, $model, $key) {
                            return Html::a(
                                '⛔',
                                ['alerta-fraude/create', 'user_id' => $model->id],
                                [
                                    'class' => 'btn btn-danger btn-sm',
                                    'title' => 'Bloquear / Reportar Fraude',
                                    'data-pjax' => '0',
                                ]
                            );
                        },
                ],
            ],
        ],
    ]); ?>

</div>
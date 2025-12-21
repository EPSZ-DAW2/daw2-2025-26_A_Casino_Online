<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Usuario $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="usuario-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'nick',
            'email:email',
            'password_hash',
            'auth_key',
            'password_reset_token',
            'access_token',
            'rol',
            'nombre',
            'apellido',
            'telefono',
            'fecha_registro',
            'avatar_url:url',
            'nivel_vip',
            'puntos_progreso',
            'estado_cuenta',
            'estado_verificacion',
            'foto_dni',
            'foto_selfie',
            'notas_internas:ntext',
            'codigo_referido_propio',
            'id_padrino',
        ],
    ]) ?>

</div>

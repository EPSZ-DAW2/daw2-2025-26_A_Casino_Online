<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AlertaFraude $model */

$this->title = 'Update Alerta Fraude: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Alerta Fraudes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="alerta-fraude-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

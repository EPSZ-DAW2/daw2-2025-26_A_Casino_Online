<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Logro $model */

$this->title = 'Actualizar Logro: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Logros', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="logro-update">

    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
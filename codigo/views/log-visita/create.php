<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\LogVisita $model */

$this->title = 'Create Log Visita';
$this->params['breadcrumbs'][] = ['label' => 'Log Visitas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-visita-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

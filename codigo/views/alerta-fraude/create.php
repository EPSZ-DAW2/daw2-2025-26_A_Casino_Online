<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AlertaFraude $model */

$this->title = 'Create Alerta Fraude';
$this->params['breadcrumbs'][] = ['label' => 'Alerta Fraudes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alerta-fraude-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

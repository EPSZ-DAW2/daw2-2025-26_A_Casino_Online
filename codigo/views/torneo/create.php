<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Torneo $model */

$this->title = 'Create Torneo';
$this->params['breadcrumbs'][] = ['label' => 'Torneos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="torneo-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

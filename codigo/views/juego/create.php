<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Juego $model */

$this->title = 'Create Juego';
$this->params['breadcrumbs'][] = ['label' => 'Juegos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="juego-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

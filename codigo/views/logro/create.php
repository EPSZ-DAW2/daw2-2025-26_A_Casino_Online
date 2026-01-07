<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Logro $model */

$this->title = 'Crear Logro';
$this->params['breadcrumbs'][] = ['label' => 'Logros', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="logro-create">

    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
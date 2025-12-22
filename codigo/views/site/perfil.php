<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Progress;

/** @var yii\web\View $this */
/** @var app\models\Usuario $model */

$this->title = 'Mi Cuenta - ' . $model->nick;

// --- LÓGICA VISUAL DE NIVEL VIP ---
$puntos = $model->puntos_progreso ?? 0;
$nivelActual = $model->nivel_vip;
$porcentaje = 0;
$claseBarra = 'bg-info'; // Color por defecto (Bronce/Azul)
$siguienteNivel = 'Plata';
$puntosObjetivo = 1000;

if ($puntos < 1000) {
    // Nivel Bronce
    $nivelActual = 'Bronce';
    $porcentaje = ($puntos / 1000) * 100;
    $claseBarra = 'bg-secondary'; // Gris
    $siguienteNivel = 'Plata';
    $puntosObjetivo = 1000;
} elseif ($puntos < 5000) {
    // Nivel Plata
    $nivelActual = 'Plata';
    $porcentaje = (($puntos - 1000) / 4000) * 100;
    $claseBarra = 'bg-secondary text-dark'; // Gris metálico
    $siguienteNivel = 'Oro';
    $puntosObjetivo = 5000;
} else {
    // Nivel Oro
    $nivelActual = 'Oro';
    $porcentaje = 100;
    $claseBarra = 'bg-warning text-dark'; // Amarillo Dorado
    $siguienteNivel = 'Máximo Nivel';
    $puntosObjetivo = $puntos;
}
?>

<div class="site-perfil container mt-4">
    
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                
                <div class="col-md-2 text-center">
                    <?php 
                        // Si la ruta no tiene "http", asumimos que es local en 'uploads/'
                        $avatarPath = ($model->avatar_url && strpos($model->avatar_url, 'http') === false) 
                            ? '@web/uploads/' . $model->avatar_url 
                            : '@web/default_avatar.png'; // Imagen por defecto si falla
                    ?>
                    <?= Html::img($avatarPath, [
                        'class' => 'img-thumbnail rounded-circle', 
                        'style' => 'width: 120px; height: 120px; object-fit: cover;',
                        'alt' => 'Avatar'
                    ]) ?>
                </div>
                
                <div class="col-md-10">
                    <h2 class="mb-0 text-primary">Hola, <?= Html::encode($model->nick) ?></h2>
                    <p class="text-muted">Miembro desde <?= Yii::$app->formatter->asDate($model->fecha_registro, 'long') ?></p>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-uppercase" style="color: #555;">Nivel VIP: <?= $nivelActual ?></span>
                            <small>Puntos: <?= $puntos ?> / <?= $puntosObjetivo ?></small>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar <?= $claseBarra ?> progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: <?= $porcentaje ?>%" 
                                 aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100">
                                <?= round($porcentaje) ?>%
                            </div>
                        </div>
                        <small class="text-muted">¡Juega más para alcanzar el nivel <?= $siguienteNivel ?>!</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Editar Mis Datos</h4>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'nombre')->textInput() ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'apellido')->textInput() ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'email')->input('email') ?>
                    
                    <?= $form->field($model, 'telefono')->textInput() ?>

                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label">Cambiar Avatar</label>
                        <?= $form->field($model, 'avatar_url')->fileInput()->label(false) ?>
                        <div class="form-text">Formatos permitidos: JPG, PNG.</div>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="text-primary mb-3">🪪 Verificación de Identidad (KYC)</h5>
                    
                    <?php if ($model->estado_verificacion == 'Verificado'): ?>
                        <div class="alert alert-success">
                            ✅ <strong>¡Tu cuenta está Verificada!</strong> Ya puedes retirar ganancias.
                        </div>
                    <?php elseif ($model->estado_verificacion == 'Pendiente'): ?>
                        <div class="alert alert-warning">
                            ⏳ <strong>Documentos en Revisión.</strong> El administrador está verificando tus archivos.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border">
                            ⚠️ <strong>Cuenta No Verificada.</strong> Sube tu DNI y Selfie para desbloquear retiradas.
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Foto del DNI (Anverso)</label>
                            <?= $form->field($model, 'foto_dni')->fileInput()->label(false) ?>
                            <?php if ($model->foto_dni): ?>
                                <small class="text-success">✔ Archivo subido previamente</small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Foto Selfie (Sosteniendo DNI)</label>
                            <?= $form->field($model, 'foto_selfie')->fileInput()->label(false) ?>
                            <?php if ($model->foto_selfie): ?>
                                <small class="text-success">✔ Archivo subido previamente</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr class="my-4">

                    <div class="form-group text-end">
                        <?= Html::submitButton('Guardar Cambios', ['class' => 'btn btn-primary px-4']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light border-0">
                <div class="card-body text-center">
                    <h5>🏅 Mis Logros</h5>
                    <p class="text-muted">Aún no has desbloqueado medallas.</p>
                    <div class="d-flex justify-content-center opacity-25">
                        <div class="mx-2" style="font-size: 2rem;">🏆</div>
                        <div class="mx-2" style="font-size: 2rem;">🎰</div>
                        <div class="mx-2" style="font-size: 2rem;">🃏</div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3 shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">🛡️ Últimos Accesos</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-sm mb-0" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Obtenemos los logs usando la relación que creamos
                            $logs = \app\models\LogVisita::find()
                                    ->where(['id_usuario' => $model->id])
                                    ->orderBy(['fecha_hora' => SORT_DESC])
                                    ->limit(5)
                                    ->all();
                            
                            if (count($logs) > 0):
                                foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= Yii::$app->formatter->asRelativeTime($log->fecha_hora) ?></td>
                                    <td><?= Html::encode($log->direccion_ip) ?></td>
                                </tr>
                                <?php endforeach; 
                            else: ?>
                                <tr><td colspan="2" class="text-center text-muted">Sin registros recientes</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white text-center">
                    <small class="text-muted">Si no reconoces una IP, ¡cambia tu contraseña!</small>
                </div>
            </div>
        </div>
    </div>
</div>
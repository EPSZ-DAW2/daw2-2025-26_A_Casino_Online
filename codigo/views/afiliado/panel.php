<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Usuario $usuario */
/** @var app\models\Usuario[] $afiliados */
/** @var float $comisionTotal */

$this->title = 'Panel de Afiliados - Gana Dinero';
$this->params['breadcrumbs'][] = $this->title;

// URL base para compartir (ej. localhost/index.php?r=site/signup&ref=...)
$linkReferido = Url::base(true) . '/index.php?r=site/signup&ref=' . $usuario->codigo_referido_propio;
?>
<div class="afiliado-panel container">

    <!-- Encabezado Promocional -->
    <div class="p-5 mb-4 bg-light rounded-3 shadow-sm text-center"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h1 class="display-4 fw-bold">🚀 Programa de Partners</h1>
        <p class="fs-4">Invita amigos y gana el 20% de la casa de por vida.</p>
        <hr class="my-4" style="background-color: white; opacity: 0.3;">

        <p>Tu enlace único de invitación es:</p>
        <div class="input-group mb-3 w-50 mx-auto">
            <input type="text" class="form-control text-center fs-5 fw-bold text-primary" value="<?= $linkReferido ?>"
                readonly id="ref-link">
            <button class="btn btn-warning" type="button" onclick="copiarLink()">Copiar Enlace</button>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row text-center mb-5">
        <div class="col-md-4">
            <div class="card border-primary shadow h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary">Usuarios Referidos</h5>
                    <p class="display-3 fw-bold">
                        <?= count($afiliados) ?>
                    </p>
                    <i class="bi bi-people-fill fs-1 text-muted"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success shadow h-100">
                <div class="card-body">
                    <h5 class="card-title text-success">Comisiones Totales</h5>
                    <p class="display-3 fw-bold">
                        <?= Yii::$app->formatter->asCurrency($comisionTotal) ?>
                    </p>
                    <i class="bi bi-cash-coin fs-1 text-muted"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info shadow h-100">
                <div class="card-body">
                    <h5 class="card-title text-info">Tasa de Conversión</h5>
                    <p class="display-3 fw-bold">100%</p> <!-- Simulado -->
                    <small>De Click a Registro</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <h3 class="mb-3"><i class="bi bi-list-stars"></i> Tus Ahijados</h3>
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Usuario (Nick)</th>
                    <th>Nivel VIP</th>
                    <th>Fecha Registro</th>
                    <th>Estado</th>
                    <th>Tus Ganancias</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($afiliados)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <em>Aún no has invitado a nadie. ¡Comparte tu enlace hoy!</em>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($afiliados as $subUser): ?>
                        <tr>
                            <td class="fw-bold">
                                <img src="<?= $subUser->avatar_url ?: 'https://via.placeholder.com/30' ?>"
                                    class="rounded-circle me-2" width="30" height="30">
                                <?= Html::encode($subUser->nick) ?>
                            </td>
                            <td><span class="badge bg-secondary">
                                    <?= $subUser->nivel_vip ?>
                                </span></td>
                            <td>
                                <?= Yii::$app->formatter->asDate($subUser->fecha_registro) ?>
                            </td>
                            <td>
                                <?php if ($subUser->esVerificado()): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold text-success">
                                <?= $subUser->esVerificado() ? '10.00 €' : '0.00 €' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    function copiarLink() {
        var copyText = document.getElementById("ref-link");
        copyText.select();
        copyText.setSelectionRange(0, 99999); /* Para móviles */
        navigator.clipboard.writeText(copyText.value);
        alert("¡Enlace copiado al portapapeles!");
    }
</script>
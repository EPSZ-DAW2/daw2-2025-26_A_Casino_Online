<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);


/** @var \app\models\Usuario $identity */
$identity = Yii::$app->user->identity;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header id="header">
    <?php
    NavBar::begin([
        'brandLabel' => '🎰 ROYAL CASINO', 
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top shadow'],
    ]);

    // --- MENÚ IZQUIERDO (Navegación Principal) ---
    $menuItems = [
        ['label' => '🏠 Inicio', 'url' => ['/site/index']],
        // Enlaces placeholder para G3 y G4
        ['label' => '🎰 Slots', 'url' => '#', 'linkOptions' => ['class' => 'text-warning']], 
        ['label' => '🎲 Ruleta', 'url' => '#'],
        ['label' => '🏆 Torneos', 'url' => '#'], 
    ];

    // --- MENÚS DE GESTIÓN (VISIBILIDAD POR ROLES) ---
    if (!Yii::$app->user->isGuest) {
        
        // 1. SEGURIDAD (Para todos los usuarios logueados)
        $menuItems[] = ['label' => '🛡️ Mi Seguridad', 'url' => ['/log-visita/mis-visitas']];

        // 2. GESTIÓN DE USUARIOS Y FRAUDE (G1 / G5)
        // Permiso: SuperAdmin o Admin
        if ($identity->puedeGestionarUsuarios()) {
            $menuItems[] = ['label' => '⚙️ USUARIOS', 'url' => ['/usuario/index'], 'linkOptions' => ['class' => 'text-danger fw-bold']];
            $menuItems[] = ['label' => '🚨 FRAUDE', 'url' => ['/alerta-fraude/index']];
        }

        // 3. GESTIÓN FINANCIERA (G2)
        // Permiso: SuperAdmin o Financiero
        if ($identity->puedeGestionarDinero()) {
            $menuItems[] = ['label' => '💰 PAGOS', 'url' => ['/transaccion/index'], 'linkOptions' => ['class' => 'text-info fw-bold']];
        }

        // 4. GESTIÓN DE JUEGOS (G3)
        // Permiso: SuperAdmin o Croupier
        if ($identity->puedeGestionarJuegos()) {
            $menuItems[] = ['label' => '🎮 JUEGOS', 'url' => ['/juego/index']];
        }
    }

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav me-auto mb-2 mb-md-0'],
        'items' => $menuItems,
    ]);

    // --- MENÚ DERECHO (Usuario y Acciones) ---
    echo '<ul class="navbar-nav ms-auto align-items-center">';

    if (Yii::$app->user->isGuest) {
        // VISTA INVITADO
        echo '<li class="nav-item">' . Html::a('Registrarse', ['/site/signup'], ['class' => 'btn btn-outline-warning btn-sm me-2']) . '</li>';
        echo '<li class="nav-item">' . Html::a('Entrar', ['/site/login'], ['class' => 'btn btn-primary btn-sm']) . '</li>';
    } else {
        // VISTA USUARIO LOGUEADO
        
        // Avatar seguro (si falla la imagen, pone una por defecto)
        $avatarPath = ($identity->avatar_url && strpos($identity->avatar_url, 'http') === false) 
            ? '@web/uploads/' . $identity->avatar_url 
            : '@web/default_avatar.png';

        // SALDO EN TIEMPO REAL (G2)
        // Usamos el operador nullsafe (?) por si monedero aún no existe
        $saldo = $identity->monedero ? number_format($identity->monedero->saldo_real, 2) : '0.00';
        
        echo '<li class="nav-item me-3">';
        echo '<span class="badge bg-success p-2 shadow-sm">💰 ' . $saldo . ' €</span>';
        echo '</li>';

        // Dropdown del Usuario
        echo '<li class="nav-item dropdown">';
        echo '<a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">';
        echo Html::img($avatarPath, ['class' => 'rounded-circle me-2', 'width' => '32', 'height' => '32', 'style' => 'object-fit:cover; border: 2px solid gold;']);
        echo Html::encode($identity->nick);
        echo ' <span class="badge bg-secondary ms-2" style="font-size:0.7em">' . strtoupper($identity->rol) . '</span>';
        echo '</a>';
        
        echo '<ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="userDropdown">';
        echo '<li>' . Html::a('👤 Mi Perfil', ['/site/perfil'], ['class' => 'dropdown-item']) . '</li>';
        echo '<li>' . Html::a('💳 Mi Monedero', ['/monedero/index'], ['class' => 'dropdown-item']) . '</li>';        
        echo '<li><hr class="dropdown-divider"></li>';
        echo '<li>' . Html::beginForm(['/site/logout'])
            . Html::submitButton('Cerrar Sesión', ['class' => 'dropdown-item text-danger'])
            . Html::endForm() . '</li>';
        echo '</ul>';
        echo '</li>';
    }

    echo '</ul>';
    NavBar::end();
    ?>
</header>

<main id="main" class="flex-shrink-0" style="padding-top: 70px;" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto py-3 bg-light">
    <div class="container">
        <div class="row text-muted">
            <div class="col-md-6 text-center text-md-start">&copy; Royal Casino <?= date('Y') ?></div>
            <div class="col-md-6 text-center text-md-end"><?= Yii::powered() ?></div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
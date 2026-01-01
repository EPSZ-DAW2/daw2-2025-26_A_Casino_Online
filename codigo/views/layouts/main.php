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
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
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
        // Marca del Casino (Izquierda)
        'brandLabel' => '🎰 ROYAL CASINO', 
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top shadow'],
    ]);

    // --- MENÚ IZQUIERDO (Navegación Principal) ---
    $menuItems = [
        ['label' => '🏠 Inicio', 'url' => ['/site/index']],
        // Estos enlaces apuntarán a los módulos de tus compañeros cuando estén listos
        ['label' => '🎰 Slots', 'url' => '#', 'linkOptions' => ['class' => 'text-warning']], 
        ['label' => '🎲 Ruleta', 'url' => '#'],
        ['label' => '🏆 Torneos', 'url' => '#'], // Futuro G4
    ];
    // --- SECCIÓN SEGURIDAD (G5) ---
    if (!Yii::$app->user->isGuest) {
        // Enlace 1: Para todos los usuarios (Tu panel bonito)
        $menuItems[] = ['label' => '🛡️ Mi Seguridad', 'url' => ['/log-visita/mis-visitas']];
        
        // Enlace 2: Solo si es admin (Gestión)
        if (Yii::$app->user->identity->username === 'admin') {
            $menuItems[] = ['label' => '🚨 Gestión Fraude', 'url' => ['/alerta-fraude/index']];
        }
    }

    // Si es ADMIN, le mostramos el acceso al Backend (G1)

    if (!Yii::$app->user->isGuest && Yii::$app->user->identity->esAdmin()) {
        $menuItems[] = ['label' => '⚙️ GESTIÓN (Admin)', 'url' => ['/usuario/index'], 'linkOptions' => ['class' => 'text-danger fw-bold']];
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
        $user = Yii::$app->user->identity;
        
        // Avatar pequeño para el menú
        $avatarPath = ($user->avatar_url && strpos($user->avatar_url, 'http') === false) 
            ? '@web/uploads/' . $user->avatar_url 
            : '@web/default_avatar.png';

        // Placeholder del Saldo (Esperando al G2)
        echo '<li class="nav-item me-3 text-white">';
        echo '<span class="badge bg-success p-2">💰 0.00 €</span>'; 
        echo '</li>';

        // Dropdown del Usuario
        echo '<li class="nav-item dropdown">';
        echo '<a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">';
        echo Html::img($avatarPath, ['class' => 'rounded-circle me-2', 'width' => '32', 'height' => '32', 'style' => 'object-fit:cover; border: 2px solid gold;']);
        echo Html::encode($user->nick);
        echo ' <span class="badge bg-secondary ms-2" style="font-size:0.7em">' . $user->nivel_vip . '</span>';
        echo '</a>';
        
        echo '<ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="userDropdown">';
        echo '<li>' . Html::a('👤 Mi Perfil', ['/site/perfil'], ['class' => 'dropdown-item']) . '</li>';
        echo '<li>' . Html::a('💳 Monedero', '#', ['class' => 'dropdown-item text-muted']) . '</li>'; // Futuro G2
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

<main id="main" class="flex-shrink-0" role="main">
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
            <div class="col-md-6 text-center text-md-start">&copy; My Company <?= date('Y') ?></div>
            <div class="col-md-6 text-center text-md-end"><?= Yii::powered() ?></div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

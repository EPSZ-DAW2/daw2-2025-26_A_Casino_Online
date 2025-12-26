<?php /*

 //** @var yii\web\View $this *

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent">
        <h1 class="display-4">Congratulations!</h1>

        <p class="lead">You have successfully created your Yii-powered application.</p>

        <p><a class="btn btn-lg btn-success" href="http://www.yiiframework.com">Get started with Yii</a></p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <h2>Heading</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-outline-secondary" href="http://www.yiiframework.com/doc/">Yii Documentation &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Heading</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-outline-secondary" href="http://www.yiiframework.com/forum/">Yii Forum &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Heading</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-outline-secondary" href="http://www.yiiframework.com/extensions/">Yii Extensions &raquo;</a></p>
            </div>
        </div>

    </div>
</div>
*/

//Index de la pagian web
/** @var yii\web\View $this */

$this->title = 'Royal Casino - Inicio';
?>
<div class="site-index">

    <div class="p-5 mb-4 bg-dark text-white rounded-3 shadow" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-bottom: 4px solid #d4af37;">
        <div class="container-fluid py-4 text-center">
            <h1 class="display-4 fw-bold text-warning">🎰 ¡Bienvenido a la Suerte!</h1>
            <p class="fs-4">Los mejores juegos, torneos en vivo y premios instantáneos.</p>
            <?php if (Yii::$app->user->isGuest): ?>
                <a class="btn btn-warning btn-lg px-5 fw-bold" href="<?= \yii\helpers\Url::to(['site/signup']) ?>">¡REGÍSTRATE AHORA!</a>
            <?php else: ?>
                <button class="btn btn-outline-light btn-lg disabled">Bonos Activos: 0</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-5 text-center">
        <div class="col-12">
            <div class="card bg-danger text-white border-0 shadow-lg overflow-hidden">
                <div class="card-body">
                    <h3 class="text-uppercase" style="letter-spacing: 2px;">🔥 Gran Jackpot Acumulado 🔥</h3>
                    <h1 class="display-3 fw-bold text-warning" style="text-shadow: 2px 2px 4px #000;">1,245,390.50 €</h1>
                    <small>Actualizándose en tiempo real...</small>
                </div>
            </div>
        </div>
    </div>

    <h3 class="border-bottom pb-2 mb-4 border-warning text-uppercase">🌟 Los Más Jugados</h3>
    
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        
        <div class="col">
            <div class="card h-100 shadow-sm border-0 game-card">
                <div style="height: 180px; background-color: #333; color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem;">🍒</div>
                <div class="card-body text-center bg-light">
                    <h5 class="card-title fw-bold">Super Slots 777</h5>
                    <p class="card-text text-muted small">Proveedor: NetEnt</p>
                    <a href="#" class="btn btn-dark w-100 disabled">Jugar Ahora</a>
                </div>
                <div class="card-footer bg-transparent border-0 text-center pb-3">
                     <small class="text-success fw-bold">RTP: 96.5%</small>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 shadow-sm border-0 game-card">
                <div style="height: 180px; background-color: #004d00; color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem;">🃏</div>
                <div class="card-body text-center bg-light">
                    <h5 class="card-title fw-bold">Blackjack Pro</h5>
                    <p class="card-text text-muted small">Proveedor: Playtech</p>
                    <a href="#" class="btn btn-dark w-100 disabled">Jugar Ahora</a>
                </div>
                <div class="card-footer bg-transparent border-0 text-center pb-3">
                     <small class="text-primary fw-bold">Mesas en Vivo</small>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 shadow-sm border-0 game-card">
                <div style="height: 180px; background-color: #800000; color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem;">🎡</div>
                <div class="card-body text-center bg-light">
                    <h5 class="card-title fw-bold">Ruleta Europea</h5>
                    <p class="card-text text-muted small">Proveedor: Evolution</p>
                    <a href="#" class="btn btn-dark w-100 disabled">Jugar Ahora</a>
                </div>
                <div class="card-footer bg-transparent border-0 text-center pb-3">
                     <small class="text-danger fw-bold">Hot: Rojo</small>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="card h-100 shadow-sm border-0 game-card">
                <div style="height: 180px; background-color: #d4af37; color: black; display: flex; align-items: center; justify-content: center; font-size: 3rem;">🏺</div>
                <div class="card-body text-center bg-light">
                    <h5 class="card-title fw-bold">Book of Ra</h5>
                    <p class="card-text text-muted small">Proveedor: Novomatic</p>
                    <a href="#" class="btn btn-dark w-100 disabled">Jugar Ahora</a>
                </div>
                <div class="card-footer bg-transparent border-0 text-center pb-3">
                     <small class="text-success fw-bold">RTP: 95%</small>
                </div>
            </div>
        </div>

    </div>
    
    <div class="text-center mt-5">
        <button class="btn btn-outline-dark">Ver Catálogo Completo (G3)</button>
    </div>
</div>
<?php
use yii\helpers\Html;

$this->title = 'Sala de Juegos';
?>

<div class="site-lobby">
    
    <div class="text-center" style="margin-bottom: 40px;">
        <h1 style="color: #333; font-weight: bold;">🎰 Sala de Juegos</h1>
        <p class="lead">Explora nuestro catálogo y encuentra tu favorito.</p>
        
        <input type="text" id="buscador-juegos" class="form-control" placeholder="🔍 Buscar juego por nombre, temática o proveedor..." style="max-width: 500px; margin: 0 auto; padding: 20px; font-size: 1.2em; border-radius: 30px;">
    </div>

    <div class="row" id="contenedor-juegos">
        
        <?php foreach ($juegos as $juego): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4 juego-item" 
                 data-nombre="<?= strtolower($juego->nombre) ?>" 
                 data-tematica="<?= strtolower($juego->tematica) ?>"
                 data-proveedor="<?= strtolower($juego->proveedor) ?>">
                
                <div class="card h-100 shadow-sm" style="border-radius: 15px; overflow: hidden; transition: transform 0.2s;">
                    
                    <div style="height: 200px; overflow: hidden; background: #000;">
                        <?php if($juego->url_caratula): ?>
                            <?= Html::img('@web/' . $juego->url_caratula, ['class' => 'card-img-top', 'style' => 'width: 100%; height: 100%; object-fit: cover;']) ?>
                        <?php else: ?>
                            <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: white;">Sin Imagen</div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body text-center">
                        <h5 class="card-title font-weight-bold" style="color: #0056b3;"><?= Html::encode($juego->nombre) ?></h5>
                        <p class="card-text small text-muted">
                            <?= Html::encode($juego->proveedor) ?> | RTP: <?= $juego->rtp ?>%
                        </p>
                        
                        <div style="margin-bottom: 10px;">
                            <span class="badge badge-info"><?= Html::encode($juego->tipo) ?></span>
                            <?php if($juego->es_nuevo): ?>
                                <span class="badge badge-warning">¡NUEVO!</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($juego->en_mantenimiento): ?>
                            
                            <button class="btn btn-secondary btn-block" disabled style="border-radius: 20px; cursor: not-allowed;">
                                🛠️ EN MANTENIMIENTO
                            </button>
                        
                        <?php else: ?>
                            
                            <?= Html::a('¡JUGAR AHORA!', ['juego/jugar', 'id' => $juego->id], [
                                'class' => 'btn btn-success btn-block',
                                'style' => 'border-radius: 20px;'
                            ]) ?>
                            
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div id="no-resultados" class="col-12 text-center" style="display: none; margin-top: 50px;">
            <h3>No encontramos juegos que coincidan.</h3>
        </div>

    </div>
</div>

<script>
document.getElementById('buscador-juegos').addEventListener('keyup', function() {
    let filtro = this.value.toLowerCase();
    let tarjetas = document.querySelectorAll('.juego-item');
    let hayResultados = false;

    tarjetas.forEach(function(tarjeta) {
        // Obtenemos los datos ocultos en el HTML
        let nombre = tarjeta.getAttribute('data-nombre');
        let tematica = tarjeta.getAttribute('data-tematica');
        let proveedor = tarjeta.getAttribute('data-proveedor');

        // Si coincide con algo, lo mostramos
        if (nombre.includes(filtro) || tematica.includes(filtro) || proveedor.includes(filtro)) {
            tarjeta.style.display = ''; // Mostrar
            hayResultados = true;
        } else {
            tarjeta.style.display = 'none'; // Ocultar
        }
    });

    // Control del mensaje "No hay resultados"
    document.getElementById('no-resultados').style.display = hayResultados ? 'none' : 'block';
});
</script>
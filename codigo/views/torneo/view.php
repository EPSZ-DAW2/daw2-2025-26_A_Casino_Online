<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Torneo */
/* @var $participantes app\models\ParticipacionTorneo[] */

$this->title = $model->titulo;
?>
<div class="torneo-view container">

    <div class="jumbotron text-center bg-dark text-white p-4 mb-4 rounded shadow">
        <h1 class="display-4 font-weight-bold"><?= Html::encode($this->title) ?></h1>
        
        <p class="lead">
            Juego: <strong class="text-info"><?= $model->juego ? $model->juego->nombre : 'Varios' ?></strong> | 
            Premio: <span class="text-warning font-weight-bold display-5"><?= number_format($model->bolsa_premios, 0) ?> €</span>
        </p>
        
        <div class="my-3">
            <?php if($model->estado === 'Abierto'): ?>
                <span class="badge badge-success p-2" style="font-size: 1rem;">Inscripciones Abiertas</span>
                
                <div class="mt-4">
                    <?= Html::a('⚠ Cancelar Torneo y Devolver Dinero', 
                        ['cancelar', 'id' => $model->id], 
                        [
                            'class' => 'btn btn-outline-danger font-weight-bold',
                            'data' => [
                                'confirm' => '¿ESTÁS SEGURO? Se cancelará el torneo y se devolverá el dinero a TODOS los inscritos automáticamente. Esta acción no se puede deshacer.',
                                'method' => 'post',
                            ],
                        ]
                    ) ?>
                </div>

            <?php elseif($model->estado === 'En Curso'): ?>
                <span class="badge badge-danger p-2" style="font-size: 1rem;">🔴 EN JUEGO - EN VIVO</span>
            
            <?php elseif($model->estado === 'Cancelado'): ?>
                <div class="alert alert-danger mt-3 font-weight-bold d-inline-block">
                    ⛔ TORNEO CANCELADO (Dinero devuelto)
                </div>

            <?php else: ?>
                <span class="badge badge-secondary p-2" style="font-size: 1rem;"><?= $model->estado ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="mb-0 text-primary">🏆 Clasificación Actual</h3>
        </div>
        
        <div class="card-body p-0">
            <?php if (count($participantes) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" width="10%" class="text-center">Pos</th>
                                <th scope="col">Jugador</th>
                                <th scope="col" class="text-right pr-4">Puntos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $posicion = 1;
                            foreach ($participantes as $participacion): 
                                // Lógica para iconos de medallas
                                $medalla = '<span class="badge badge-secondary badge-pill">' . $posicion . '</span>';
                                $claseFila = '';
                                
                                if ($posicion == 1) {
                                    $medalla = '🥇';
                                    $claseFila = 'table-warning font-weight-bold';
                                } elseif ($posicion == 2) {
                                    $medalla = '🥈';
                                } elseif ($posicion == 3) {
                                    $medalla = '🥉';
                                }
                            ?>
                                <tr class="<?= $claseFila ?>">
                                    <td class="align-middle h4 text-center"><?= $medalla ?></td>
                                    <td class="align-middle">
                                        <?php 
                                            $nombreAvatar = $participacion->usuario->avatar_url;
                                                                
                                            // 1. Si no tiene avatar o el campo está vacío, usamos uno por defecto
                                            if (empty($nombreAvatar)) {
                                                $nombreAvatar = 'default.png'; 
                                            }
                                        
                                            // 2. Construimos la ruta
                                            $rutaAvatar = Url::to('@web/img/' . $nombreAvatar);
                                        ?>
                                        <img src="<?= $rutaAvatar ?>" 
                                             alt="👤" 
                                             class="rounded-circle mr-2 shadow-sm" 
                                             style="width: 40px; height: 40px; object-fit: cover; background-color: #eee;">
                                                                            
                                        <span style="font-size: 1.1rem;">
                                            <?= Html::encode($participacion->usuario->nick) ?>
                                        </span>
                                    </td>
                                    <td class="text-right align-middle h5 text-primary pr-4">
                                        <?= number_format($participacion->puntuacion_actual, 0) ?> pts
                                    </td>
                                </tr>
                            <?php 
                                $posicion++;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center m-4">
                    <h4>Aún no hay valientes inscritos en este torneo.</h4>
                    <p>¡Sé el primero en participar!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 mb-5">
        <?= Html::a('⬅ Volver al Listado', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

</div>
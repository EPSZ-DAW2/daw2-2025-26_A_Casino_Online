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

    <div class="jumbotron text-center bg-dark text-white p-5 mb-4 rounded shadow" style="background: linear-gradient(135deg, #1f1c2c 0%, #928DAB 100%);">
        <h1 class="display-4 font-weight-bold"><?= Html::encode($this->title) ?></h1>
        
        <p class="lead mt-3">
            Juego: <strong class="text-info"><?= $model->juegoAsociado ? Html::encode($model->juegoAsociado->nombre) : 'Varios' ?></strong> 
            <span class="mx-3">|</span> 
            Bote de Premios: <span class="text-warning font-weight-bold" style="font-size: 1.5em;"><?= number_format($model->bolsa_premios, 0) ?> €</span>
        </p>
        
        <div class="my-4">
            <?php 
            $yaInscrito = false;
            if (!Yii::$app->user->isGuest) {
                $yaInscrito = \app\models\ParticipacionTorneo::find()
                    ->where(['id_torneo' => $model->id, 'id_usuario' => Yii::$app->user->id])
                    ->exists();
            }
            ?>

            <?php if ($yaInscrito): ?>
                
                <?php if ($model->estado === 'En Curso'): ?>
                    <div class="alert alert-success d-inline-block px-5 py-3 shadow">
                        <h4 class="alert-heading">🔥 ¡El torneo está EN VIVO!</h4>
                        <p class="mb-3">Entra ahora y acumula puntos.</p>
                        <?= Html::a('🎮 JUGAR AHORA', 
                            ['/juego/jugar', 'id' => $model->id_juego_asociado, 'id_torneo' => $model->id], 
                            ['class' => 'btn btn-lg btn-success font-weight-bold pulse-button']
                        ) ?>
                    </div>

                <?php elseif ($model->estado === 'Abierto'): ?>
                    <div class="alert alert-info d-inline-block px-5 py-3">
                        <h4 class="alert-heading">✅ Pre-inscripción Correcta</h4>
                        <p class="mb-0">Ya tienes tu plaza. Espera a que el administrador inicie el torneo para jugar.</p>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                
                <?php if ($model->estado === 'En Curso'): ?>
                    <span class="badge bg-danger p-2 mb-3 spinner-grow-sm">🔴 Torneo En Vivo - ¡Únete ya!</span>
                    <br>
                    <?= Html::a('⚡ Pagar Entrada (' . $model->coste_entrada . '€) y Jugar', 
                        ['unirse', 'id' => $model->id], 
                        [
                            'class' => 'btn btn-danger btn-lg shadow',
                            'data' => [
                                'confirm' => 'El torneo ya ha empezado. ¿Quieres pagar ' . $model->coste_entrada . '€ y entrar ahora mismo?',
                                'method' => 'post',
                            ],
                        ]
                    ) ?>

                <?php elseif ($model->estado === 'Abierto'): ?>
                    <span class="badge bg-success p-2 mb-3">Inscripciones Abiertas</span>
                    <br>
                    <?= Html::a('🎟️ Pre-inscribirse (' . $model->coste_entrada . '€)', 
                        ['unirse', 'id' => $model->id], 
                        [
                            'class' => 'btn btn-primary btn-lg shadow',
                            'data' => [
                                'confirm' => '¿Confirmas el pago de ' . $model->coste_entrada . '€ para reservar tu plaza?',
                                'method' => 'post',
                            ],
                        ]
                    ) ?>
                <?php endif; ?>

            <?php endif; ?>

            <?php if ($model->estado === 'Finalizado' || $model->estado === 'Cancelado'): ?>
                <div class="alert alert-secondary d-inline-block">
                    Este torneo está <strong><?= strtoupper($model->estado) ?></strong>.
                </div>
            <?php endif; ?>

            <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->esAdmin()): ?>
                <hr class="border-light mt-4">
                <div class="btn-group">
                    <?= Html::a('✏ Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-light btn-sm']) ?>
                    <?php if ($model->estado !== 'Cancelado' && $model->estado !== 'Finalizado'): ?>
                        <?= Html::a('🛑 Finalizar Torneo', ['finalizar', 'id' => $model->id], [
                            'class' => 'btn btn-warning btn-sm',
                            'data' => ['confirm' => '¿Cerrar torneo y dar premios?', 'method' => 'post']
                        ]) ?>
                        <?= Html::a('⚠ Cancelar', ['cancelar', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => ['confirm' => '¿CANCELAR TORNEO? Se devolverá el dinero.', 'method' => 'post']
                        ]) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h3 class="text-center mb-4 border-bottom pb-2">🏆 Clasificación en Tiempo Real</h3>
            
            <?php if (!empty($participantes)): ?>
                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th scope="col" class="text-center">#</th>
                                <th scope="col">Jugador</th>
                                <th scope="col" class="text-end">Puntuación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $posicion = 1;
                            // Ordenamos los participantes por puntuación descendente
                            usort($participantes, function($a, $b) {
                                return $b->puntuacion_actual <=> $a->puntuacion_actual;
                            });

                            foreach ($participantes as $participacion): 
                                // Estilos para el podio (1º Oro, 2º Plata, 3º Bronce)
                                $medalla = '';
                                $claseFila = '';
                                if ($posicion == 1) { $medalla = '🥇'; $claseFila = 'table-warning font-weight-bold'; }
                                elseif ($posicion == 2) { $medalla = '🥈'; }
                                elseif ($posicion == 3) { $medalla = '🥉'; }
                            ?>
                                <tr class="<?= $claseFila ?>">
                                    <td class="text-center align-middle h5"><?= $posicion == 1 ? $medalla : $posicion ?></td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center mr-3" style="width: 40px; height: 40px; margin-right:10px;">
                                                <?= strtoupper(substr($participacion->usuario->nick, 0, 1)) ?>
                                            </div>
                                            <span style="font-size: 1.1rem;">
                                                <?= Html::encode($participacion->usuario->nick) ?>
                                            </span>
                                            <?php if ($posicion == 1): ?>
                                                <span class="badge bg-danger ms-2" style="font-size: 0.7em;">LÍDER</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end align-middle h5 text-primary pr-4">
                                        <?= number_format($participacion->puntuacion_actual, 0, ',', '.') ?> pts
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
                <div class="alert alert-info text-center m-4 p-5 shadow-sm">
                    <h4>Aún no hay valientes inscritos en este torneo.</h4>
                    <p class="mb-0">¡Sé el primero en participar y lidera la tabla!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-5 mb-5 text-center">
        <?= Html::a('⬅ Volver al Listado de Torneos', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

</div>

<style>
@keyframes pulse {
	0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
	70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
	100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}
.pulse-button {
	animation: pulse 2s infinite;
}
</style>
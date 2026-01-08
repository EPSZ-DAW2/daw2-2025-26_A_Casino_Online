<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\MensajeChat;

/** @var yii\web\View $this */
/** @var app\models\MesaPrivada $mesa */
/** @var app\models\MensajeChat $chatModel */
/** @var app\models\MensajeChat[] $mensajes */
/** @var app\models\Juego|null $juegoAsociado */

$this->title = 'Mesa: ' . $mesa->tipo_juego;
// CSS básico para el chat
$this->registerCss("
    .chat-container { height: 400px; overflow-y: auto; background-color: #f8f9fa; border: 1px solid #dee2e6; }
    .chat-message { margin-bottom: 10px; padding: 5px 10px; border-radius: 10px; }
    .chat-mine { background-color: #d1e7dd; text-align: right; margin-left: auto; width: fit-content; max-width: 80%; }
    .chat-other { background-color: #e2e3e5; text-align: left; margin-right: auto; width: fit-content; max-width: 80%; }
    .game-area { background-color: #2c3e50; height: 500px; color: white; display: flex; align-items: center; justify-content: center; border-radius: 1rem; }
");
?>
<div class="mesa-privada-room container-fluid">

    <div class="row">
        <!-- ÁREA DE JUEGO (IZQUIERDA) -->
        <div class="col-md-8">
            <div class="card shadow mb-3">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">🎲 Zona de Juego:
                        <?= Html::encode($mesa->tipo_juego) ?> (Anfitrión:
                        <?= Html::encode($mesa->anfitrion->nick) ?>)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="game-area" style="height: 600px; overflow: hidden; background: #000;">
                        <?php if ($juegoAsociado): ?>
                            <!-- INTEGRACIÓN EXITOSA: Cargamos el juego real mediante IFRAME -->
                            <iframe src="<?= \yii\helpers\Url::to(['juego/jugar', 'id' => $juegoAsociado->id]) ?>"
                                style="width: 100%; height: 100%; border: none;" title="Juego de Casino">
                            </iframe>
                        <?php else: ?>
                            <!-- FALLBACK: Si no se encuentra un juego compatible -->
                            <div class="text-center p-5">
                                <h3 class="text-warning"><i class="bi bi-exclamation-triangle"></i> Módulo de Juego no
                                    detectado</h3>
                                <p>No se ha encontrado un juego en el catálogo llamado
                                    <strong>"<?= Html::encode($mesa->tipo_juego) ?>"</strong>.
                                </p>
                                <p class="text-muted small">Prueba a crear una mesa con nombre: <em>Blackjack, Ruleta,
                                        Slots...</em></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::a('Abandonar Mesa', ['index'], ['class' => 'btn btn-danger btn-sm']) ?>
                </div>
            </div>
        </div>

        <!-- ÁREA DE CHAT (DERECHA) -->
        <div class="col-md-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">💬 Chat de Sala</h5>
                </div>

                <!-- Historial de Mensajes -->
                <div class="card-body chat-container" id="chat-box">
                    <?php if (empty($mensajes)): ?>
                        <div class="text-center text-muted mt-5"><i>Inicia la conversación...</i></div>
                    <?php else: ?>
                        <?php foreach ($mensajes as $msg): ?>
                            <?php
                            $isMine = ($msg->id_usuario === Yii::$app->user->id);
                            $class = $isMine ? 'chat-mine' : 'chat-other';
                            $sender = $isMine ? 'Tú' : $msg->usuario->nick;
                            ?>
                            <div class="chat-message <?= $class ?>">
                                <small class="fw-bold">
                                    <?= Html::encode($sender) ?>
                                </small><br>
                                <?= Html::encode($msg->mensaje) ?>
                                <div style="font-size:0.7em; color:#666">
                                    <?= Yii::$app->formatter->asTime($msg->fecha_envio, 'short') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Input para enviar -->
                <div class="card-footer">
                    <?php $form = ActiveForm::begin(['options' => ['class' => 'd-flex']]); ?>
                    <?= $form->field($chatModel, 'mensaje')->textInput([
                        'placeholder' => 'Escribe aquí...',
                        'class' => 'form-control me-2',
                        'autocomplete' => 'off'
                    ])->label(false) ?>

                    <?= Html::submitButton('<i class="bi bi-send-fill"></i>', ['class' => 'btn btn-primary']) ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para bajar el scroll del chat automáticamente -->
<?php
$this->registerJs('
    var chatBox = document.getElementById("chat-box");
    chatBox.scrollTop = chatBox.scrollHeight;
');
?>
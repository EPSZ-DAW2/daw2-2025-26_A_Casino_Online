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
                    <!-- Los mensajes se cargarán vía JS -->
                </div>

                <!-- Input para enviar -->
                <div class="card-footer">
                    <form id="chat-form" class="d-flex" onsubmit="enviarMensaje(event)">
                        <input type="text" id="mensaje-input" class="form-control me-2" placeholder="Escribe aquí..."
                            autocomplete="off">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para el Chat AJAX (G6) -->
<script>
    const mesaId = <?= $mesa->id ?>;
    let lastMessageId = 0;
    const chatBox = document.getElementById("chat-box");

    // Función: Cargar mensajes nuevos
    function cargarMensajes() {
        console.log('Poll');
        fetch(`index.php?r=mesa-privada/get-mensajes&id=${mesaId}&lastId=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    let hayNuevos = false;
                    data.forEach(msg => {
                        // Actualizamos el último ID conocido
                        if (msg.id > lastMessageId) {
                            lastMessageId = msg.id;
                            hayNuevos = true;
                            appendMessage(msg);
                        }
                    });

                    if (hayNuevos) scrollToBottom();
                }
            })
            .catch(error => console.error('Error polling:', error));
    }

    // Función: Pintar un mensaje en el chat
    function appendMessage(msg) {
        const div = document.createElement('div');
        const clase = msg.es_mio ? 'chat-mine' : 'chat-other';
        const sender = msg.es_mio ? 'Tú' : msg.autor;

        div.className = `chat-message ${clase}`;
        div.innerHTML = `
            <small class="fw-bold">${sender}</small><br>
            ${msg.contenido}
            <div style="font-size:0.7em; color:#666">${msg.hora}</div>
        `;
        chatBox.appendChild(div);
    }

    // Función: Enviar mensaje
    function enviarMensaje(e) {
        e.preventDefault();
        const input = document.getElementById('mensaje-input');
        const contenido = input.value.trim();

        if (!contenido) return;

        // Limpiamos input inmediatamente para UX rápida
        input.value = '';

        const formData = new FormData();
        formData.append('contenido', contenido);
        formData.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->getCsrfToken() ?>');

        fetch(`index.php?r=mesa-privada/enviar-mensaje&id=${mesaId}`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Forzamos carga inmediata para ver mi propio mensaje
                    cargarMensajes();
                } else {
                    alert('Error al enviar mensaje');
                }
            })
            .catch(error => console.error('Error enviando:', error));
    }

    // Scroll al fondo
    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Polling cada 2 segundos
    setInterval(cargarMensajes, 2000);

    // Carga inicial
    cargarMensajes();
</script>
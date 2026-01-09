<?php
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Mi Monedero Virtual';
?>

<div class="monedero-index container mt-4">
    <h4 class="mb-3 mt-4">Gestión de Fondos (Ingresos y Retiradas)</h4>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100 border-primary shadow-sm" style="cursor: pointer;"
                onclick="gestionarFlujo('Ingreso', 'Tarjeta')">
                <div class="card-body text-center">
                    <i class="bi bi-credit-card-2-back display-4 text-primary"></i>
                    <h5 class="card-title mt-2">Tarjeta Bancaria</h5>
                    <p class="text-muted small">Ingreso inmediato</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-success shadow-sm" style="cursor: pointer;"
                onclick="gestionarFlujo('Ingreso', 'Bizum')">
                <div class="card-body text-center">
                    <i class="bi bi-phone-vibrate display-4 text-success"></i>
                    <h5 class="card-title mt-2">Bizum</h5>
                    <p class="text-muted small">Rápido desde tu móvil</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-danger shadow-sm" style="cursor: pointer;"
                onclick="gestionarFlujo('Retirada')">
                <div class="card-body text-center">
                    <i class="bi bi-bank display-4 text-danger"></i>
                    <h5 class="card-title mt-2">Retirar Fondos</h5>
                    <p class="text-muted small">Cobrar saldo real</p>
                </div>
            </div>
        </div>
    </div>

    <div id="panel-operacion" style="display:none;"
        class="card p-4 bg-light mb-5 border-0 shadow-lg border-start border-5">
        <h5 id="titulo-operacion" class="fw-bold"></h5>
        <p id="info-operacion" class="text-muted"></p>

        <div class="row g-3 mt-2">
            <div class="col-md-3">
                <label class="form-label fw-bold">Cantidad (€):</label>
                <input type="number" id="input-cantidad" class="form-control" value="10" min="1">
            </div>

            <div class="col-md-5" id="grupo-dato-pago">
                <label id="label-dato" class="form-label fw-bold">Número de Tarjeta:</label>
                <input type="text" id="input-dato-pago" class="form-control" placeholder="0000 0000 0000 0000">
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-primary w-100" onclick="ejecutarOperacion()">Confirmar
                    Pago</button>
                <button type="button" class="btn btn-secondary ms-2"
                    onclick="document.getElementById('panel-operacion').style.display='none'">X</button>
            </div>
        </div>
    </div>

    <script>
        /**
        * VARIABLES DE ESTADO: Mantienen el rastro de qué queremos hacer.
        */
        let operacionActual = '';
        let metodoActual = '';

        /**
        * FUNCIÓN gestionarFlujo (W2):
        * Modifica el DOM en tiempo real para adaptar el formulario al método de pago.
        */
        function gestionarFlujo(tipo, metodo = '') {
            operacionActual = tipo;
            metodoActual = metodo;
            const panel = document.getElementById('panel-operacion');
            const grupoDato = document.getElementById('grupo-dato-pago');
            const labelDato = document.getElementById('label-dato');
            const inputDato = document.getElementById('input-dato-pago');

            panel.style.display = 'block'; // Mostrar el formulario

            if (tipo === 'Ingreso') {
                grupoDato.style.display = 'block';
                if (metodo === 'Tarjeta') {
                    labelDato.innerText = 'Número de Tarjeta (16 dígitos):';
                    inputDato.placeholder = 'XXXX XXXX XXXX XXXX';
                } else {
                    labelDato.innerText = 'Número de Teléfono Bizum:';
                    inputDato.placeholder = '600 000 000';
                }
                document.getElementById('titulo-operacion').innerText = 'Depositar vía ' + metodo;
            } else {
                // Lógica de Retirada: Ocultamos el campo de tarjeta/tlf ya que se asume transferencia
                grupoDato.style.display = 'none'; // Para retirar no pedimos número aquí
                document.getElementById('titulo-operacion').innerText = 'Solicitar Retirada';
            }
        }

        /**
        * FUNCIÓN ejecutarOperacion (G2):
        * Recopila los datos y redirige al controlador de Yii2 con los parámetros necesarios.
        */
        function ejecutarOperacion() {
            const cantidad = document.getElementById('input-cantidad').value;
            const datoExtra = document.getElementById('input-dato-pago').value;

            // Validaciones básicas de cliente
            if (cantidad <= 0) return alert('Cantidad no válida');
            if (operacionActual === 'Ingreso' && !datoExtra) return alert('Por favor, introduce los datos de pago');

            // Construcción de la URL de Yii2 incluyendo cantidad, método y el dato (Tarjeta/Tlf)
            let url = (operacionActual === 'Ingreso')
                ? '<?= \yii\helpers\Url::to(['depositar']) ?>&cantidad=' + cantidad + '&metodo=' + metodoActual + '&dato=' + datoExtra
                : '<?= \yii\helpers\Url::to(['retirar']) ?>&cantidad=' + cantidad;

            window.location.href = url;
        }
    </script>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h4 class="mb-0">Historial de Transacciones</h4>
        </div>
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover mb-0'],
                'summary' => false,
                'columns' => [
                    [
                        'attribute' => 'tipo_operacion',
                        'format' => 'raw',
                        'value' => function ($model) {
                                        // Iconografía dinámica para identificar el tipo de movimiento visualmente
                                        $icon = $model->tipo_operacion == 'Deposito' ? 'arrow-up-circle-fill text-success' : 'arrow-down-circle-fill text-danger';
                                        if ($model->tipo_operacion == 'Premio')
                                            $icon = 'trophy-fill text-warning';
                                        return "<i class='bi bi-$icon'></i> " . $model->tipo_operacion;
                                    }
                    ],
                    'cantidad:currency', // Formateado automático a moneda
                    'metodo_pago',
                    [
                        'attribute' => 'estado',
                        'format' => 'raw',
                        'value' => function ($model) {
                                        // Badge de colores: verde (success), rojo (danger) o amarillo (warning)
                                        $class = 'warning'; // Por defecto pendiente
                                        if ($model->estado == 'Completado')
                                            $class = 'success';
                                        if ($model->estado == 'Rechazado')
                                            $class = 'danger';

                                        return "<span class='badge bg-$class'>$model->estado</span>";
                                    }
                    ],
                    'fecha_hora:datetime',
                ],
            ]); ?>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-6 offset-md-3">
            <div class="card shadow">
                <div class="card-header bg-dark text-white text-center">
                    <h5 class="mb-0">Distribución de Gasto Mensual</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficaGasto" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('graficaGasto');
        const datos = <?= json_encode($datosGrafica) ?>;

        if (!datos || datos.length === 0) {
            // Si no hay datos, mostrar mensaje
            ctx.replaceWith("No hay datos de gastos registrados.");
        } else {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: datos.map(d => d.categoria || 'Sin categoría'),
                    datasets: [{
                        data: datos.map(d => d.cantidad),
                        backgroundColor: ['#dc3545', '#198754', '#0d6efd', '#ffc107']
                    }]
                },
                options: { responsive: true }
            });
        }
    </script>
</div>
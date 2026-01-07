<?php

namespace app\controllers;

use Yii;
use app\models\Juego;
use app\models\JuegoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * JuegoController implements the CRUD actions for Juego model.
 */
class JuegoController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Juego models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new JuegoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Juego model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Juego model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Juego();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                // 1. Instanciar la imagen
                $model->archivoImagen = UploadedFile::getInstance($model, 'archivoImagen');

                // 2. IMPORTANTE: Validamos el modelo AQUÍ, mientras la imagen sigue en la carpeta temporal
                if ($model->validate()) {

                    // Si la validación pasa, procedemos a mover el archivo
                    if ($model->archivoImagen) {
                        $nombreArchivo = 'juego_' . time() . '_' . $model->archivoImagen->baseName . '.' . $model->archivoImagen->extension;
                        $rutaCarpeta = Yii::getAlias('@webroot') . '/uploads/';

                        // Guardamos el archivo físico
                        if ($model->archivoImagen->saveAs($rutaCarpeta . $nombreArchivo)) {
                            $model->url_caratula = 'uploads/' . $nombreArchivo;
                        }
                    }

                    // 3. Guardamos en BD poniendo 'false' para que NO valide de nuevo (evita el error de archivo no encontrado)
                    $model->save(false);

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Juego model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Juego model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Juego model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Juego the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Juego::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Muestra el catálogo público de juegos (El Lobby)
     */
    public function actionLobby()
    {
        // 1. Buscamos solo los juegos que estén marcados como 'activo'
        $juegos = Juego::find()
            ->where(['activo' => 1])
            ->all();

        // 2. Renderizamos la vista 'lobby' (que crearemos ahora)
        return $this->render('lobby', [
            'juegos' => $juegos,
        ]);
    }

    /**
     * Pantalla de Juego Individual (La Sala)
     */
    public function actionJugar($id)
    {
        $model = $this->findModel($id);

        // --- SEGURIDAD: SI ESTÁ EN MANTENIMIENTO O DESACTIVADO, EXPULSAR ---
        if ($model->en_mantenimiento == 1 || $model->activo == 0) {

            Yii::$app->session->setFlash('error', 'El juego "' . $model->nombre . '" está en mantenimiento.');
            return $this->redirect(['lobby']);
        }
        // -------------------------------------------------------------------

        if (Yii::$app->user->identity->monedero) {
            $saldo = Yii::$app->user->identity->monedero->saldo_real;
        } else {
            $saldo = 0.00; // Si no tiene monedero, saldo 0 para que no rompa
        }

        $this->layout = false;
        return $this->render('jugar', [
            'model' => $model,
            'saldo' => $saldo
        ]);
    }

    /**
     * Juego del slot la tragamonedas
     * Se llama mediante AJAX desde la vista 'jugar.php'.
     */
    public function actionApiGirarSlot($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Cargamos el Juego y el Usuario
        $juego = $this->findModel($id);
        $usuario = Yii::$app->user->identity;
        $monedero = $usuario->monedero;

        // Coste de la tirada, 
        $costeTirada = 1.00;

        // Validaciones de Seguridad
        if (!$monedero || $monedero->saldo_real < $costeTirada) {
            return ['success' => false, 'mensaje' => 'Saldo insuficiente. Recarga tu cuenta.'];
        }

        // Cobramos la entrada
        $monedero->saldo_real -= $costeTirada;

        // LÓGICA DEL JUEGO 
        // Definimos los símbolos posibles
        $simbolos = ['🍒', '🍋', '🍇', '💎', '🔔'];
        //Todos tienen la misma probabilidad ahora mismo

        $resultado = [
            $simbolos[array_rand($simbolos)], // Rodillo 1
            $simbolos[array_rand($simbolos)], // Rodillo 2
            $simbolos[array_rand($simbolos)]  // Rodillo 3
        ];

        // 5. Comprobar si ha ganado
        $ganancia = 0;
        $esVictoria = false;

        // Regla: Si los 3 símbolos son iguales
        if ($resultado[0] === $resultado[1] && $resultado[1] === $resultado[2]) {
            $esVictoria = true;

            // Tabla de Pagos simple
            switch ($resultado[0]) {
                case '💎':
                    $ganancia = 50.00;
                    break; // Jackpot
                case '🔔':
                    $ganancia = 20.00;
                    break;
                default:
                    $ganancia = 5.00;
                    break; // Frutas normales
            }
        }
        // Si salen dos cerezas al principio
        elseif ($resultado[0] === '🍒' && $resultado[1] === '🍒') {
            $esVictoria = true;
            $ganancia = 2.00; // Premio consuelo
        }

        // Si ganó, le pagamos
        if ($esVictoria) {
            $monedero->saldo_real += $ganancia;
        }

        // Guardamos el nuevo saldo en la BD
        $monedero->save();

        // 7. Devolvemos el resultado al juego (JS)
        return [
            'success' => true,
            'rodillos' => $resultado, // Ej: ['🍒', '💎', '🍒']
            'premio' => $ganancia,
            'nuevoSaldo' => $monedero->saldo_real,
            'esVictoria' => $esVictoria
        ];
    }

    /* * Procesa una tirada de Ruleta
     * Recibe por POST: 
     * - 'tipoApuesta' (numero, color, paridad)
     * - 'valorApuesta' (17, rojo, par)
     * - 'cantidadApuesta' (El dinero que el usuario quiere arriesgar)
     */
    public function actionApiGirarRuleta($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;

        // Recogemos los datos y la cantidad apostada
        $tipoApuesta = $request->post('tipoApuesta');
        $valorApuesta = $request->post('valorApuesta');

        // Convertimos a float y si no envían nada, asumimos 1€ por seguridad
        $cantidadApuesta = (float) $request->post('cantidadApuesta', 1.00);

        // Evitar apuestas negativas o cero
        if ($cantidadApuesta <= 0) {
            return ['success' => false, 'mensaje' => 'La apuesta debe ser mayor a 0.'];
        }
        //Límite máximo de apuesta para no arruinar la banca
        if ($cantidadApuesta > 1000) {
            return ['success' => false, 'mensaje' => 'El límite máximo de apuesta es 1000€.'];
        }

        // Cargar Usuario y Monedero
        $juego = $this->findModel($id);
        $usuario = Yii::$app->user->identity;
        $monedero = $usuario->monedero;

        // Comprobamos si tiene saldo para esa cantidad específica
        if (!$monedero || $monedero->saldo_real < $cantidadApuesta) {
            return ['success' => false, 'mensaje' => 'Saldo insuficiente para esta apuesta.'];
        }

        // Cobrar la apuesta 
        $monedero->saldo_real -= $cantidadApuesta;

        // girar la ruleta
        $numeroGanador = rand(0, 36);

        // Lógica de colores
        $rojos = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
        $colorGanador = 'verde';
        if (in_array($numeroGanador, $rojos))
            $colorGanador = 'rojo';
        elseif ($numeroGanador != 0)
            $colorGanador = 'negro';

        $paridadGanadora = ($numeroGanador != 0 && $numeroGanador % 2 == 0) ? 'par' : 'impar';

        //Calcular Ganancia 
        $ganancia = 0;
        $esVictoria = false;

        switch ($tipoApuesta) {
            case 'numero':
                if ($numeroGanador == intval($valorApuesta)) {
                    $esVictoria = true;
                    // Pleno: Paga 35 a 1 + la apuesta (Total x36)
                    $ganancia = $cantidadApuesta * 36;
                }
                break;

            case 'color':
                if ($colorGanador == $valorApuesta) {
                    $esVictoria = true;
                    // Color: Paga 1 a 1 (Doblas la apuesta)
                    $ganancia = $cantidadApuesta * 2;
                }
                break;

            case 'paridad':
                if ($numeroGanador != 0 && $paridadGanadora == $valorApuesta) {
                    $esVictoria = true;
                    // Par/Impar: Paga 1 a 1
                    $ganancia = $cantidadApuesta * 2;
                }
                break;
        }

        // 6. Pagar y Guardar
        if ($esVictoria) {
            $monedero->saldo_real += $ganancia;
        }
        $monedero->save();

        // 7. Devolver resultado (añadimos la cantidad para feedback visual)
        return [
            'success' => true,
            'numero' => $numeroGanador,
            'color' => $colorGanador,
            'nuevoSaldo' => $monedero->saldo_real,
            'esVictoria' => $esVictoria,
            'premio' => $ganancia,
            'apuesta' => $cantidadApuesta // Devolvemos el dato para mostrarlo
        ];
    }

    // ==========================================
    // LÓGICA DEL BLACKJACK 
    // ==========================================

    /**
     * Genera una carta aleatoria 
     */
    private function generarCarta()
    {
        $palos = ['♥', '♦', '♣', '♠'];
        $valores = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        $palo = $palos[array_rand($palos)];
        $valor = $valores[array_rand($valores)];

        // Calculamos el valor numérico
        $puntos = 0;
        if (is_numeric($valor)) {
            $puntos = intval($valor);
        } elseif ($valor === 'A') {
            $puntos = 11; // El As vale 11 por defecto (se ajusta luego)
        } else {
            $puntos = 10; // J, Q, K valen 10
        }

        return ['palo' => $palo, 'valor' => $valor, 'puntos' => $puntos];
    }

    /**
     * Calcula el total de una mano ajustando los Ases
     */
    private function calcularMano($cartas)
    {
        $total = 0;
        $ases = 0;

        foreach ($cartas as $c) {
            $total += $c['puntos'];
            if ($c['valor'] === 'A')
                $ases++;
        }

        // Si nos pasamos de 21 y tenemos Ases, los convertimos de 11 a 1
        while ($total > 21 && $ases > 0) {
            $total -= 10;
            $ases--;
        }

        return $total;
    }

    /**
     * INicio de la partida
     */
    public function actionApiBlackjackIniciar($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $session = Yii::$app->session;

        // Cobrar apuesta
        $cantidad = (float) Yii::$app->request->post('cantidadApuesta', 1.00);
        $usuario = Yii::$app->user->identity;
        $monedero = $usuario->monedero;

        // Evitar apuestas negativas o cero
        if ($cantidad <= 0) {
            return ['success' => false, 'mensaje' => 'La apuesta debe ser mayor a 0.'];
        }
        //Límite máximo de apuesta para no arruinar la banca
        if ($cantidad > 1000) {
            return ['success' => false, 'mensaje' => 'El límite máximo de apuesta es 1000€.'];
        }

        if (!$monedero || $monedero->saldo_real < $cantidad) {
            return ['success' => false, 'mensaje' => 'Saldo insuficiente.'];
        }
        $monedero->saldo_real -= $cantidad;
        $monedero->save();

        // Repartir cartas iniciales
        $manoJugador = [$this->generarCarta(), $this->generarCarta()];
        $manoDealer = [$this->generarCarta()]; // El dealer solo enseña 1 al principio

        // Guardar en sesión
        $session->set('bj_mano_jugador', $manoJugador);
        $session->set('bj_mano_dealer', $manoDealer);
        $session->set('bj_apuesta', $cantidad);
        $session->set('bj_juego_id', $id);

        return [
            'success' => true,
            'manoJugador' => $manoJugador,
            'puntosJugador' => $this->calcularMano($manoJugador),
            'manoDealer' => $manoDealer,
            'nuevoSaldo' => $monedero->saldo_real
        ];
    }

    /**
     * Pedir carta
     */
    public function actionApiBlackjackPedir()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $session = Yii::$app->session;

        $manoJugador = $session->get('bj_mano_jugador');

        // Dar carta
        $nuevaCarta = $this->generarCarta();
        $manoJugador[] = $nuevaCarta;
        $puntos = $this->calcularMano($manoJugador);

        // Actualizar sesión
        $session->set('bj_mano_jugador', $manoJugador);

        // Comprobar si se ha pasado 
        if ($puntos > 21) {
            return [
                'success' => true,
                'terminado' => true,
                'victoria' => false,
                'mensaje' => '¡Te pasaste! (Total: ' . $puntos . ')',
                'manoJugador' => $manoJugador,
                'puntosJugador' => $puntos
            ];
        }

        return [
            'success' => true,
            'terminado' => false,
            'manoJugador' => $manoJugador,
            'puntosJugador' => $puntos
        ];
    }

    /**
     * Plantarse 
     */
    public function actionApiBlackjackPlantarse()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $session = Yii::$app->session;

        $manoJugador = $session->get('bj_mano_jugador');
        $manoDealer = $session->get('bj_mano_dealer');
        $apuesta = $session->get('bj_apuesta');
        $juegoId = $session->get('bj_juego_id');

        $puntosJugador = $this->calcularMano($manoJugador);
        $puntosDealer = $this->calcularMano($manoDealer);

        // El dealer pide carta si tiene menos de 17
        while ($puntosDealer < 17) {
            $manoDealer[] = $this->generarCarta();
            $puntosDealer = $this->calcularMano($manoDealer);
        }

        // Determinar Ganador
        $victoria = false;
        $empate = false;
        $mensaje = "";

        if ($puntosDealer > 21) {
            $victoria = true;
            $mensaje = "¡Dealer se pasó! Ganas tú.";
        } elseif ($puntosJugador > $puntosDealer) {
            $victoria = true;
            $mensaje = "¡Tienes mejor mano! Ganas.";
        } elseif ($puntosJugador == $puntosDealer) {
            $empate = true;
            $mensaje = "Empate. Recuperas lo apostado.";
        } else {
            $mensaje = "El Dealer gana con " . $puntosDealer;
        }

        // Pagos
        $usuario = Yii::$app->user->identity;
        $monedero = $usuario->monedero;

        if ($victoria) {
            $monedero->saldo_real += ($apuesta * 2); // Devuelve apuesta + ganancia
        } elseif ($empate) {
            $monedero->saldo_real += $apuesta; // Devuelve apuesta
        }
        $monedero->save();

        return [
            'success' => true,
            'terminado' => true,
            'victoria' => $victoria,
            'mensaje' => $mensaje,
            'manoDealer' => $manoDealer,
            'puntosDealer' => $puntosDealer,
            'nuevoSaldo' => $monedero->saldo_real
        ];
    }
}

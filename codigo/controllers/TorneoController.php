<?php

namespace app\controllers;

use Yii;
use app\models\Torneo;
use app\models\TorneoSearch;
use app\models\ParticipacionTorneo;
use app\models\Monedero;
use app\models\Transaccion;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TorneoController implements the CRUD actions for Torneo model.
 */
class TorneoController extends Controller
{
    /**
     * PASO 1: ELIMINAR EL "PORTERO" (Behaviors)
     * Devolvemos un array vacío o solo con verbos. 
     * Quitamos 'AccessControl' para que NO redirija al login.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    // REGLA 1: Permitir ver (index, view) y unirse a todo el mundo (o solo logueados según prefieras)
                    [
                        'actions' => ['index', 'view', 'unirse'],
                        'allow' => true,
                        // 'roles' => ['?'], // Si quieres que invitados vean
                    ],
                    // REGLA 2: Solo ADMIN puede Crear, Editar, Borrar, Cancelar y Finalizar
                    [
                        'actions' => ['create', 'update', 'delete', 'cancelar', 'finalizar'],
                        'allow' => true,
                        'roles' => ['@'], // Usuario logueado
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->esAdmin();
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'cancelar' => ['POST'],
                    'finalizar' => ['POST'],
                    'unirse' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Torneo models.
     */
    public function actionIndex()
    {
        $searchModel = new TorneoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

   /**
     * Acción para entrar a la sala de juego.
     * Redirige al controlador del Juego (Módulo G2/G3).
     */
    public function actionJugar($id)
    {
        // 1. Bypass de usuario (El truco que estamos usando)
        $usuarioId = 2; 

        // 2. Buscar torneo
        $torneo = $this->findModel($id);

        // 3. Comprobar si el usuario REALMENTE está inscrito
        $estaInscrito = ParticipacionTorneo::find()
            ->where(['id_torneo' => $id, 'id_usuario' => $usuarioId])
            ->exists();

        if (!$estaInscrito) {
            // Si intenta jugar sin pagar, lo mandamos a inscribirse
            Yii::$app->session->setFlash('warning', 'Primero debes pagar la inscripción.');
            return $this->redirect(['unirse', 'id' => $id]);
        }

        // 4. LÓGICA DE REDIRECCIÓN AL JUEGO (Aquí conectamos con G2)
        // Asumimos que el controlador de juegos se llama 'JuegoController' y la acción 'play'
        // Pasamos el ID del juego Y el ID del torneo para que sepan que es competición.
        
        // En TorneoController.php -> actionJugar($id)
        return $this->redirect([
            'juego/jugar', // <--- CAMBIO AQUÍ (Antes era 'play')
            'id' => $torneo->id_juego_asociado, // El ID del juego (Slots, Ruleta...)
            'id_torneo' => $torneo->id // Le pasamos el ID del torneo
        ]);
    }

    /**
     * Muestra el detalle de un torneo y su RANKING.
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $participantes = ParticipacionTorneo::find()
            ->where(['id_torneo' => $id])
            ->joinWith('usuario')
            ->orderBy(['puntuacion_actual' => SORT_DESC])
            ->all();

        return $this->render('view', [
            'model' => $model,
            'participantes' => $participantes,
        ]);
    }

    public function actionUnirse($id)
{
    $torneo = $this->findModel($id);
    $usuario = Yii::$app->user->identity;
    $monedero = $usuario->monedero; // Asegúrate de tener la relación en el modelo Usuario

    // 1. Validaciones básicas
    if ($torneo->estado !== 'Abierto') {
        Yii::$app->session->setFlash('error', 'Este torneo no admite inscripciones ahora.');
        return $this->redirect(['view', 'id' => $id]);
    }

    // 2. Comprobar si ya está inscrito
    $yaInscrito = \app\models\ParticipacionTorneo::find()
        ->where(['id_torneo' => $id, 'id_usuario' => $usuario->id])
        ->exists();

    if ($yaInscrito) {
        Yii::$app->session->setFlash('warning', '¡Ya estás inscrito en este torneo!');
        return $this->redirect(['view', 'id' => $id]);
    }

    // 3. VALIDACIÓN DE FONDOS (Aquí estaba el problema)
    // Convertimos todo a float para evitar errores de texto vs número
    $saldo = (float) $monedero->saldo_real;
    $coste = (float) $torneo->coste_entrada;

    if ($saldo < $coste) {
        Yii::$app->session->setFlash('error', "Fondos insuficientes. Tienes $saldo € y necesitas $coste €.");
        return $this->redirect(['view', 'id' => $id]);
    }

    // 4. TRANSACCIÓN: Cobrar y Unir
    $transaction = Yii::$app->db->beginTransaction();
    try {
        // A. Restar Saldo
        $monedero->saldo_real -= $coste;
        if (!$monedero->save()) throw new \Exception("Error al actualizar monedero.");

        // B. Crear Participación (Puntuación inicial 0)
        $participacion = new \app\models\ParticipacionTorneo();
        $participacion->id_torneo = $id;
        $participacion->id_usuario = $usuario->id;
        $participacion->puntuacion_actual = 0; // Empieza con 0 ganancias
        if (!$participacion->save()) throw new \Exception("Error al crear participación.");

        // C. Crear Transacción (Historial)
        $trans = new \app\models\Transaccion();
        $trans->id_usuario = $usuario->id;
        $trans->tipo_operacion = 'Apuesta'; // O 'Entrada Torneo'
        $trans->cantidad = $coste;
        $trans->metodo_pago = 'Monedero';
        $trans->estado = 'Completado';
        $trans->referencia_externa = "Inscripción Torneo #" . $torneo->id;
        $trans->save();

        $transaction->commit();
        Yii::$app->session->setFlash('success', '¡Inscripción realizada con éxito! ¡A jugar!');
        return $this->redirect(['/juego/jugar', 'id' => $torneo->id_juego_asociado, 'id_torneo' => $torneo->id]);
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', 'Ocurrió un error técnico: ' . $e->getMessage());
    }

    return $this->redirect(['view', 'id' => $id]);
}

    public function actionFinalizar($id)
{
    // Solo admin puede forzar finalizar (o mediante CronJob)
    if (!Yii::$app->user->identity->esAdmin()) return $this->redirect(['index']);

    $torneo = $this->findModel($id);
    
    if ($torneo->estado === 'Finalizado') {
        return $this->redirect(['view', 'id' => $id]);
    }

    // Buscar al ganador (El que tenga más puntuacion_actual)
    $ganadorParticipacion = \app\models\ParticipacionTorneo::find()
        ->where(['id_torneo' => $id])
        ->orderBy(['puntuacion_actual' => SORT_DESC])
        ->one();

    $transaction = Yii::$app->db->beginTransaction();
    try {
        $torneo->estado = 'Finalizado';
        $torneo->save();

        if ($ganadorParticipacion) {
            // Dar premio al ganador
            $premio = $torneo->bolsa_premios;
            
            // Actualizar monedero del ganador
            $monederoGanador = \app\models\Monedero::findOne(['id_usuario' => $ganadorParticipacion->id_usuario]);
            $monederoGanador->saldo_real += $premio;
            $monederoGanador->save();

            // Guardar registro en participación
            $ganadorParticipacion->posicion_final = 1;
            $ganadorParticipacion->premio_ganado = $premio;
            $ganadorParticipacion->save();

            // Crear transacción de premio
            $trans = new \app\models\Transaccion();
            $trans->id_usuario = $ganadorParticipacion->id_usuario;
            $trans->tipo_operacion = 'Premio';
            $trans->cantidad = $premio;
            $trans->metodo_pago = 'Monedero';
            $trans->estado = 'Completado';
            $trans->referencia_externa = "Premio Torneo: " . $torneo->titulo;
            $trans->save();
            
            Yii::$app->session->setFlash('success', 'Torneo finalizado. Ganador: ' . $ganadorParticipacion->usuario->nick);
        } else {
            Yii::$app->session->setFlash('warning', 'Torneo finalizado sin participantes.');
        }

        $transaction->commit();
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', 'Error al finalizar: ' . $e->getMessage());
    }

    return $this->redirect(['view', 'id' => $id]);
}

    /**
     * Crear Torneo (Acceso libre por el Hack de behaviors)
     */
    public function actionCreate()
    {
        $model = new Torneo();

        if ($model->load(Yii::$app->request->post())) {
            // Parche fechas HTML5
            $model->fecha_inicio = str_replace('T', ' ', $model->fecha_inicio);
            $model->fecha_fin = str_replace('T', ' ', $model->fecha_fin);

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Editar Torneo
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->fecha_inicio = str_replace('T', ' ', $model->fecha_inicio);
            $model->fecha_fin = str_replace('T', ' ', $model->fecha_fin);

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Cancelar Torneo y Devolver Dinero
     */
    public function actionCancelar($id)
    {
        $torneo = $this->findModel($id);

        if ($torneo->estado === 'Cancelado' || $torneo->estado === 'Finalizado') {
            return $this->redirect(['index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $torneo->estado = 'Cancelado';
            $torneo->save();

            foreach ($torneo->participaciones as $participacion) {
                $monedero = Monedero::findOne(['id_usuario' => $participacion->id_usuario]);
                if ($monedero) {
                    $monedero->saldo_real += $torneo->coste_entrada;
                    $monedero->save();
                    
                    // Aquí podrías crear log de transacción
                }
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Torneo cancelado y dinero devuelto.');

        } catch (\Exception $e) {
            $transaction->rollBack();
        }

        return $this->redirect(['index']);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Torneo::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
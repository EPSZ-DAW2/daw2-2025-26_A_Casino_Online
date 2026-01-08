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
    public function actionJugar($id, $id_torneo = null) 
    {
        $model = $this->findModel($id);

        // --- SEGURIDAD: SI ESTÁ EN MANTENIMIENTO O DESACTIVADO, EXPULSAR ---
        if ($model->en_mantenimiento == 1 || $model->activo == 0) {
            Yii::$app->session->setFlash('error', 'El juego "' . $model->nombre . '" está en mantenimiento.');
            return $this->redirect(['lobby']);
        }

        // --- MODO TORNEO ---
        if ($id_torneo !== null) {
            // Verificar estado del torneo
            $torneo = \app\models\Torneo::findOne($id_torneo);
            
            if (!$torneo) {
                return $this->redirect(['lobby']);
            }

            // CORRECCIÓN IMPORTANTE: Si es un torneo, SOLO dejamos jugar si está "En Curso"
            if ($torneo->estado === 'Abierto') {
                Yii::$app->session->setFlash('warning', '⏳ Este torneo está en fase de PRE-INSCRIPCIÓN. Espera a que comience para jugar.');
                return $this->redirect(['/torneo/view', 'id' => $id_torneo]);
            }

            if ($torneo->estado === 'Finalizado' || $torneo->estado === 'Cancelado') {
                Yii::$app->session->setFlash('error', 'Este torneo ha finalizado.');
                return $this->redirect(['/torneo/view', 'id' => $id_torneo]);
            }

            // Si está "En Curso", dejamos pasar y renderizamos la vista de juego
            $this->layout = false;
            return $this->render('jugar', [
                'model' => $model,
                'saldo' => 0, 
                'es_torneo' => true,
                'id_torneo' => $id_torneo
            ]);
        }

        // --- MODO NORMAL (JUGAR POR DINERO) ---
        if (Yii::$app->user->isGuest) {
             // ... lógica de invitado ...
             $saldo = 0;
        } else {
            $saldo = Yii::$app->user->identity->monedero ? Yii::$app->user->identity->monedero->saldo_real : 0;
        }
        
        $this->layout = false; 
        return $this->render('jugar', [
            'model' => $model,
            'saldo' => $saldo,
            'es_torneo' => false,
            'id_torneo' => null
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
    $monedero = $usuario->monedero;

    // --- AQUÍ ESTÁ LA CLAVE ---
    // Si tras el merge esta línea pone solo 'Abierto', cámbiala por esta:
    if ($torneo->estado !== 'Abierto' && $torneo->estado !== 'En Curso') {
        Yii::$app->session->setFlash('error', 'Este torneo no admite inscripciones ahora.');
        return $this->redirect(['view', 'id' => $id]);
    }

    // 2. Comprobar si ya está inscrito
    $yaInscrito = \app\models\ParticipacionTorneo::find()
        ->where(['id_torneo' => $id, 'id_usuario' => $usuario->id])
        ->exists();

    if ($yaInscrito) {
        Yii::$app->session->setFlash('warning', '¡Ya estás inscrito en este torneo!');
        
        // Si ya está inscrito y el torneo está en curso, lo mandamos a jugar directamente
        if ($torneo->estado === 'En Curso') {
            return $this->redirect(['/juego/jugar', 'id' => $torneo->id_juego_asociado, 'id_torneo' => $torneo->id]);
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    // 3. Validación de fondos
    $saldo = (float) $monedero->saldo_real;
    $coste = (float) $torneo->coste_entrada;

    if ($saldo < $coste) {
        Yii::$app->session->setFlash('error', "Fondos insuficientes. Tienes $saldo € y necesitas $coste €.");
        return $this->redirect(['view', 'id' => $id]);
    }

    // 4. Transacción: Cobrar y Crear Participación
    $transaction = Yii::$app->db->beginTransaction();
    try {
        // A. Restar dinero
        $monedero->saldo_real -= $coste;
        if (!$monedero->save()) throw new \Exception("Error al actualizar monedero.");

        // B. Crear participación
        $participacion = new \app\models\ParticipacionTorneo();
        $participacion->id_torneo = $id;
        $participacion->id_usuario = $usuario->id;
        $participacion->puntuacion_actual = 0;
        if (!$participacion->save()) throw new \Exception("Error al crear participación.");

        // C. Crear registro de transacción
        $trans = new \app\models\Transaccion();
        $trans->id_usuario = $usuario->id;
        $trans->tipo_operacion = 'Apuesta'; 
        $trans->cantidad = $coste;
        $trans->metodo_pago = 'Monedero';
        $trans->estado = 'Completado';
        $trans->referencia_externa = "Inscripción Torneo #" . $torneo->id;
        $trans->save();

        $transaction->commit();

        // --- REDIRECCIÓN INTELIGENTE ---
        if ($torneo->estado === 'En Curso') {
            Yii::$app->session->setFlash('success', '¡Inscripción completada! El torneo está EN VIVO. ¡Mucha suerte!');
            // Si está en vivo, vamos directo al juego
            return $this->redirect(['/juego/jugar', 'id' => $torneo->id_juego_asociado, 'id_torneo' => $torneo->id]);
        } else {
            Yii::$app->session->setFlash('success', '¡Pre-inscripción realizada! Tu plaza está reservada. Espera a que empiece.');
            // Si está solo abierto, nos quedamos en la ficha
            return $this->redirect(['view', 'id' => $id]);
        }

    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', 'Ocurrió un error técnico: ' . $e->getMessage());
        return $this->redirect(['view', 'id' => $id]);
    }
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
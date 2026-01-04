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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'cancelar' => ['POST'],
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

    /**
     * ACCIÓN TRUCADA: Inscribirse sin Login real.
     */
    public function actionUnirse($id)
    {
        // --- HACK: BYPASS DE SEGURIDAD ---
        // Forzamos ser el usuario 1 porque el login no funciona
        $usuarioId = 2; 
        // ---------------------------------

        $torneo = $this->findModel($id);

        // Validaciones básicas del torneo
        // Permitimos unirse si está Abierto O si está En Curso (Registro tardío)
        if ($torneo->estado !== 'Abierto' && $torneo->estado !== 'En Curso') {
            Yii::$app->session->setFlash('error', 'El torneo ha finalizado o está cancelado.');
            return $this->redirect(['index']);
        }

        // Comprobar si ya está inscrito
        $yaInscrito = ParticipacionTorneo::find()
            ->where(['id_torneo' => $id, 'id_usuario' => $usuarioId])
            ->exists();

        if ($yaInscrito) {
            Yii::$app->session->setFlash('warning', '¡Ya estás dentro de este torneo!');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Buscar monedero (Si no existe, fallará, asegúrate de tenerlo en BD)
        $monedero = Monedero::findOne(['id_usuario' => $usuarioId]);
        
        if (!$monedero) {
            Yii::$app->session->setFlash('error', 'Error: El usuario 1 no tiene monedero creado en la BD.');
            return $this->redirect(['index']);
        }

        // Lógica de cobro
        $saldoTotal = $monedero->saldo_real + $monedero->saldo_bono;
        if ($saldoTotal < $torneo->coste_entrada) {
            Yii::$app->session->setFlash('error', 'Saldo insuficiente.');
            return $this->redirect(['index']);
        }

        // TRANSACCIÓN
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Restar dinero
            if ($monedero->saldo_real >= $torneo->coste_entrada) {
                $monedero->saldo_real -= $torneo->coste_entrada;
            } else {
                $falta = $torneo->coste_entrada - $monedero->saldo_real;
                $monedero->saldo_real = 0;
                $monedero->saldo_bono -= $falta;
            }
            $monedero->save();

            // Crear inscripción
            $inscripcion = new ParticipacionTorneo();
            $inscripcion->id_torneo = $id;
            $inscripcion->id_usuario = $usuarioId;
            $inscripcion->puntuacion_actual = 0;
            $inscripcion->save();

            $transaction->commit();
            Yii::$app->session->setFlash('success', '¡Inscripción exitosa (Modo Test)!');

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Error técnico.');
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
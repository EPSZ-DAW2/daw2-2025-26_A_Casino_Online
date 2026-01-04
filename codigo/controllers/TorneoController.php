<?php

namespace app\controllers;

use app\models\Torneo;
use app\models\TorneoSearch;
use app\models\ParticipacionTorneo;
use app\models\Monedero;
use app\models\Transaccion;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TorneoController implements the CRUD actions for Torneo model.
 */
class TorneoController extends Controller
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
     * Lists all Torneo models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TorneoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }



    /**
     * Muestra el detalle de un torneo y su RANKING.
     */
    public function actionView($id)
    {
        // 1. Buscamos el torneo (lo que ya hacía Gii)
        $model = $this->findModel($id);

        // 2. Buscamos los participantes ordenados por puntuación (De mayor a menor)
        // Usamos 'joinWith' para traer también los datos del Usuario (Nick y Avatar)
        $participantes = \app\models\ParticipacionTorneo::find()
            ->where(['id_torneo' => $id])
            ->joinWith('usuario') // Esto conecta con la tabla G1
            ->orderBy(['puntuacion_actual' => SORT_DESC])
            ->all();

        // 3. Enviamos ambas cosas a la vista
        return $this->render('view', [
            'model' => $model,
            'participantes' => $participantes, // ¡Nueva variable!
        ]);
    }

    public function actionCreate()
    {
        $model = new Torneo();

        if ($model->load(Yii::$app->request->post())) {
            
            // --- PARCHE DE FECHAS ---
            // Reemplazamos la 'T' por un espacio para que MySQL sea feliz
            $model->fecha_inicio = str_replace('T', ' ', $model->fecha_inicio);
            $model->fecha_fin = str_replace('T', ' ', $model->fecha_fin);
            // ------------------------

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Torneo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            
             // --- PARCHE DE FECHAS ---
            $model->fecha_inicio = str_replace('T', ' ', $model->fecha_inicio);
            $model->fecha_fin = str_replace('T', ' ', $model->fecha_fin);
            // ------------------------

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Torneo model.
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
     * Cancela el torneo y DEVUELVE el dinero a todos los inscritos.
     */
    public function actionCancelar($id)
    {
        $torneo = $this->findModel($id);

        // 1. Validaciones de seguridad
        if ($torneo->estado === 'Cancelado') {
            Yii::$app->session->setFlash('warning', 'Este torneo ya estaba cancelado.');
            return $this->redirect(['index']);
        }
        
        if ($torneo->estado === 'Finalizado') {
            Yii::$app->session->setFlash('error', 'No puedes cancelar un torneo que ya terminó.');
            return $this->redirect(['index']);
        }

        // 2. Iniciamos Transacción (Todo o Nada)
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // A. Cambiar estado del torneo
            $torneo->estado = 'Cancelado';
            if (!$torneo->save()) {
                throw new \Exception('No se pudo actualizar el estado del torneo.');
            }

            // B. Buscar a todos los inscritos
            $participaciones = $torneo->participaciones; // Gracias a la relación que pusimos en el Modelo
            $devueltos = 0;

            foreach ($participaciones as $participacion) {
                // Recuperar el monedero del usuario
                $monedero = \app\models\Monedero::findOne(['id_usuario' => $participacion->id_usuario]);
                
                if ($monedero) {
                    // DEVOLUCIÓN: Sumamos el coste de entrada al saldo real
                    // (Simplificación: devolvemos todo a saldo real para no complicar con bonos)
                    $monedero->saldo_real += $torneo->coste_entrada;
                    $monedero->save();

                    // LOG DE TRANSACCIÓN (Para que quede constancia en G1)
                    $log = new \app\models\Transaccion();
                    $log->id_usuario = $participacion->id_usuario;
                    $log->tipo_operacion = 'Premio'; // O crear un tipo 'Devolucion'
                    $log->categoria = 'Banco';
                    $log->cantidad = $torneo->coste_entrada; // Positivo
                    $log->metodo_pago = 'Sistema';
                    $log->estado = 'Completado';
                    $log->save();
                    
                    $devueltos++;
                }
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', "Torneo cancelado. Se ha devuelto el dinero a $devueltos jugadores.");

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Error al cancelar: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Acción para que un usuario se inscriba en un torneo.
     */
    public function actionUnirse($id)
    {
        // 1. ¿El usuario está logueado?
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'Debes iniciar sesión para competir.');
            return $this->redirect(['site/login']); // O la ruta de login que tengáis
        }

        $usuarioId = Yii::$app->user->id; 
        //$usuarioId = 1; // FORZAMOS QUE SOY EL USUARIO 1 PARA PROBAR
        $torneo = $this->findModel($id); // Busca el torneo o da error 404

        // 2. ¿El torneo está abierto?
        if ($torneo->estado !== 'Abierto') {
            Yii::$app->session->setFlash('error', 'Este torneo ya no admite inscripciones.');
            return $this->redirect(['index']);
        }

        // 3. ¿Ya está inscrito? (Evitar duplicados)
        $yaInscrito = ParticipacionTorneo::find()
            ->where(['id_torneo' => $id, 'id_usuario' => $usuarioId])
            ->exists();

        if ($yaInscrito) {
            Yii::$app->session->setFlash('warning', '¡Ya estás dentro de este torneo!');
            return $this->redirect(['view', 'id' => $id]);
        }

        // 4. Comprobar dinero (Llamamos al modelo de G1)
        // Nota: Asumimos que existe un registro en 'monedero' para este usuario
        $monedero = Monedero::findOne(['id_usuario' => $usuarioId]);
        
        if (!$monedero) {
            Yii::$app->session->setFlash('error', 'Error crítico: No tienes monedero asignado.');
            return $this->redirect(['index']);
        }

        $saldoTotal = $monedero->saldo_real + $monedero->saldo_bono;

        if ($saldoTotal < $torneo->coste_entrada) {
            Yii::$app->session->setFlash('error', 'No tienes saldo suficiente. Coste: ' . $torneo->coste_entrada . '€');
            return $this->redirect(['index']); // O redirigir a "Depositar"
        }

        // 5. TRANSACCIÓN (Cobrar y Apuntar)
        $dbTransaccion = Yii::$app->db->beginTransaction();
        try {
            // A. Restar saldo (Simplificado: primero real, luego bono)
            if ($monedero->saldo_real >= $torneo->coste_entrada) {
                $monedero->saldo_real -= $torneo->coste_entrada;
            } else {
                $falta = $torneo->coste_entrada - $monedero->saldo_real;
                $monedero->saldo_real = 0;
                $monedero->saldo_bono -= $falta;
            }
            $monedero->save();

            // B. Crear inscripción
            $inscripcion = new ParticipacionTorneo();
            $inscripcion->id_torneo = $id;
            $inscripcion->id_usuario = $usuarioId;
            $inscripcion->puntuacion_actual = 0;
            $inscripcion->save();

            $dbTransaccion->commit();
            Yii::$app->session->setFlash('success', '¡Inscripción confirmada! Suerte.');

        } catch (\Exception $e) {
            $dbTransaccion->rollBack();
            Yii::$app->session->setFlash('error', 'Error técnico al procesar la inscripción.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Finds the Torneo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Torneo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Torneo::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

<?php

namespace app\controllers;

use Yii;
use app\models\MesaPrivada;
use app\models\MensajeChat;
use app\models\Usuario;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * MesaPrivadaController gestiona las salas privadas y el chat asociado.
 * Parte del módulo G6.
 */
class MesaPrivadaController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Solo registrados pueden jugar
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista todas las mesas privadas disponibles (Abiertas o Jugando).
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => MesaPrivada::find()
                ->where(['!=', 'estado_mesa', MesaPrivada::ESTADO_CERRADA]) // Solo mostramos activas
                ->orderBy(['id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Crea una nueva mesa privada.
     * Si se crea con éxito, redirige a la sala directamente.
     */
    public function actionCreate()
    {
        $model = new MesaPrivada();
        // Asignar el anfitrión automáticamente al usuario actual
        $model->id_anfitrion = Yii::$app->user->id;
        $model->estado_mesa = MesaPrivada::ESTADO_ABIERTA;

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Mesa creada. ¡Esperando jugadores!');
                // Auto-ingreso a la sala
                return $this->redirect(['room', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Acción intermedia para validar la contraseña de la mesa.
     */
    public function actionJoin($id)
    {
        $model = $this->findModel($id);

        // Si el usuario es el anfitrión, entra directo
        if ($model->id_anfitrion === Yii::$app->user->id) {
            return $this->redirect(['room', 'id' => $model->id]);
        }

        // Si ya hay sesión verificada para esta mesa (lógica simple de sesión)
        if (Yii::$app->session->has("acceso_mesa_{$id}")) {
            return $this->redirect(['room', 'id' => $model->id]);
        }

        // Procesar formulario de contraseña
        if ($this->request->isPost) {
            $password = $this->request->post('password_mesa');
            if ($model->validarContrasena($password)) {
                // Guardar en sesión que tiene permiso
                Yii::$app->session->set("acceso_mesa_{$id}", true);
                return $this->redirect(['room', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Contraseña incorrecta.');
            }
        }

        return $this->render('join', [
            'model' => $model,
        ]);
    }

    /**
     * Vista principal de la SALA DE JUEGO + CHAT.
     */
    public function actionRoom($id)
    {
        $mesa = $this->findModel($id);

        // Verificación de seguridad: ¿Tiene permiso para estar aquí?
        // El anfitrión siempre pasa, los demás necesitan la flag de sesión
        $esAnfitrion = ($mesa->id_anfitrion === Yii::$app->user->id);
        if (!$esAnfitrion && !Yii::$app->session->has("acceso_mesa_{$id}")) {
            return $this->redirect(['join', 'id' => $id]);
        }

        // Modelo para el formulario de nuevo mensaje
        $nuevoMensaje = new MensajeChat();

        // Procesar envío de mensaje (Postback simple para MVP, idealmente AJAX)
        if ($this->request->isPost && $nuevoMensaje->load($this->request->post())) {
            $nuevoMensaje->id_mesa = $mesa->id;
            $nuevoMensaje->id_usuario = Yii::$app->user->id;
            // La fecha y el filtro de "bad words" se manejan en el Modelo (beforeSave)
            if ($nuevoMensaje->save()) {
                // Refrescar página para ver el mensaje
                return $this->refresh();
            }
        }

        // Cargar historial de mensajes ordenados cronológicamente
        $mensajes = MensajeChat::find()
            ->where(['id_mesa' => $mesa->id])
            ->orderBy(['fecha_envio' => SORT_ASC]) // Los viejos arriba (estilo chat normal)
            ->all();

        // INTEGRACIÓN G3/G4: Buscar el juego real para incrustarlo
        // Buscamos un juego cuyo nombre contenga el tipo de la mesa (Búsqueda laxa)
        $juegoAsociado = \app\models\Juego::find()
            ->where(['like', 'nombre', $mesa->tipo_juego])
            ->orWhere(['like', 'tipo', $mesa->tipo_juego]) // Por si pone "Ruleta"
            ->one();

        return $this->render('room', [
            'mesa' => $mesa,
            'chatModel' => $nuevoMensaje,
            'mensajes' => $mensajes,
            'juegoAsociado' => $juegoAsociado, // Pasamos el juego encontrado (o null)
        ]);
    }

    /**
     * Helper: Buscar modelo
     */
    protected function findModel($id)
    {
        if (($model = MesaPrivada::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La mesa no existe.');
    }
}

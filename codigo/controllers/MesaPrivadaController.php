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
        // El anfitrión siempre entra. Los invitados deben haber pasado por actionJoin (tener sesión).
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

        // INTEGRACIÓN G3/G4: Conexión con el Módulo de Juegos
        // Buscamos si existe un juego en BD que coincida con el nombre de la mesa.
        // Si existe, lo pasaremos a la vista para cargarlo en el Iframe.
        $juegoAsociado = \app\models\Juego::find()
            ->where(['like', 'nombre', $mesa->tipo_juego])
            ->orWhere(['like', 'tipo', $mesa->tipo_juego]) // Por si pone "Ruleta" generico
            ->one();

        return $this->render('room', [
            'mesa' => $mesa,
            'chatModel' => $nuevoMensaje,
            'mensajes' => $mensajes,
            'juegoAsociado' => $juegoAsociado, // Pasamos el juego encontrado (o null)
        ]);
    }

    /**
     * AJAX (G6): Obtener mensajes nuevos.
     * @param int $id ID de la mesa
     * @param int $lastId ID del último mensaje que tiene el cliente
     */
    public function actionGetMensajes($id, $lastId = 0)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $mensajes = MensajeChat::find()
            ->where(['id_mesa' => $id])
            ->andWhere(['>', 'id', $lastId])
            ->orderBy(['fecha_envio' => SORT_ASC])
            ->all();

        $data = [];
        foreach ($mensajes as $msg) {
            $data[] = [
                'id' => $msg->id,
                'autor' => $msg->usuario->nick,
                'contenido' => \yii\helpers\Html::encode($msg->mensaje), // Corregido el nombre de propiedad
                'hora' => Yii::$app->formatter->asTime($msg->fecha_envio, 'short'),
                'es_mio' => ($msg->id_usuario == Yii::$app->user->id)
            ];
        }

        return $data;
    }

    /**
     * AJAX (G6): Enviar mensaje sin recarga.
     */
    public function actionEnviarMensaje($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = new MensajeChat();
        $model->id_mesa = $id;
        $model->id_usuario = Yii::$app->user->id;

        if ($this->request->isPost) {
            // El JS envía 'contenido' pero el modelo espera 'mensaje'
            $contenido = $this->request->post('contenido');
            $model->mensaje = $contenido;

            if ($model->save()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'errors' => $model->errors];
            }
        }
        return ['success' => false, 'error' => 'Petición inválida'];
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

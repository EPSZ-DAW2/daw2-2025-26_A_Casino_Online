<?php

namespace app\controllers;

use Yii;
use app\models\Logro;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\Usuario;

/**
 * LogroController implementa el CRUD para el modelo Logro.
 * Parte del módulo G6 (Social y Gamificación).
 */
class LogroController extends Controller
{
    /**
     * Comportamientos y Filtros (Seguridad G6)
     * Define quién puede acceder a este controlador.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        // REGLA DE ORO: Solo Admins y SuperAdmins pueden gestionar logros.
                        // Los jugadores normales solo pueden VERLOS (en GamificacionController), no editarlos.
                        'allow' => true,
                        'roles' => ['@'], // Usuarios autenticados
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->esAdmin() || Yii::$app->user->identity->esSuperAdmin();
                        }
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
     * Lista todos los logros.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Logro::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Muestra un logro individual.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si no existe
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Crea un nuevo logro.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Logro();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Logro creado correctamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Actualiza un logro existente.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Logro actualizado correctamente.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Elimina un logro (Cuidado: borrará cascada en logro_usuario).
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Logro eliminado.');

        return $this->redirect(['index']);
    }

    /**
     * Encuentra el modelo Logro basado en su ID.
     * @param int $id
     * @return Logro
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Logro::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La página solicitada no existe.');
    }
}

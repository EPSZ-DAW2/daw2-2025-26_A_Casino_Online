<?php

namespace app\controllers;

use app\models\AlertaFraude;
use app\models\AlertaFraudeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii;

/**
 * AlertaFraudeController implements the CRUD actions for AlertaFraude model.
 */
class AlertaFraudeController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // '@' significa usuario logueado
                        'matchCallback' => function ($rule, $action) {
                            // Solo dejamos pasar si el usuario es "admin"
                            // Usamos la función que creamos antes en el modelo Usuario
                        return !Yii::$app->user->isGuest && Yii::$app->user->identity->esAdmin();
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'banear' => ['POST'], // Protegemos el ban también
                ],
            ],
        ];
    }

    /**
     * Lists all AlertaFraude models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AlertaFraudeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AlertaFraude model.
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
     * Creates a new AlertaFraude model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AlertaFraude();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AlertaFraude model.
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
     * Deletes an existing AlertaFraude model.
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
     * Finds the AlertaFraude model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return AlertaFraude the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AlertaFraude::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Acción para Banear a un usuario directamente desde una alerta.
     */
    public function actionBanear($id)
    {
        // 1. Buscamos la alerta seleccionada
        $alerta = $this->findModel($id);

        // 2. Buscamos al usuario asociado a esa alerta
        $usuario = \app\models\Usuario::findOne($alerta->id_usuario);

        if ($usuario) {
            // 3. ¡ZAS! Cambiamos su estado a Bloqueado
            $usuario->estado_cuenta = 'Bloqueado';

            // 4. Guardamos los cambios
            if ($usuario->save(false)) { // false para saltar validaciones estrictas
                Yii::$app->session->setFlash('success', '🚫 El usuario ' . $usuario->nick . ' ha sido BANEADO correctamente.');

                // Opcional: Marcar la alerta como "Resuelta" automáticamente
                $alerta->estado = 'Resuelto';
                $alerta->save(false);
            } else {
                Yii::$app->session->setFlash('error', 'No se pudo banear al usuario.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Error: Esta alerta no tiene un usuario válido asociado.');
        }

        // 5. Volvemos a la lista
        return $this->redirect(['index']);
    }
}

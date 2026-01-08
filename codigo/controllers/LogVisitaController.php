<?php

namespace app\controllers;

use Yii;
use app\models\LogVisita;
use app\models\LogVisitaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * LogVisitaController implements the CRUD actions for LogVisita model.
 */
class LogVisitaController extends Controller
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
                    // Regla 1: Acciones públicas para usuarios logueados
                    [
                        'actions' => ['mis-visitas', 'view'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    // Regla 2: Todo lo demás (index, delete) solo para admin
                    [
                        'actions' => ['index', 'delete'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->puedeAccederBackend();
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all LogVisita models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LogVisitaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single LogVisita model.
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
     * Creates a new LogVisita model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new LogVisita();

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
     * Updates an existing LogVisita model.
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
     * Deletes an existing LogVisita model.
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
     * Finds the LogVisita model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return LogVisita the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LogVisita::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionMisVisitas()
    {
        // Verificamos que el usuario esté logueado (importante por seguridad)
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        // Buscamos SOLO las visitas del usuario actual
        $searchModel = new \app\models\LogVisitaSearch();

        // Forzamos que el filtro busque por el ID del usuario conectado
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['id_usuario' => Yii::$app->user->id]);

        // Ordenamos para ver las más recientes primero
        $dataProvider->setSort(['defaultOrder' => ['fecha_hora' => SORT_DESC]]);

        // Renderizamos una vista especial para el usuario (no la de admin)
        return $this->render('mis-visitas', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}

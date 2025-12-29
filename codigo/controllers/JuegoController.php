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

        $saldo = Yii::$app->user->identity->monedero->saldo_real;
        
        $this->layout = false; 
        return $this->render('jugar', [
            'model' => $model,
            'saldo' => $saldo
        ]);
    }
}

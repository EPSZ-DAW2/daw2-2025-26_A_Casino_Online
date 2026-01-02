<?php

namespace app\controllers;

use Yii;
use app\models\Transaccion;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

class TransaccionController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->esAdmin();
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Listado masivo para el Admin
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Transaccion::find()->orderBy(['fecha_hora' => SORT_DESC]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lógica para Aprobar/Rechazar (G2)
     */
    public function actionCambiarEstado($id, $estado)
    {
        $model = Transaccion::findOne($id);
        if ($model && Yii::$app->user->identity->esAdmin()) {
            $model->estado = $estado;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Transacción #$id marcada como $estado.");
            }
        }
        return $this->redirect(['index']);
    }
}
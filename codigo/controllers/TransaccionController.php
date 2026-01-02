<?php

namespace app\controllers;

use Yii;
use app\models\Transaccion;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

/**
* TransaccionController gestiona la administración masiva de movimientos (G2).
* Es la herramienta principal para que el admin valide retiradas y vigile ingresos.
*/
class TransaccionController extends Controller
{
    /**
     * BEHAVIORS (G2):
     * Restricción de seguridad para que solo el rol 'admin' pueda entrar aquí.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Usuario logueado
                        'matchCallback' => function ($rule, $action) {
                            // Además pasa la validación del método esAdmin() del modelo Usuario
                            return Yii::$app->user->identity->esAdmin();
                        }
                    ],
                ],
            ],
        ];
    }

    /**
    * PANEL DE CONTROL (index):
    * Lista todas las transacciones de todos los usuarios de la base de datos.
    */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            // Ordenamos por fecha descendente para ver lo más nuevo primero
            'query' => Transaccion::find()->orderBy(['fecha_hora' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20, // Mostramos 20 registros por página para mayor agilidad
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
    * LÓGICA DE VALIDACIÓN (G2):
    * Permite al administrador cambiar el estado de una operación (Aprobar/Rechazar).
    * Especialmente crítico para las retiradas que nacen en estado 'Pendiente'.
    */
    public function actionCambiarEstado($id, $estado)
    {
        // Buscamos la transacción específica por su ID
        $model = Transaccion::findOne($id);
        if ($model && Yii::$app->user->identity->esAdmin()) {
            // Actualizamos el estado ('Completado' o 'Rechazado')
            $model->estado = $estado;
            if ($model->save()) {
                // Feedback visual para el administrador
                Yii::$app->session->setFlash('success', "Transacción #$id marcada como $estado.");
            } else {
                Yii::$app->session->setFlash('error', "No se pudo actualizar el estado de la transacción.");
            }
        }
        // Redirigimos de vuelta al listado para seguir gestionando
        return $this->redirect(['index']);
    }
}
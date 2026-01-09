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
                            return Yii::$app->user->identity->puedeGestionarDinero();
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
        if ($model && Yii::$app->user->identity->puedeGestionarDinero()) {
            // Actualizamos el estado ('Completado' o 'Rechazado')
            $model->estado = $estado;
            if ($model->save()) {
                // Feedback visual para el administrador
                Yii::$app->session->setFlash('success', "Transacción #$id marcada como $estado.");

                // LÓGICA DE REEMBOLSO (G2):
                // Si rechazamos una retirada, debemos devolver el dinero al monedero del usuario.
                if ($model->tipo_operacion === 'Retirada' && $estado === 'Rechazado') {
                    $monedero = $model->usuario->monedero;
                    if ($monedero) {
                        $monedero->saldo_real += $model->cantidad;
                        if ($monedero->save()) {
                            Yii::$app->session->addFlash('success', "Se han reembolsado {$model->cantidad}€ al usuario.");
                            Yii::info("Reembolso de {$model->cantidad}€ al usuario {$model->id_usuario} por rechazo de retirada #$id", __METHOD__);
                        } else {
                            Yii::error("Error al reembolsar dinero al usuario {$model->id_usuario}: " . print_r($monedero->getErrors(), true), __METHOD__);
                            Yii::$app->session->addFlash('error', "Error crítico: No se pudo reembolsar el dinero al monedero.");
                        }
                    }
                }
            } else {
                $errors = implode(', ', \yii\helpers\ArrayHelper::getColumn($model->getErrors(), 0));
                Yii::error("Error al actualizar transacción #$id: " . print_r($model->getErrors(), true), __METHOD__);
                Yii::$app->session->setFlash('error', "No se pudo actualizar el estado de la transacción: $errors");
            }
        }
        // Redirigimos de vuelta al listado para seguir gestionando
        return $this->redirect(['index']);
    }
}
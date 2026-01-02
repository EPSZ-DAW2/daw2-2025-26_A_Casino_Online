<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use app\models\Monedero;
use app\models\Transaccion;
use yii\data\ActiveDataProvider;

class MonederoController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Solo usuarios logueados
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $usuarioId = Yii::$app->user->id;
        $monedero = Monedero::findOne(['id_usuario' => $usuarioId]);

        // Consultamos el gasto total por categoría (Solo apuestas/gastos)
        $gastosPorCategoria = Transaccion::find()
            ->select(['categoria', 'SUM(cantidad) as cantidad'])
            ->where(['id_usuario' => $usuarioId, 'tipo_operacion' => 'Apuesta'])
            ->groupBy('categoria')
            ->asArray()
            ->all();

        return $this->render('index', [
            'monedero' => $monedero,
            'dataProvider' => new ActiveDataProvider([
                'query' => Transaccion::find()->where(['id_usuario' => $usuarioId])->orderBy(['fecha_hora' => SORT_DESC]),
            ]),
            'datosGrafica' => $gastosPorCategoria, // Enviamos los datos procesados
        ]);
    }

    /**
    * Simulación de ingreso de saldo (G2)
    */
    public function actionDepositar($cantidad, $metodo, $dato)
    {
        $usuarioId = Yii::$app->user->id;
        $monedero = Monedero::findOne(['id_usuario' => $usuarioId]);

        // IMPORTANTE: Si es un usuario nuevo, es posible que no tenga una fila en la tabla 'monedero'
        if (!$monedero) {
            $monedero = new Monedero();
            $monedero->id_usuario = $usuarioId;
            $monedero->saldo_real = 0;
            $monedero->saldo_bono = 0;
        }

        if ($cantidad > 0) {
            // Iniciar una transacción de base de datos para asegurar integridad
            $dbTrans = Yii::$app->db->beginTransaction();
            try {
                // 1. Actualizar el saldo en el monedero
                $monedero->saldo_real += $cantidad;
                $monedero->save();

                // 2. Registrar el movimiento en la tabla transaccion
                $nuevaTrans = new Transaccion();
                $nuevaTrans->id_usuario = $usuarioId;
                $nuevaTrans->tipo_operacion = 'Deposito';
                $nuevaTrans->cantidad = $cantidad;
                $nuevaTrans->metodo_pago = $metodo; // Ahora guarda 'Bizum' o 'Tarjeta' según el clic
                $nuevaTrans->referencia_externa = $dato;
                $nuevaTrans->estado = 'Completado';
                $nuevaTrans->fecha_hora = date('Y-m-d H:i:s');
                $nuevaTrans->save();

                $dbTrans->commit();
                Yii::$app->session->setFlash('success', "¡Ingreso de $cantidad € realizado con éxito!");
            } catch (\Exception $e) {
                $dbTrans->rollBack();
                Yii::$app->session->setFlash('error', "Error al procesar el ingreso.");
            }
        }

        return $this->redirect(['index']);
    }

    /**
    * Solicitar retirada de fondos (G2)
    */
    public function actionRetirar($cantidad)
    {
        $usuarioId = Yii::$app->user->id;
        $monedero = Monedero::findOne(['id_usuario' => $usuarioId]);

        // Validación crítica: No se puede retirar más de lo que se tiene en Saldo Real
        if ($monedero && $cantidad > 0 && $cantidad <= $monedero->saldo_real) {
            $dbTrans = Yii::$app->db->beginTransaction();
            try {
                // 1. Restamos el dinero del saldo real inmediatamente para que no lo use
                $monedero->saldo_real -= $cantidad;
                $monedero->save();

                // 2. Registramos la transacción como 'Pendiente'
                $trans = new Transaccion();
                $trans->id_usuario = $usuarioId;
                $trans->tipo_operacion = 'Retirada';
                $trans->cantidad = $cantidad;
                $trans->metodo_pago = 'Transferencia';
                $trans->estado = 'Pendiente'; // Requisito G2: queda a espera de aprobación
                $trans->fecha_hora = date('Y-m-d H:i:s');
                $trans->save();

                $dbTrans->commit();
                Yii::$app->session->setFlash('success', "Solicitud de retirada de $cantidad € enviada. Pendiente de aprobación.");
            } catch (\Exception $e) {
                $dbTrans->rollBack();
                Yii::$app->session->setFlash('error', "Error al procesar la retirada.");
            }
        } else {
            Yii::$app->session->setFlash('error', "Fondos insuficientes o cantidad inválida.");
        }

        return $this->redirect(['index']);
    }
}
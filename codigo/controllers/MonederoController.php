<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use app\models\Monedero;
use app\models\Transaccion;
use yii\data\ActiveDataProvider;

/**
* MonederoController gestiona la lógica financiera del lado del usuario (G2-W2).
*/
class MonederoController extends Controller
{
    /**
    * BEHAVIORS: Configura las reglas de acceso.
    * Solo permitimos que usuarios autenticados ('@') accedan a estas funciones.
    */
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

    /**
    * VISTA PRINCIPAL (index):
    * Muestra el saldo actual, el historial y los datos para la gráfica.
    */
    public function actionIndex()
    {
        $usuarioId = Yii::$app->user->id;

        // Buscamos el monedero del usuario
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
            // PREPARACIÓN DEL HISTORIAL (GridView):
            'dataProvider' => new ActiveDataProvider([
                'query' => Transaccion::find()->where(['id_usuario' => $usuarioId])->orderBy(['fecha_hora' => SORT_DESC]),
            ]),
            // DATOS PARA LA GRÁFICA (W2):
            // Agrupamos las transacciones por categoría para alimentar el Chart.js
            'datosGrafica' => $gastosPorCategoria, // Enviamos los datos procesados
        ]);
    }

    /**
    * ACCIÓN DEPOSITAR (G2):
    * Procesa los ingresos mediante Tarjeta o Bizum.
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
            // TRANSACCIÓN DE BD: Aseguramos que si falla el registro, no se sume el dinero.
            $dbTrans = Yii::$app->db->beginTransaction();
            try {
                // Actualizar el saldo en el monedero
                $monedero->saldo_real += $cantidad;
                $monedero->save();

                // Registrar el movimiento en la tabla transaccion
                $nuevaTrans = new Transaccion();
                $nuevaTrans->id_usuario = $usuarioId;
                $nuevaTrans->tipo_operacion = 'Deposito';
                $nuevaTrans->cantidad = $cantidad;
                $nuevaTrans->metodo_pago = $metodo; // Ahora guarda 'Bizum' o 'Tarjeta' según el clic
                $nuevaTrans->referencia_externa = $dato; // El dato que capturamos del input dinámico
                $nuevaTrans->estado = 'Completado'; // Los ingresos se completan automáticamente
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
    * ACCIÓN RETIRAR (G2):
    * Gestiona las solicitudes de cobro del usuario.
    */
    public function actionRetirar($cantidad)
    {
        $usuarioId = Yii::$app->user->id;
        $monedero = Monedero::findOne(['id_usuario' => $usuarioId]);

       // VALIDACIÓN DE SALDO: Solo se puede retirar del saldo REAL.
        if ($monedero && $cantidad > 0 && $cantidad <= $monedero->saldo_real) {
            $dbTrans = Yii::$app->db->beginTransaction();
            try {
                // Restamos el dinero del saldo real inmediatamente para que no lo use
                $monedero->saldo_real -= $cantidad;
                $monedero->save();

                // Creamos la transacción en estado PENDIENTE para que el admin la apruebe
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
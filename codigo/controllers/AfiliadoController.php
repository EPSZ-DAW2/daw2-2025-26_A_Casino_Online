<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\Usuario;
use app\models\Monedero;

/**
 * Controlador para el Sistema de Afiliados (G6).
 * Gestiona el panel de promoción y visualización de comisiones.
 */
class AfiliadoController extends Controller
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
                        'roles' => ['@'], // Solo usuarios registrados
                    ],
                ],
            ],
        ];
    }

    /**
     * Dashboard del Afiliado.
     * Muestra enlace de referido, contadores y lista de usuarios captados.
     */
    public function actionIndex()
    {
        /** @var Usuario $usuario */
        $usuario = Yii::$app->user->identity;

        // Verificar si tiene código propio, si no, generarlo
        if (empty($usuario->codigo_referido_propio)) {
            $usuario->codigo_referido_propio = $this->generarCodigoUnico($usuario->id);
            $usuario->save(false, ['codigo_referido_propio']);
        }

        // Obtener lista de afiliados (Ahijados)
        $afiliados = $usuario->getAfiliados()->all();

        // Simular cálculo de comisiones (En prod esto vendría de tabla Transacciones)
        // Por ahora, asumimos que ganamos 10€ por cada afiliados verificado como "Bonus de Bienvenida"
        $comisionTotal = 0;
        foreach ($afiliados as $ahijado) {
            if ($ahijado->esVerificado()) {
                $comisionTotal += 10.00;
            }
        }

        return $this->render('panel', [
            'usuario' => $usuario,
            'afiliados' => $afiliados,
            'comisionTotal' => $comisionTotal,
        ]);
    }

    /**
     * Genera un código aleatorio corto basado en el ID.
     */
    protected function generarCodigoUnico($id)
    {
        return 'REF-' . $id . '-' . strtoupper(substr(md5(time()), 0, 5));
    }
}

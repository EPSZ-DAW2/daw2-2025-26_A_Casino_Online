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

        // 1. Verificar si tiene código propio, si no, generarlo
        // 1. AUTOGENERACIÓN DE CÓDIGO (Lógica G6)
        // Si es la primera vez que entra y no tiene código, se lo creamos.
        if (empty($usuario->codigo_referido_propio)) {
            $usuario->codigo_referido_propio = $this->generarCodigoUnico($usuario->id);
            $usuario->save(false, ['codigo_referido_propio']);
        }

        // 2. OBTENCIÓN DE REFERIDOS
        // Usamos la relación 'getAfiliados' definida en el Modelo Usuario
        $afiliados = $usuario->getAfiliados()->all();

        // 3. CÁLCULO DE COMISIONES (Simulado)
        // Ejemplo: 10€ por cada usuario que haya verificado su cuenta (KYC)
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

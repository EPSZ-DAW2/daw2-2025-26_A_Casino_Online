<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\Usuario;

/**
 * Controlador para la visualización de Gamificación (Frontend).
 * Permite al usuario ver sus logros y progreso (W6).
 */
class GamificacionController extends Controller
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
                        'roles' => ['@'], // Solo usuarios registrados pueden ver su perfil de gamificación
                    ],
                ],
            ],
        ];
    }

    /**
     * Muestra el "Muro de Logros" del usuario actual.
     * Recupera los logros obtenidos mediante la relación definida en el modelo Usuario.
     */
    public function actionIndex()
    {
        // Obtener el usuario logueado
        /** @var Usuario $usuario */
        $usuario = Yii::$app->user->identity;

        // Recuperar sus logros desbloqueados
        $logros = $usuario->getLogros()->all();

        // (Opcional) Recuperar TODOS los logros para mostrar los bloqueados en gris
        // $todosLosLogros = \app\models\Logro::find()->all();

        return $this->render('index', [
            'usuario' => $usuario,
            'logros' => $logros,
        ]);
    }
}

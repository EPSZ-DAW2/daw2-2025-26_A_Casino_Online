<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Usuario;

/**
 * Este comando inicializa datos de prueba para el proyecto.
 */
class SeedController extends Controller
{
    /**
     * Crea un usuario Administrador y algunos datos iniciales.
     * Uso: php yii seed/init
     */
    public function actionInit()
    {
        echo "Inicializando datos de prueba...\n";

        // Crear Usuario Admin
        $admin = Usuario::findOne(['nick' => 'admin']);
        if (!$admin) {
            $admin = new Usuario();
            $admin->nick = 'admin';
            $admin->email = 'admin@casino.com';
            $admin->nombre = 'Super';
            $admin->apellido = 'Admin';
            $admin->rol = Usuario::ROL_SUPERADMIN;
            $admin->nivel_vip = 'Oro';
            $admin->estado_cuenta = 'Activo';
            $admin->estado_verificacion = 'Verificado';
            $admin->setPassword('admin123'); // Contraseña por defecto
            $admin->generateAuthKey();

            if ($admin->save()) {
                echo " [OK] Usuario 'admin' creado con password 'admin123'.\n";
            } else {
                echo " [ERROR] No se pudo crear al admin:\n";
                print_r($admin->errors);
            }
        } else {
            echo " [SKIP] El usuario 'admin' ya existe.\n";
        }

        // Crear Jugador de prueba
        $jugador = Usuario::findOne(['nick' => 'jugador1']);
        if (!$jugador) {
            $jugador = new Usuario();
            $jugador->nick = 'jugador1';
            $jugador->email = 'jugador1@casino.com';
            $jugador->rol = Usuario::ROL_JUGADOR;
            $jugador->nivel_vip = 'Bronce';
            $jugador->estado_cuenta = 'Activo';
            $jugador->estado_verificacion = 'Verificado';
            $jugador->setPassword('jugador123');
            $jugador->generateAuthKey();

            if ($jugador->save()) {
                echo " [OK] Usuario 'jugador1' creado con password 'jugador123'.\n";
            }
        }

        echo "Datos inicializados correctamente.\n";
        return ExitCode::OK;
    }
}

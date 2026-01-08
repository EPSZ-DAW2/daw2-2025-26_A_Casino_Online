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

        // Datos comunes
        $passwordComun = '123456';

        // 1. Crear ADMIN
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
            $admin->setPassword($passwordComun);
            $admin->generateAuthKey();

            if ($admin->save()) {
                echo " [OK] Usuario 'admin' creado (Pass: $passwordComun).\n";
            }
        } else {
            // Si ya existe, le actualizamos la password por si acaso
            $admin->setPassword($passwordComun);
            $admin->save(false);
            echo " [UPDATE] Password de 'admin' reseteada a '$passwordComun'.\n";
        }

        // 2. Crear Jugador 1
        $jugador1 = Usuario::findOne(['nick' => 'jugador1']);
        if (!$jugador1) {
            $jugador1 = new Usuario();
            $jugador1->nick = 'jugador1';
            $jugador1->email = 'jugador1@casino.com';
            $jugador1->rol = Usuario::ROL_JUGADOR;
            $jugador1->nivel_vip = 'Bronce';
            $jugador1->estado_cuenta = 'Activo';
            $jugador1->estado_verificacion = 'Verificado';
            $jugador1->setPassword($passwordComun);
            $jugador1->generateAuthKey();

            if ($jugador1->save()) {
                echo " [OK] Usuario 'jugador1' creado (Pass: $passwordComun).\n";
            }
        } else {
            $jugador1->setPassword($passwordComun);
            $jugador1->save(false);
            echo " [UPDATE] Password de 'jugador1' reseteada a '$passwordComun'.\n";
        }

        // 3. Crear Jugador 2
        $jugador2 = Usuario::findOne(['nick' => 'jugador2']);
        if (!$jugador2) {
            $jugador2 = new Usuario();
            $jugador2->nick = 'jugador2';
            $jugador2->email = 'jugador2@casino.com';
            $jugador2->rol = Usuario::ROL_JUGADOR;
            $jugador2->nivel_vip = 'Plata';
            $jugador2->estado_cuenta = 'Activo';
            $jugador2->estado_verificacion = 'Verificado';
            $jugador2->setPassword($passwordComun);
            $jugador2->generateAuthKey();

            if ($jugador2->save()) {
                echo " [OK] Usuario 'jugador2' creado (Pass: $passwordComun).\n";
            }
        } else {
            $jugador2->setPassword($passwordComun);
            $jugador2->save(false);
            echo " [UPDATE] Password de 'jugador2' reseteada a '$passwordComun'.\n";
        }

        echo "Datos inicializados correctamente.\n";
        return ExitCode::OK;
    }
}

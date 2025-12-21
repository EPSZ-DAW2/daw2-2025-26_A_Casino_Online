<?php
namespace app\models;

use Yii;
use yii\base\Model;

/**
 * SignupForm es el modelo detrás del formulario de registro.
 */
class SignupForm extends Model
{
    public $nick;
    public $email;
    public $password;
    public $password_repeat;

    /**
     * Reglas de validación para el registro
     */
    public function rules()
    {
        return [
            ['nick', 'trim'],
            ['nick', 'required', 'message' => 'Por favor, elige un nombre de usuario.'],
            ['nick', 'unique', 'targetClass' => '\app\models\Usuario', 'message' => 'Este usuario ya está en uso.'],
            ['nick', 'string', 'min' => 2, 'max' => 50],

            ['email', 'trim'],
            ['email', 'required', 'message' => 'Necesitamos tu email.'],
            ['email', 'email'],
            ['email', 'string', 'max' => 100],
            ['email', 'unique', 'targetClass' => '\app\models\Usuario', 'message' => 'Este email ya está registrado.'],

            ['password', 'required', 'message' => 'La contraseña es obligatoria.'],
            ['password', 'string', 'min' => 6],

            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Las contraseñas no coinciden.'],
        ];
    }

    /**
     * Guarda el usuario en la base de datos (con contraseña encriptada).
     * @return bool si el usuario se creó correctamente.
     */
    public function signup()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = new Usuario();
        $user->nick = $this->nick;
        $user->email = $this->email;
        $user->rol = 'jugador'; // Por defecto todos son jugadores
        $user->setPassword($this->password);
        $user->generateAuthKey();
        
        // Inicializamos valores por defecto requeridos por tu BD
        $user->nivel_vip = 'Bronce';
        $user->puntos_progreso = 0;
        $user->estado_cuenta = 'Activo';

        return $user->save();
    }
}
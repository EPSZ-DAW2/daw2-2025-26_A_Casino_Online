<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\Usuario;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            // Logueamos al usuario
            $loginExitoso = Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
            
            if ($loginExitoso) {
                // Insertamos directamente en la tabla usando SQL nativo por rapidez
                Yii::$app->db->createCommand()->insert('log_visita', [
                    'id_usuario' => $this->getUser()->id,
                    'direccion_ip' => Yii::$app->request->userIP,
                    'dispositivo' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', // Navegador/SO
                    'fecha_hora' => new \yii\db\Expression('NOW()'),
                ])->execute();
            }

            return $loginExitoso;
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = Usuario::findByUsername($this->username);
        }

        return $this->_user;
    }
}

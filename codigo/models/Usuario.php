<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Este es el modelo para la tabla "usuario".
 *
 * @property int $id
 * @property string $nick
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property string|null $password_reset_token
 * @property string|null $access_token
 * @property string|null $rol
 * @property string|null $nombre
 * @property string|null $apellido
 * @property string|null $telefono
 * @property string|null $fecha_registro
 * @property string|null $avatar_url
 * @property string|null $nivel_vip
 * @property int|null $puntos_progreso
 * @property string|null $estado_cuenta
 * @property string|null $estado_verificacion
 * @property string|null $foto_dni
 * @property string|null $foto_selfie
 * @property string|null $notas_internas
 * @property string|null $codigo_referido_propio
 * @property int|null $id_padrino
 *
 * @property Usuario $padrino
 * @property Usuario[] $ahijados
 */

/**
 * Modelo Usuario: Gestiona la identidad y seguridad (G5) y roles (G2).
 */
class Usuario extends ActiveRecord implements IdentityInterface
{
    // Variable auxiliar para cuando estemos creando/editando la contraseña en un formulario
    public $password_plain; 

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usuario';
    }

    /**
     * Reglas de validación de datos.
     * Aquí definimos qué es obligatorio, qué debe ser único, etc.
     */
    public function rules()
    {
        return [
            [['nick', 'email'], 'required', 'message' => 'Este campo es obligatorio.'],
            [['nick', 'email'], 'unique', 'message' => 'Este dato ya está registrado.'],
            [['email'], 'email', 'message' => 'Formato de correo inválido.'],
            [['rol', 'nivel_vip', 'estado_cuenta', 'estado_verificacion', 'notas_internas'], 'string'],
            [['puntos_progreso', 'id_padrino'], 'integer'],
            [['fecha_registro'], 'safe'],
            [['nombre', 'apellido'], 'string', 'max' => 50],
            [['telefono'], 'string', 'max' => 20],
            [['avatar_url', 'foto_dni', 'foto_selfie', 'password_hash', 'password_reset_token', 'access_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['codigo_referido_propio'], 'string', 'max' => 20],
            
            // Regla especial para el padrino (autoreferencia)
            [['id_padrino'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['id_padrino' => 'id']],
        ];
    }

    /**
     * Etiquetas para mostrar en los formularios (Labels)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nick' => 'Usuario (Nick)',
            'email' => 'Correo Electrónico',
            'password_plain' => 'Contraseña',
            'nombre' => 'Nombre',
            'apellido' => 'Apellidos',
            'telefono' => 'Teléfono',
            'rol' => 'Rol',
            'nivel_vip' => 'Nivel VIP',
            'puntos_progreso' => 'Puntos',
            'avatar_url' => 'Avatar',
            'estado_cuenta' => 'Estado Cuenta',
            'estado_verificacion' => 'Verificación Documentos',
            'codigo_referido_propio' => 'Tu Código de Afiliado',
        ];
    }

    /**
     * ALIAS: Permite acceder a $usuario->username redirigiendo a $usuario->nick.
     * Con esto arreglamos el error de que yii busca username en nuestra base se llama nick
     */
    public function getUsername()
    {
        return $this->nick;
    }

    // ------------------------------------------------------------
    // MÉTODOS Necesarios para el Login

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Busca usuario por Nick
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['nick' => $username]);
    }

    /**
     * Valida la contraseña usando el hash de seguridad de Yii2
     * @param string $password contraseña escrita por el usuario
     * @return bool si coincide o no
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Genera el hash de la contraseña antes de guardar
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Genera la clave de autenticación (cookie "recordarme")
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
    
    
    public function getPadrino()
    {
        return $this->hasOne(Usuario::class, ['id' => 'id_padrino']);
    }

    /**
     * Relación para obtener el historial de accesos de este usuario.
     */
    public function getLogsVisitas()
    {
        // Ordenamos por fecha descendente para ver los últimos primero
        return $this->hasMany(\app\models\LogVisita::class, ['id_usuario' => 'id'])
                    ->orderBy(['fecha_hora' => SORT_DESC])
                    ->limit(10); // Solo mostramos las últimas 10 para no saturar
    }

    // -----------------------------------------------
    // Sistema para controlar los permisos
    // -----------------------------------------------

    /**
     * Verifica si el usuario es Administrador.
     * Uso: Yii::$app->user->identity->esAdmin()
     */
    public function esAdmin()
    {
        return $this->rol === 'admin';
    }

    /**
     * Verifica si el usuario es Jugador.
     * Uso: Yii::$app->user->identity->esJugador()
     */
    public function esJugador()
    {
        return $this->rol === 'jugador';
    }

    /**
     * Verifica si el usuario tiene permiso para entrar al Backend (Gestión).
     * cambir esta función aquí si se modifican los roles
     */
    public function puedeAccederBackend()
    {
        return $this->esAdmin(); 
    }

    /**
     * Devuelve true SOLO si  el admin lo ha validado.
     */
    public function esVerificado()
    {
        return $this->estado_verificacion === 'Verificado';
    }
    
    /**
     * Verifica si el usuario puede jugar (no está bloqueado/baneado).
     */
    public function puedeJugar()
    {
        // Si está bloqueado, fuera.
        if ($this->estado_cuenta !== 'Activo') {
            return false;
        }

        // Si no está verificado, tampoco juega.
        if (!$this->esVerificado()) {
            return false;
        }

        return true;
    }

    /**
    * RELACIÓN CON MONEDERO (W2):
    * Permite acceder al saldo del usuario directamente desde la identidad.
    * Ejemplo: Yii::$app->user->identity->monedero->saldo_real
    */
    public function getMonedero()
    {
        // Un usuario tiene un único monedero (hasOne)
        return $this->hasOne(Monedero::class, ['id_usuario' => 'id']);
    }

    /**
    * SEGURIDAD DE CONTRASEÑAS (MODIFICADO POR G2):
    * Este método se ejecuta automáticamente antes de guardar en la base de datos.
    * Resuelve el error "Hash is invalid" al asegurar que la clave siempre se cifre.
    */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Si el campo password_hash contiene texto plano (no empieza por $2y$), lo ciframos
            // Un hash de Yii siempre empieza por $2y$
            if (!empty($this->password_hash) && strpos($this->password_hash, '$2y$') !== 0) {
                // Aplicamos el algoritmo Blowfish a través de la seguridad de Yii2
                $this->password_hash = Yii::$app->security->generatePasswordHash($this->password_hash);
            }
        
            // Para nuevos registros, generamos la clave de autenticación para "Recordarme"
            if ($insert) {
                $this->auth_key = Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }
}
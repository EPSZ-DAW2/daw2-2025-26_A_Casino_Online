<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo para la tabla "logro_usuario".
 * Tabla intermedia que registra qué usuarios tienen qué logros y cuándo los ganaron.
 * 
 * @property int $id_usuario ID del usuario
 * @property int $id_logro ID del logro obtenido
 * @property string $fecha_desbloqueo Fecha y hora en que se consiguió el logro
 * 
 * @property Logro $logro Relación con el modelo Logro
 * @property Usuario $usuario Relación con el modelo Usuario
 */
class LogroUsuario extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'logro_usuario';
    }

    /**
     * Reglas de validación.
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Ambos IDs son necesarios para crear el registro
            [['id_usuario', 'id_logro'], 'required'],
            [['id_usuario', 'id_logro'], 'integer'],

            // La fecha es segura, se suele llenar automáticamente
            [['fecha_desbloqueo'], 'safe'],

            // Clave primaria compuesta: Un usuario no puede tener el mismo logro dos veces
            [
                ['id_usuario', 'id_logro'],
                'unique',
                'targetAttribute' => ['id_usuario', 'id_logro'],
                'message' => 'El usuario ya posee este logro.'
            ],

            // Validar existencia de las claves foráneas
            [['id_logro'], 'exist', 'skipOnError' => true, 'targetClass' => Logro::class, 'targetAttribute' => ['id_logro' => 'id']],
            [['id_usuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['id_usuario' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_usuario' => 'Usuario',
            'id_logro' => 'Logro',
            'fecha_desbloqueo' => 'Fecha de Obtención',
        ];
    }

    /**
     * Relación con la tabla Logro (para saber nombre e icono).
     * @return \yii\db\ActiveQuery
     */
    public function getLogro()
    {
        return $this->hasOne(Logro::class, ['id' => 'id_logro']);
    }

    /**
     * Relación con la tabla Usuario (para saber quién lo ganó).
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuario::class, ['id' => 'id_usuario']);
    }
}

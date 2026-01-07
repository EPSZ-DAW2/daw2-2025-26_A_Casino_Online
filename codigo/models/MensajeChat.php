<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo para la tabla "mensaje_chat".
 * Almacena los mensajes enviados por usuarios en las mesas privadas.
 * 
 * @property int $id ID del mensaje
 * @property int $id_mesa ID de la mesa donde se envió (Null si fuera un chat global/soporte)
 * @property int $id_usuario ID del autor
 * @property string $mensaje Contenido del texto
 * @property int|null $es_ofensivo Flag (0/1) si el sistema detectó malas palabras
 * @property string $fecha_envio Timestamp de envío
 * 
 * @property MesaPrivada $mesa Relación con la mesa
 * @property Usuario $usuario Relación con el autor
 */
class MensajeChat extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mensaje_chat';
    }

    /**
     * Reglas de validación.
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Usuario y Mensaje son obligatorios
            [['id_usuario', 'mensaje'], 'required'],
            [['id_usuario', 'id_mesa', 'es_ofensivo'], 'integer'],
            [['mensaje'], 'string'],
            [['fecha_envio'], 'safe'],

            // Relaciones
            [['id_mesa'], 'exist', 'skipOnError' => true, 'targetClass' => MesaPrivada::class, 'targetAttribute' => ['id_mesa' => 'id']],
            [['id_usuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['id_usuario' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_mesa' => 'Mesa',
            'id_usuario' => 'Usuario',
            'mensaje' => 'Mensaje',
            'es_ofensivo' => 'Es Ofensivo',
            'fecha_envio' => 'Hora',
        ];
    }

    /**
     * Relación con la Mesa Privada.
     * @return \yii\db\ActiveQuery
     */
    public function getMesa()
    {
        return $this->hasOne(MesaPrivada::class, ['id' => 'id_mesa']);
    }

    /**
     * Relación con el usuario autor del mensaje.
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuario::class, ['id' => 'id_usuario']);
    }

    /**
     * Filtro de malas palabras (Lógica G6).
     * Se llama antes de guardarse para sanitizar o marcar el mensaje.
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Diccionario de palabras prohibidas
            $badWords = ['tonto', 'estupido', 'fraude', 'robo', 'idiota'];

            foreach ($badWords as $word) {
                // stripos busca sin importar mayúsculas/minúsculas
                if (stripos($this->mensaje, $word) !== false) {
                    $this->es_ofensivo = 1; // Marcamos para auditoría
                    // Reemplazamos la palabra por asteriscos del mismo largo
                    $this->mensaje = str_ireplace($word, str_repeat('*', strlen($word)), $this->mensaje);
                }
            }
            return true; // Continúa con el guardado
        }
        return false;
    }
}

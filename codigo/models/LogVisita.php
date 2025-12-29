<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "log_visita".
 *
 * @property int $id
 * @property int $id_usuario
 * @property string $direccion_ip
 * @property string|null $dispositivo
 * @property string|null $fecha_hora
 *
 * @property Usuario $usuario
 */
class LogVisita extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log_visita';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_usuario', 'direccion_ip'], 'required'],
            [['id_usuario'], 'integer'],
            [['fecha_hora'], 'safe'],
            [['direccion_ip'], 'string', 'max' => 45],
            [['dispositivo'], 'string', 'max' => 255],
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
            'id_usuario' => 'Id Usuario',
            'direccion_ip' => 'Direccion Ip',
            'dispositivo' => 'Dispositivo',
            'fecha_hora' => 'Fecha Hora',
        ];
    }

    /**
     * Gets query for [[Usuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuario::class, ['id' => 'id_usuario']);
    }
}

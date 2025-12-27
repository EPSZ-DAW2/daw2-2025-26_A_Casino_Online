<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "alerta_fraude".
 *
 * @property int $id
 * @property int|null $id_usuario
 * @property string $tipo Colusión, Bot, Chip Dumping
 * @property string|null $nivel_riesgo
 * @property string|null $estado
 * @property string|null $detalles_tecnicos
 * @property string|null $fecha_detectada
 *
 * @property Usuario $usuario
 */
class AlertaFraude extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alerta_fraude';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_usuario'], 'integer'],
            [['tipo'], 'required'],
            [['nivel_riesgo', 'estado', 'detalles_tecnicos'], 'string'],
            [['fecha_detectada'], 'safe'],
            [['tipo'], 'string', 'max' => 50],
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
            'tipo' => 'Tipo',
            'nivel_riesgo' => 'Nivel Riesgo',
            'estado' => 'Estado',
            'detalles_tecnicos' => 'Detalles Tecnicos',
            'fecha_detectada' => 'Fecha Detectada',
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

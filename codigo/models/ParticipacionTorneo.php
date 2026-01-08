<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "participacion_torneo".
 *
 * @property int $id
 * @property int $id_torneo
 * @property int $id_usuario
 * @property int|null $puntuacion_actual Para Ranking en vivo
 * @property int|null $posicion_final
 * @property float|null $premio_ganado
 *
 * @property Torneo $torneo
 * @property Usuario $usuario
 */
class ParticipacionTorneo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'participacion_torneo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_torneo', 'id_usuario'], 'required'],
            [['id_torneo', 'id_usuario', 'puntuacion_actual', 'posicion_final'], 'integer'],
            [['premio_ganado'], 'number'],
            [['id_torneo'], 'exist', 'skipOnError' => true, 'targetClass' => Torneo::class, 'targetAttribute' => ['id_torneo' => 'id']],
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
            'id_torneo' => 'Id Torneo',
            'id_usuario' => 'Id Usuario',
            'puntuacion_actual' => 'Puntuacion Actual',
            'posicion_final' => 'Posicion Final',
            'premio_ganado' => 'Premio Ganado',
        ];
    }

    /**
     * Gets query for [[Torneo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTorneo()
    {
        return $this->hasOne(Torneo::class, ['id' => 'id_torneo']);
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

<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "torneo".
 *
 * @property int $id
 * @property string $titulo
 * @property int $id_juego_asociado
 * @property string $fecha_inicio
 * @property string $fecha_fin
 * @property float|null $coste_entrada
 * @property float|null $bolsa_premios Premios garantizados
 * @property string|null $estado
 *
 * @property Juego $juegoAsociado
 * @property ParticipacionTorneo[] $participacionTorneos
 */
class Torneo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'torneo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['titulo', 'id_juego_asociado', 'fecha_inicio', 'fecha_fin'], 'required'],
            [['id_juego_asociado'], 'integer'],
            [['fecha_inicio', 'fecha_fin'], 'safe'],
            [['coste_entrada', 'bolsa_premios'], 'number'],
            [['estado'], 'string'],
            [['titulo'], 'string', 'max' => 100],
            [['id_juego_asociado'], 'exist', 'skipOnError' => true, 'targetClass' => Juego::class, 'targetAttribute' => ['id_juego_asociado' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titulo' => 'Titulo',
            'id_juego_asociado' => 'Id Juego Asociado',
            'fecha_inicio' => 'Fecha Inicio',
            'fecha_fin' => 'Fecha Fin',
            'coste_entrada' => 'Coste Entrada',
            'bolsa_premios' => 'Bolsa Premios',
            'estado' => 'Estado',
        ];
    }

    /**
     * Gets query for [[JuegoAsociado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJuegoAsociado()
    {
        return $this->hasOne(Juego::class, ['id' => 'id_juego_asociado']);
    }

    /**
     * Gets query for [[ParticipacionTorneos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParticipacionTorneos()
    {
        return $this->hasMany(ParticipacionTorneo::class, ['id_torneo' => 'id']);
    }
    // Relación con la tabla Juego (G3)
    // Esto permite usar $torneo->juego->nombre
    public function getJuego()
    {
        return $this->hasOne(Juego::class, ['id' => 'id_juego_asociado']);
    }

    // Relación con las inscripciones
    public function getParticipaciones()
    {
        return $this->hasMany(ParticipacionTorneo::class, ['id_torneo' => 'id']);
    }
}

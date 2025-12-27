<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "juego".
 *
 * @property int $id
 * @property string $nombre
 * @property string $proveedor NetEnt, Playtech, etc.
 * @property string $tipo
 * @property string|null $tematica Egipto, Futuro, etc.
 * @property float $rtp Porcentaje de retorno teórico
 * @property string|null $url_caratula Para el Grid visual
 * @property int|null $activo Interruptor Activo/Inactivo
 * @property int|null $es_nuevo Etiqueta Nuevo
 * @property int|null $en_mantenimiento
 * @property float|null $tasa_pago_actual Estadística Hot/Cold
 * @property string|null $estado_racha
 *
 * @property HistorialPartida[] $historialPartidas
 * @property Torneo[] $torneos
 */
class Juego extends \yii\db\ActiveRecord
{
    // Variable auxiliar para subir la foto (no existe en la base de datos)
    public $archivoImagen;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'juego';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'proveedor', 'tipo', 'rtp'], 'required'],
            [['tipo', 'estado_racha'], 'string'],
            [['rtp', 'tasa_pago_actual'], 'number'],
            [['activo', 'es_nuevo', 'en_mantenimiento'], 'integer'],
            [['nombre'], 'string', 'max' => 100],
            [['proveedor', 'tematica'], 'string', 'max' => 50],
            [['url_caratula'], 'string', 'max' => 255],
            // Regla para validar que lo que suben es una imagen válida
            [['archivoImagen'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg','checkExtensionByMimeType' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'proveedor' => 'Proveedor',
            'tipo' => 'Tipo',
            'tematica' => 'Tematica',
            'rtp' => 'Rtp',
            'url_caratula' => 'Url Caratula',
            'activo' => 'Activo',
            'es_nuevo' => 'Es Nuevo',
            'en_mantenimiento' => 'En Mantenimiento',
            'tasa_pago_actual' => 'Tasa Pago Actual',
            'estado_racha' => 'Estado Racha',
        ];
    }

    /**
     * Gets query for [[HistorialPartidas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHistorialPartidas()
    {
        return $this->hasMany(HistorialPartida::class, ['id_juego' => 'id']);
    }

    /**
     * Gets query for [[Torneos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTorneos()
    {
        return $this->hasMany(Torneo::class, ['id_juego_asociado' => 'id']);
    }
}

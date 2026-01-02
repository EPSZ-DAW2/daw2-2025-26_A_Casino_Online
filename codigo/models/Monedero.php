<?php

namespace app\models;

use Yii;

/**
 * Modelo para la tabla "monedero".
 */
class Monedero extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'monedero';
    }

    public function rules()
    {
        return [
            [['id_usuario'], 'required'],
            [['id_usuario'], 'integer'],
            [['saldo_real', 'saldo_bono'], 'number'],
            [['divisa'], 'string', 'max' => 3],
            [['id_usuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['id_usuario' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'saldo_real' => 'Saldo Retirable (€)',
            'saldo_bono' => 'Saldo de Bono (€)',
            'divisa' => 'Moneda',
        ];
    }

    // Lógica de seguridad para asegurar la existencia del monedero
    public static function obtenerOConfigurar($usuarioId) {
        $monedero = self::findOne(['id_usuario' => $usuarioId]);
        if (!$monedero) {
            $monedero = new self();
            $monedero->id_usuario = $usuarioId;
            $monedero->saldo_real = 0.00;
            $monedero->saldo_bono = 0.00;
            $monedero->save();
        }
        return $monedero;
    }

    /**
     * Relación con el usuario
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuario::class, ['id' => 'id_usuario']);
    }
}
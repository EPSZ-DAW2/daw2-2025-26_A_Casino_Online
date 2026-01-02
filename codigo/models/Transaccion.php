<?php

namespace app\models;

use Yii;

/**
 * Modelo para la tabla "transaccion".
 */
class Transaccion extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'transaccion';
    }

    public function rules()
    {
        return [
            [['id_usuario', 'tipo_operacion', 'cantidad'], 'required'],
            [['id_usuario'], 'integer'],
            [['tipo_operacion', 'categoria', 'metodo_pago', 'estado'], 'string'],
            [['cantidad'], 'number'],
            [['fecha_hora'], 'safe'],
            [['referencia_externa'], 'string', 'max' => 100],
            [['id_usuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['id_usuario' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID Transacción',
            'tipo_operacion' => 'Tipo',
            'categoria' => 'Categoría de Juego',
            'cantidad' => 'Importe (€)',
            'metodo_pago' => 'Método',
            'estado' => 'Estado de la Operación',
            'fecha_hora' => 'Fecha y Hora',
        ];
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuario::class, ['id' => 'id_usuario']);
    }
}
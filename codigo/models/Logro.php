<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo para la tabla "logro".
 * Representa los logros (badges/trofeos) que los usuarios pueden desbloquear.
 * 
 * @property int $id Identificador único del logro
 * @property string $nombre Nombre descriptivo del logro (Ej: "Ganador de Torneo")
 * @property string|null $descripcion Explicación de cómo conseguirlo
 * @property string|null $icono_trofeo Ruta relativa o nombre del icono (Ej: "copa_oro.png")
 * 
 * @property LogroUsuario[] $logroUsuarios Relación con la tabla intermedia usuario-logro
 * @property Usuario[] $usuarios Usuarios que han obtenido este logro
 */
class Logro extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'logro';
    }

    /**
     * Reglas de validación para los formularios.
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // El nombre es obligatorio y no puede exceder 50 caracteres
            [['nombre'], 'required', 'message' => 'Debes asignar un nombre al logro.'],
            [['nombre'], 'string', 'max' => 50],
            
            // La descripción es texto opcional
            [['descripcion'], 'string'],
            
            // El icono es una ruta de archivo (string), opcional
            [['icono_trofeo'], 'string', 'max' => 255],
        ];
    }

    /**
     * Etiquetas de atributos para mostrar en las vistas.
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre del Logro',
            'descripcion' => 'Descripción',
            'icono_trofeo' => 'Icono (URL/Archivo)',
        ];
    }

    /**
     * Relación: Obtener los registros de la tabla intermedia.
     * Un logro puede estar asignado a múltiples usuarios.
     * @return \yii\db\ActiveQuery
     */
    public function getLogroUsuarios()
    {
        return $this->hasMany(LogroUsuario::class, ['id_logro' => 'id']);
    }

    /**
     * Relación: Obtener los usuarios que tienen este logro.
     * Relación directa a través de la tabla intermedia logrousuario.
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarios()
    {
        return $this->hasMany(Usuario::class, ['id' => 'id_usuario'])
                    ->viaTable('logro_usuario', ['id_logro' => 'id']);
    }
}

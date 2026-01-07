<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo para la tabla "mesa_privada".
 * Gestiona las salas de juego creadas por usuarios (mesas privadas).
 * 
 * @property int $id Identificador único
 * @property int $id_anfitrion Usuario creador de la mesa (dueño)
 * @property string|null $tipo_juego Juego que se va a jugar (Ej: 'Poker', 'Blackjack')
 * @property string|null $contrasena_acceso Clave para entrar (hash o texto simple dependiendo del nivel de seguridad deseado)
 * @property string|null $estado_mesa Estado actual: 'Abierta', 'Jugando', 'Cerrada'
 * 
 * @property Usuario $anfitrion Relación con el usuario creador
 * @property MensajeChat[] $mensajesChat Historial de mensajes en esta mesa
 */
class MesaPrivada extends ActiveRecord
{
    // Constantes para estados de mesa facilitar control en lógica
    const ESTADO_ABIERTA = 'Abierta';
    const ESTADO_JUGANDO = 'Jugando';
    const ESTADO_CERRADA = 'Cerrada';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mesa_privada';
    }

    /**
     * Reglas de validación.
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // El anfitrión es obligatorio
            [['id_anfitrion'], 'required'],
            [['id_anfitrion'], 'integer'],

            // Campos de texto
            [['tipo_juego'], 'string', 'max' => 50],
            [['contrasena_acceso'], 'string', 'max' => 255],

            // Estado con rango definido (Enum en base de datos)
            [['estado_mesa'], 'string'],
            ['estado_mesa', 'in', 'range' => [self::ESTADO_ABIERTA, self::ESTADO_JUGANDO, self::ESTADO_CERRADA]],

            // Validar que el anfitrión exista
            [['id_anfitrion'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['id_anfitrion' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID Mesa',
            'id_anfitrion' => 'Creador (Anfitrión)',
            'tipo_juego' => 'Juego',
            'contrasena_acceso' => 'Contraseña',
            'estado_mesa' => 'Estado',
        ];
    }

    /**
     * Relación con el usuario que creó la mesa (Anfitrión).
     * @return \yii\db\ActiveQuery
     */
    public function getAnfitrion()
    {
        return $this->hasOne(Usuario::class, ['id' => 'id_anfitrion']);
    }

    /**
     * Relación con el chat de la mesa.
     * Recupera todos los mensajes enviados en esta sala.
     * @return \yii\db\ActiveQuery
     */
    public function getMensajesChat()
    {
        return $this->hasMany(MensajeChat::class, ['id_mesa' => 'id']);
    }

    /**
     * Método helper para verificar la contraseña de entrada.
     * @param string $passwordIntento La contraseña que escribe el usuario invitado.
     * @return bool True si coincide, False si no.
     */
    public function validarContrasena($passwordIntento)
    {
        // En este prototipo usamos comparación directa. 
        // Si se cifrara la contraseña de la mesa, aquí usaríamos Yii::$app->security->validatePassword
        return $this->contrasena_acceso === $passwordIntento;
    }
}

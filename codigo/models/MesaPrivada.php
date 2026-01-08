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
    // Constantes para definir el estado de la mesa (Lógica de Negocio G6)
    const ESTADO_ABIERTA = 'Abierta'; // Se puede entrar
    const ESTADO_JUGANDO = 'Jugando'; // Partida en curso
    const ESTADO_CERRADA = 'Cerrada'; // Ya no existe o finalizó

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mesa_privada';
    }

    /**
     * Reglas de validación Yii2
     * Define qué datos son obligatorios y sus formatos.
     */
    public function rules()
    {
        return [
            [['id_anfitrion', 'tipo_juego'], 'required', 'message' => 'El anfitrión y el tipo de juego son obligatorios.'],
            [['id_anfitrion'], 'integer'],
            // Enumerado para asegurar consistencia en la base de datos
            [['estado_mesa'], 'in', 'range' => [self::ESTADO_ABIERTA, self::ESTADO_JUGANDO, self::ESTADO_CERRADA]],
            [['tipo_juego'], 'string', 'max' => 50],
            // La contraseña es opcional, pero si se pone, máx 255 chars
            [['contrasena_acceso'], 'string', 'max' => 255],
            // Verifica que el anfitrión exista en la tabla Usuarios (Integridad Referencial)
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
     * Validación de Contraseña de Sala (G6)
     * Compara la contraseña introducida por el invitado con la almacenada.
     * @param string $passwordIntento La contraseña que escribe el usuario
     * @return bool True si es correcta o si la mesa es pública (sin pass)
     */
    public function validarContrasena($passwordIntento)
    {
        // Si la mesa no tiene contraseña, cualquiera entra
        if (empty($this->contrasena_acceso)) {
            return true;
        }
        // Comparación directa (MVP)
        return $this->contrasena_acceso === $passwordIntento;
    }
}

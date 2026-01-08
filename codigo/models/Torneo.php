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

    /**
     * Comprueba si el torneo ha caducado y, de ser así, lo cierra y reparte premios.
     * @return bool True si se cerró, False si sigue activo.
     */
    public function comprobarFinalizacionAutomatica()
    {
        // 1. Si no está 'En Curso', no hacemos nada
        if ($this->estado !== 'En Curso') {
            return false;
        }

        // 2. Comprobamos la fecha
        // Si la fecha actual es MENOR que el fin, aún no ha terminado.
        if (time() < strtotime($this->fecha_fin)) {
            return false;
        }

        // --- AUTOMATIZACIÓN DEL CIERRE ---
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Buscar al ganador
            $ganadorParticipacion = ParticipacionTorneo::find()
                ->where(['id_torneo' => $this->id])
                ->orderBy(['puntuacion_actual' => SORT_DESC])
                ->one();

            if ($ganadorParticipacion) {
                $premio = $this->bolsa_premios;
                
                // Dar dinero
                $monedero = Monedero::findOne(['id_usuario' => $ganadorParticipacion->id_usuario]);
                if ($monedero) {
                    $monedero->saldo_real += $premio;
                    $monedero->save();
                }

                // Guardar datos ganadores
                $ganadorParticipacion->posicion_final = 1;
                $ganadorParticipacion->premio_ganado = $premio;
                $ganadorParticipacion->save();

                // Crear transacción
                $trans = new Transaccion();
                $trans->id_usuario = $ganadorParticipacion->id_usuario;
                $trans->tipo_operacion = 'Premio';
                $trans->categoria = 'Torneo'; // Importante para la gráfica
                $trans->cantidad = $premio;
                $trans->estado = 'Completado';
                $trans->referencia_externa = "Ganador Torneo: " . $this->titulo;
                $trans->save();
            }

            // Cerrar el torneo
            $this->estado = 'Finalizado';
            $this->save();

            $transaction->commit();
            return true; // ¡Se cerró automáticamente!

        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }
}

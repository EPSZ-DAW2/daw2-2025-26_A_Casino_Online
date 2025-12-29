<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Juego;

/**
 * JuegoSearch represents the model behind the search form of `app\models\Juego`.
 */
class JuegoSearch extends Juego
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'activo', 'es_nuevo', 'en_mantenimiento'], 'integer'],
            [['nombre', 'proveedor', 'tipo', 'tematica', 'url_caratula', 'estado_racha'], 'safe'],
            [['rtp', 'tasa_pago_actual'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Juego::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'rtp' => $this->rtp,
            'activo' => $this->activo,
            'es_nuevo' => $this->es_nuevo,
            'en_mantenimiento' => $this->en_mantenimiento,
            'tasa_pago_actual' => $this->tasa_pago_actual,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'proveedor', $this->proveedor])
            ->andFilterWhere(['like', 'tipo', $this->tipo])
            ->andFilterWhere(['like', 'tematica', $this->tematica])
            ->andFilterWhere(['like', 'url_caratula', $this->url_caratula])
            ->andFilterWhere(['like', 'estado_racha', $this->estado_racha]);

        return $dataProvider;
    }
}

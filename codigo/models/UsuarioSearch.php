<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Usuario;

/**
 * UsuarioSearch represents the model behind the search form of `app\models\Usuario`.
 */
class UsuarioSearch extends Usuario
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'puntos_progreso', 'id_padrino'], 'integer'],
            [['nick', 'email', 'password_hash', 'auth_key', 'password_reset_token', 'access_token', 'rol', 'nombre', 'apellido', 'telefono', 'fecha_registro', 'avatar_url', 'nivel_vip', 'estado_cuenta', 'estado_verificacion', 'foto_dni', 'foto_selfie', 'notas_internas', 'codigo_referido_propio'], 'safe'],
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
        $query = Usuario::find();

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
            'fecha_registro' => $this->fecha_registro,
            'puntos_progreso' => $this->puntos_progreso,
            'id_padrino' => $this->id_padrino,
        ]);

        $query->andFilterWhere(['like', 'nick', $this->nick])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'access_token', $this->access_token])
            ->andFilterWhere(['like', 'rol', $this->rol])
            ->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'apellido', $this->apellido])
            ->andFilterWhere(['like', 'telefono', $this->telefono])
            ->andFilterWhere(['like', 'avatar_url', $this->avatar_url])
            ->andFilterWhere(['like', 'nivel_vip', $this->nivel_vip])
            ->andFilterWhere(['like', 'estado_cuenta', $this->estado_cuenta])
            ->andFilterWhere(['like', 'estado_verificacion', $this->estado_verificacion])
            ->andFilterWhere(['like', 'foto_dni', $this->foto_dni])
            ->andFilterWhere(['like', 'foto_selfie', $this->foto_selfie])
            ->andFilterWhere(['like', 'notas_internas', $this->notas_internas])
            ->andFilterWhere(['like', 'codigo_referido_propio', $this->codigo_referido_propio]);

        return $dataProvider;
    }
}

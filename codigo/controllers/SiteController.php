<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SignupForm;
use yii\web\UploadedFile;
use app\models\Juego;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Si no has creado juegos aún, esto devolverá una lista vacía.
        $juegosDestacados = Juego::find()
            ->where(['activo' => 1])
            ->limit(4) // Solo queremos 4 para que quede bonito
            ->orderBy(['id' => SORT_DESC]) // Los más nuevos primero
            ->all();

        return $this->render('index', [
            'juegosDestacados' => $juegosDestacados,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Registro de nuevos usuarios.
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Gracias por registrarte. Por favor inicia sesión.');
            return $this->redirect(['site/login']);
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Muestra el perfil del usuario, su barra VIP y permite editar datos.
     */
    public function actionPerfil()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['login']);
        }

        /** @var \app\models\Usuario $model */
        $model = Yii::$app->user->identity;
        
        if ($this->request->isPost && $model->load($this->request->post())) {
            
            // --- Lógica del Avatar ---
            $archivoAvatar = \yii\web\UploadedFile::getInstance($model, 'avatar_url');
            if ($archivoAvatar) {
                $nombreArchivo = 'avatar_' . $model->id . '_' . uniqid() . '.' . $archivoAvatar->extension;
                if ($archivoAvatar->saveAs('uploads/' . $nombreArchivo)) {
                    $model->avatar_url = $nombreArchivo;
                }
            }

            // --- Lógica de Documentos (DNI y Selfie) ---
            $archivoDNI = \yii\web\UploadedFile::getInstance($model, 'foto_dni');
            $archivoSelfie = \yii\web\UploadedFile::getInstance($model, 'foto_selfie');
            $documentosSubidos = false;

            if ($archivoDNI) {
                $nombreDNI = 'dni_' . $model->id . '_' . uniqid() . '.' . $archivoDNI->extension;
                if ($archivoDNI->saveAs('uploads/' . $nombreDNI)) {
                    $model->foto_dni = $nombreDNI;
                    $documentosSubidos = true;
                }
            }

            if ($archivoSelfie) {
                $nombreSelfie = 'selfie_' . $model->id . '_' . uniqid() . '.' . $archivoSelfie->extension;
                if ($archivoSelfie->saveAs('uploads/' . $nombreSelfie)) {
                    $model->foto_selfie = $nombreSelfie;
                    $documentosSubidos = true;
                }
            }

            // Si subió documentos nuevos, cambiamos estado a "Pendiente" para que el Admin lo revise
            if ($documentosSubidos) {
                $model->estado_verificacion = 'Pendiente';
            }

            // Guardamos todo
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Perfil actualizado. Si has subido documentos, serán revisados pronto.');
                return $this->refresh();
            }
        }

        return $this->render('perfil', [
            'model' => $model,
        ]);
    }
}

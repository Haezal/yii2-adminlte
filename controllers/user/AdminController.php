<?php
/**
 * Created by PhpStorm.
 * User: haezalmusa
 * Date: 10/02/2018
 * Time: 11:09 AM
 */

namespace app\controllers\user;

use app\models\AuthAssignment;
use dektrium\user\models\Profile;
use dektrium\user\models\User;
use app\models\UserSearch;
use Yii;

use dektrium\user\controllers\AdminController as BaseAdminController;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\rbac\DbManager;

class AdminController extends BaseAdminController
{

    /**
     * Switches to the given user for the rest of the Session.
     * When no id is given, we switch back to the original admin user
     * that started the impersonation.
     *
     * @param int $id
     *
     * @return string
     */
    public function actionSwitch($id = null)
    {
        if (!$this->module->enableImpersonateUser) {
            throw new ForbiddenHttpException(Yii::t('user', 'Impersonate user is disabled in the application configuration'));
        }

        if(!$id && Yii::$app->session->has(self::ORIGINAL_USER_SESSION_KEY)) {
            $user = $this->findModel(Yii::$app->session->get(self::ORIGINAL_USER_SESSION_KEY));

            Yii::$app->session->remove(self::ORIGINAL_USER_SESSION_KEY);
        } else {
//            if (!Yii::$app->user->identity->isAdmin) {
//                throw new ForbiddenHttpException;
//            }

            $user = $this->findModel($id);
            Yii::$app->session->set(self::ORIGINAL_USER_SESSION_KEY, Yii::$app->user->id);
        }

        $event = $this->getUserEvent($user);

        $this->trigger(self::EVENT_BEFORE_IMPERSONATE, $event);

        Yii::$app->user->switchIdentity($user, 3600);

        $this->trigger(self::EVENT_AFTER_IMPERSONATE, $event);

        return $this->goHome();
    }

    /**
     * Lists all User models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'actions-redirect');
        $searchModel  = \Yii::createObject(\dektrium\user\models\UserSearch::className());
        $dataProvider = $searchModel->search(\Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }


    /**
     * Create new user with profile and role
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        /** @var User $user */
        $user = \Yii::createObject([
            'class'    => User::className(),
            'scenario' => 'create',
        ]);

        /** @var $profile */
        $profile = new Profile();
        $assignment = new AuthAssignment();

        $event = $this->getUserEvent($user);

        $this->performAjaxValidation($user);

        $this->trigger(self::EVENT_BEFORE_CREATE, $event);
        if ($user->load(\Yii::$app->request->post()) && $assignment->load(\Yii::$app->request->post())) {

            $valid = $user->validate();
            $valid = $valid && $profile->validate();

            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();

                try {

                    $user->create();

                    $profile = $user->profile;
                    $profile->load(\Yii::$app->request->post());
                    $profile->save();

                    $role = Yii::$app->authManager->getRole($assignment->item_name);
                    $r = new DbManager();
                    $r->assign($role, $user->id);

                    $transaction->commit();

                    \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been created'));
                    $this->trigger(self::EVENT_AFTER_CREATE, $event);
                    return $this->redirect(['update', 'id' => $user->id]);
                }
                catch (Exception $e) {
                    $transaction->rollBack();
                    die($e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'user' => $user,
            'roles' => $this->getRoles(),
            'profile' => $profile,
            'assignment' => $assignment,
        ]);
    }

    /**
     * Updates an existing User model.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);
        $profile = $user->profile;
        $user->scenario = 'update';
        $assignment = AuthAssignment::find()->where(['user_id' => $id])->one();
        if (!$assignment)
        {
            $assignment = new AuthAssignment();
        }
        $event = $this->getUserEvent($user);

        $this->performAjaxValidation($user);

        $this->trigger(self::EVENT_BEFORE_UPDATE, $event);
        if ($user->load(\Yii::$app->request->post()) && $user->save()) {
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Account details have been updated'));
            $this->trigger(self::EVENT_AFTER_UPDATE, $event);
            return $this->refresh();
        }

        return $this->render('_account', [
            'user' => $user,
            'profile' => $profile,
            'assignment' => $assignment,
            'roles' => $this->getRoles(),
        ]);
    }

    /**
     * Updates an existing profile.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdateProfile($id)
    {
        Url::remember('', 'actions-redirect');
        $user    = $this->findModel($id);
        $profile = $user->profile;

        $assignment = AuthAssignment::find()->where(['user_id' => $id])->one();
        if (!$assignment)
        {
            $assignment = new AuthAssignment();
        }


        if ($profile == null) {
            $profile = \Yii::createObject(Profile::className());
            $profile->link('user', $user);
        }
        $event = $this->getProfileEvent($profile);

        $this->performAjaxValidation($profile);

        $this->trigger(self::EVENT_BEFORE_PROFILE_UPDATE, $event);

        if ($profile->load(\Yii::$app->request->post()) && $assignment->load(\Yii::$app->request->post())) {

            if ($profile->save())
            {
                Yii::$app->authManager->revokeAll($id);

                $role = Yii::$app->authManager->getRole($assignment->item_name);
                $r = new DbManager();
                $r->assign($role, $id);


                \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Profile details have been updated'));
                $this->trigger(self::EVENT_AFTER_PROFILE_UPDATE, $event);
                return $this->refresh();
            }

        }


        return $this->render('_profile', [
            'user'    => $user,
            'profile' => $profile,
            'assignment' => $assignment,
            'roles' => $this->getRoles(),
        ]);
    }

    /**
     * Get roles list for dropdown value
     *
     * @return array
     */
    protected function getRoles() {
        $get_roles = Yii::$app->authManager->getRoles();

        $roles = array();
        foreach ($get_roles as $key => $value)
        {
            if ($value->name != 'admin')
                $roles[$value->name] = $value->description;
        }

        return $roles;
    }
}
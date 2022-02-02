<?php

/**
 * @var $this yii\web\View
 * @var $model webvimark\modules\UserManagement\models\forms\LoginForm
 */

use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use webvimark\modules\UserManagement\assets\LoginAsset;

$loginAsset = LoginAsset::register($this);
?>

<?php $form = ActiveForm::begin([
  'id'      => 'form-signin',
  'options' => ['autocomplete' => 'off'],
  'validateOnBlur' => false,
  'fieldConfig' => [
    'template' => "{input}{label}{error}",
  ],
]) ?>

<div class="text-center mb-4">
  <?= Html::img($loginAsset->baseUrl . '/img/bootstrap-solid.svg', ['class' => 'mb-4', 'alt' => '', 'width' => '72', 'height' => '72']); ?>
  <?= Html::tag('h1', Yii::$app->name, ['class' => 'h3 mb-3 font-weight-normal']) ?>
</div>

<?= $form->field($model, 'username', ['options' => ['class' => 'form-label-group'], 'errorOptions' => ['class' => 'invalid-tooltip']])
  ->textInput(['placeholder' => $model->getAttributeLabel('username'), 'autocomplete' => 'off', 'required' => 'true']) ?>

<?= $form->field($model, 'password', ['options' => ['class' => 'form-label-group'], 'errorOptions' => ['class' => 'invalid-tooltip']])
  ->passwordInput(['placeholder' => $model->getAttributeLabel('password'), 'autocomplete' => 'off', 'required' => 'true']) ?>

<?= (isset(Yii::$app->user->enableAutoLogin) && Yii::$app->user->enableAutoLogin) ? $form->field($model, 'rememberMe')->checkbox(['value' => true]) : '' ?>

<?= Html::submitButton(UserManagementModule::t('front', 'Login'), ['class' => 'btn btn-lg btn-primary btn-block']) ?>

<div class="text-center mt-2 mb-4 d-flex">
  <div>
    <?= GhostHtml::a(UserManagementModule::t('front', "Registration"),  ['/user-management/auth/registration']) ?>
  </div>
  <div class="ml-auto">
    <?= GhostHtml::a(UserManagementModule::t('front', "Forgot password"), ['/user-management/auth/password-recovery']) ?>
  </div>
</div>

<?php ActiveForm::end() ?>
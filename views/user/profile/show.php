<?php
/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \dektrium\user\models\Profile $profile
 */

$this->title = empty($profile->name) ? Html::encode($profile->user->username) : Html::encode($profile->name);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-sm-6 col-md-3">
        <?= $this->render('../settings/_menu') ?>
    </div>
    <div class="col-sm-6 col-md-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?= Html::encode($this->title) ?>
            </div>

                <table class="table">
                    <tr>
                        <th style="width: 200px;">Email</th>
                        <td>
                            <?php if (!empty($profile->public_email)): ?>
                                <?= Html::a(Html::encode($profile->public_email), 'mailto:' . Html::encode($profile->public_email)) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Branch</th>
                        <td>
                            <?php if (!empty($profile->branch)): ?>
                                <?= Html::encode($profile->branch->name) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Qualifiation</th>
                        <td>
                            <?php if (!empty($profile->qualificationType)): ?>
                                <?= Html::encode($profile->qualificationType->name) ?>
                            <?php endif; ?>

                            <?php if (!empty($profile->qualification)): ?>
                                - <?= Html::encode($profile->qualification) ?>
                            <?php endif; ?>
                        </td>
                    </tr>

                </table>

        </div>
    </div>
</div>

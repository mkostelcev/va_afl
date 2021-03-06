<?php
use yii\helpers\BaseHtml;?>

<h1 class="register-header">
    <?php if (Yii::$app->request->get(
            'lang'
        ) == 'RU'
    ): ?>Регистрация
    <?php else: ?>Sign Up
    <?php endif; ?>
    <small><?php if (Yii::$app->request->get(
                'lang'
            ) == 'RU'
        ): ?>Создайте свой аккаунт в ВА "АФЛ". Это бесплатно и навсегда.<?php else: ?>Create your VA AFL Account. It’s free and always will be.<?php endif; ?></small>
</h1>
<!-- end register-header -->
<!-- begin register-content -->
<div class="register-content">

    <form action="<?= Yii::$app->request->url ?>" method="POST" class="margin-bottom-0">
        <label class="control-label">Email <?php if (Yii::$app->request->get(
                    'lang'
                ) == 'RU'
            ): ?>и язык
            <?php else: ?>and language
            <?php endif; ?></label>
        <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" />
        <div class="row row-space-10">
            <div class="col-md-6 m-b-15">
                <input required type="text" name="email" class="form-control" placeholder="Email address">
            </div>
            <div class="col-md-6 m-b-15">
                <label>
                    <select name="language" class="form-control">
                        <option selected="selected" value="EN">English</option>
                        <option value="RU">Русский</option>
                    </select>
                </label>
            </div>
        </div>
        <div class="register-buttons">
            <button type="submit" class="btn btn-primary btn-block btn-lg"><?php if (Yii::$app->request->get(
                        'lang'
                    ) == 'RU'
                ): ?>
                    Зарегистрироваться
                <?php else: ?>
                    Sign Up
                <?php endif; ?></button>
        </div>
        <hr>
        <p class="text-center text-inverse">
            VA AFL
            <br/>&copy; 2012-<?= date('Y') ?>
        </p>
    </form>
</div>
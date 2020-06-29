<?= form_open(current_url(),
    [
        'role' => 'form',
        'method' => 'POST',
        'data-request' => 'account::onLogin',
    ]
); ?>

    <div class="form-group">
        <div class="input-group">
            <input
                type="text"
                name="email"
                id="login-email"
                class="form-control input-lg"
                placeholder="<?= lang('igniter.user::default.settings.label_email'); ?>"
                autofocus=""
                required
            />
            <span class="input-group-addon">
            <span class="input-group-text">@</span>
        </span>
        </div>
        <?= form_error('email', '<span class="text-danger">', '</span>'); ?>
    </div>

    <div class="form-group">
        <div class="input-group">
            <input
                type="password"
                name="password"
                id="login-password"
                class="form-control input-lg"
                placeholder="<?= lang('igniter.user::default.login.label_password'); ?>"
                required
            />
            <span class="input-group-addon">
            <span class="input-group-text"><i class="fa fa-lock"></i></span>
        </span>
        </div>
        <?= form_error('password', '<span class="text-danger">', '</span>'); ?>
    </div>

    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input
                id="rememberLogin"
                type="checkbox"
                name="newsletter"
                value="1"
                class="custom-control-input" <?= set_checkbox('remember', '1'); ?>
            >
            <label class="custom-control-label" for="rememberLogin">
                <?= lang('igniter.user::default.login.label_remember'); ?>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-12">
                <button
                    type="submit"
                    class="btn btn-primary btn-block btn-lg"
                    data-attach-loading
                ><?= lang('igniter.user::default.login.button_login'); ?></button>
            </div>
        </div>
    </div>

<?= form_close(); ?>
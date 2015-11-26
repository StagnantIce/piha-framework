<div class="form-wrapper">

    <h3>
        <span>Авторизация</span>
    </h3>

    <?= $form->start(array('action' => $this->url()));?>
    <?= $form->fieldGroup('EMAIL'); ?>
    <?= $form->fieldGroup('PASSWORD'); ?>
    <div class="checkbox">
        <label><input type="checkbox" class="custom-checkbox" name="UserLogin[rememberMe]" id="remember-password" value="1"/> Remember me</label>
    </div>
    <div class="forgot-password" style="float: right;">
        <a href="<?=$this->url('auth/recovery');?>">Я забыл свой пароль</a>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
    <?= $form->end();?>

</div>

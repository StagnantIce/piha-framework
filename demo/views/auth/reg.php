<div class="form-wrapper">

    <h3>
        <span>Регистрация</span>
    </h3>

    <?= $form->start(array('action' => $this->url()));?>
    <?= $form->fieldGroup('LOGIN'); ?>
    <?= $form->fieldGroup('EMAIL'); ?>
    <?= $form->fieldGroup('PASSWORD'); ?>
    <?= $form->fieldGroup('CONFIRM_PASSWORD', array('label' => 'Повтор пароля')); ?>
    <?= $form->submit(array('class' => 'btn btn-primary', 'value' => 'Зарегистрироваться'));?>
    <?= $form->end();?>

</div>

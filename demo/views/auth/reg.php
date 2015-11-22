<div class="form-wrapper">

    <h3>
        <span>Регистрация</span>
    </h3>

	<?= $form->start(array('action' => $this->url()));?>
    <?= $form->textGroup(array('name' => 'LOGIN')); ?>
    <?= $form->emailGroup(array('name' => 'EMAIL')); ?>
    <?= $form->passwordGroup(array('name' => 'PASSWORD')); ?>
    <?= $form->passwordGroup(array('name' => 'CONFIRM_PASSWORD', 'label' => 'Повтор пароля')); ?>
    <?= $form->submit(array('class' => 'btn btn-primary', 'value' => 'Зарегистрироваться'));?>
	<?= $form->end();?>

</div>

<div class="user_forms">
	<form class="form-horizontal" role="form" method="post">
		<input type="hidden" name="login_checker" value="">
		<div class="form-group">
			<div class="col-sm-offset-4 col-sm-8">
				<h1><span class="fa fa-sign-in"></span><?php echo $this->Get_variable('username') ?></h1>
			</div>
		</div>
		<?php echo $errors_html ?>
		<div class="form-group <?php echo $errors['input_login']['CLASS'] ?>">
			<label for="inputLogin" class="col-sm-4 control-label"><?php echo $this->Get_variable('username') ?></label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="inputLogin" name="user_login" placeholder="<?php echo $this->Get_variable('username') ?>">
				<?php echo $errors['input_login']['TEXT'] ?>
			</div>
		</div>
		<div class="form-group last_group <?php echo $errors['input_password']['CLASS'] ?>">
			<label for="inputPassword" class="col-sm-4 control-label"><?php echo $this->Get_variable('password') ?></label>
			<div class="col-sm-8">
				<input type="password" class="form-control" id="inputPassword" name="user_password" placeholder="<?php echo $this->Get_variable('password') ?>">
				<?php echo $errors['input_password']['TEXT'] ?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-4 col-sm-8">
				<a href="<?php echo $register_link ?>" class="btn btn-link" role="button"><?php echo $this->Get_variable('register') ?></a>
				<a href="<?php echo $pass_change_link ?>" class="btn btn-link" role="button"><?php echo $this->Get_variable('forgot_password') ?></a>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-4 col-sm-8">
				<button type="submit" class="btn_green" name="login"><?php echo $this->Get_variable('btn_login') ?></button>
			</div>
		</div>
	</form>
	<div class="bottom"></div>
</div>
<?php


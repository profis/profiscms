<?php
/**
 * @var PC_plugin_login_form_widget $this
 * @var string $tpl_group
 * @var string $errors_html
 * @var string $pass_change_link
 * @var string $remind_username_link
 * @var string $register_link
 */
?>
<div class="user_forms">
	<form class="form-horizontal" role="form" method="post">
		<input type="hidden" name="redirect" value="<?php echo (isset($this->_config['redirect_url']) && !empty($this->_config['redirect_url'])) ? $this->_config['redirect_url'] : htmlspecialchars($this->site->Get_home_link()); ?>">
		<div class="form-group">
			<div class="col-sm-offset-4 col-sm-8">
				<h1><span class="fa fa-sign-in"></span><?php echo $this->Get_variable('sign_in') ?></h1>
			</div>
		</div>
		<div class="form-group">
			<label for="inputLogin" class="col-sm-4 control-label"><?php echo $this->Get_variable('username') ?></label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="inputLogin" name="user_login" placeholder="<?php echo $this->Get_variable('username') ?>">
			</div>
		</div>
		<div class="form-group last_group">
			<label for="inputPassword" class="col-sm-4 control-label"><?php echo $this->Get_variable('password') ?></label>
			<div class="col-sm-8">
				<input type="password" class="form-control" id="inputPassword" name="user_password" placeholder="<?php echo $this->Get_variable('password') ?>">
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-4 col-sm-8">
				<?php if( $register_link ) { ?><a href="<?php echo htmlspecialchars($register_link); ?>" class="btn btn-link" role="button"><?php echo $this->Get_variable('register') ?></a><?php } ?>
				<?php if( $pass_change_link ) { ?><a href="<?php echo htmlspecialchars($pass_change_link); ?>" class="btn btn-link" role="button"><?php echo $this->Get_variable('forgot_password') ?></a><?php } ?>
				<?php if( $remind_username_link ) { ?><a href="<?php echo htmlspecialchars($remind_username_link); ?>" class="btn btn-link" role="button"><?php echo $this->Get_variable('forgot_password') ?></a><?php } ?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-4 col-sm-8">
				<button type="submit" class="btn_green" name="login"><?php echo $this->Get_variable('btn_login') ?></button>
			</div>
		</div>
		<?php if( $errors_html ) { ?><div class="error"><?php echo $errors_html; ?></div><?php } ?>
	</form>
	<div class="bottom"></div>
</div>
<?php


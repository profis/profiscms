<?php

if (!defined('PC_INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

?>

<?php if (isset($error) && false !== $error): ?>
	<p class="alert alert-error"><?php echo $error; ?></p>
	<p><a href=""><? echo $t['try_again'] ?></a></p>
<?php else: ?>
	<p><?php echo $msg; ?></p><br/>
	<p  class="alert alert-success"><strong><? echo $t['install_success'] ?></strong></p>
	<ul>
		<li><a target ="_blank" href="../admin/"><? echo $t['go_to_admin'] ?></a></li>
		<li><a target ="_blank" href="../"><? echo $t['go_to_frontend'] ?></a></li>
	</ul>

<?php endif; ?>
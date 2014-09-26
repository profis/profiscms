<?php

if (!defined('PC_INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

?>

<?php if (isset($error) && false !== $error): ?>
	<p class="alert alert-error"><?php echo $error; ?></p>
	<p><a href=""><?php echo $t['try_again'] ?></a></p>
<?php else: ?>
	<p><?php echo isset($msg) ? $msg : ''; ?></p><br/>
	<p  class="alert alert-success"><strong><?php echo $t['install_success'] ?></strong></p>
	<ul>
		<li><a target ="_blank" href="../admin/"><?php echo $t['go_to_admin'] ?></a></li>
		<li><a target ="_blank" href="../"><?php echo $t['go_to_frontend'] ?></a></li>
	</ul>

<?php endif; ?>
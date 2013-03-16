<?php

if (!defined('PC_INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

?>

<h1>Installation result</h1>
<?php if (isset($error) && false !== $error): ?>
	<p class="alert alert-error"><?php echo $error; ?></p>
	<p><a href="">Click here to try again</a></p>
<?php else: ?>
	<p><?php echo $msg; ?></p><br/>
	<p  class="alert alert-success"><strong>Profis CMS was succesfully installed!</strong></p>
	<ul>
		<li><a target ="_blank" href="../admin/">Go to admin</a></li>
		<li><a target ="_blank" href="../">Go to frontend</a></li>
	</ul>

<?php endif; ?>
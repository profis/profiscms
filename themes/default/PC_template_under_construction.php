<?php
/**
 * @var PC_site $this
 * @var array $p "Under construction" page from the site tree
 */
?>
<!DOCTYPE html>
<html lang="<?php echo $this->ln; ?>" style="min-height: 100%; height: 100%;">
<head>
<?php if(!empty($p['info'])) { ?><style type="text/css"><?php echo html_entity_decode(strip_tags($p['info'])); ?></style><?php } ?>
</head>
<body style="height:100%;padding:0;margin:0;">
	<table class="pc_content" width="100%" height="100%">
		<tr valign="middle">
			<td width="30%">&nbsp;</td>
			<td class="pc_content"><?php echo $p['text']; ?></td>
			<td width="30%">&nbsp;</td>
		</tr>
	</table>
</body>
</html>

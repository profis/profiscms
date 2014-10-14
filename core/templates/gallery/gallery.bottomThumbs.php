<?php
/**
 * @var PC_gallery_widget $this
 * @var array $data
 * @var string $tpl_group
 */
$startIndex = intval($data['startIndex']);
if( $startIndex >= ($cnt = count($data['images'])) )
	$startIndex = $cnt - 1;
if( $startIndex < 0 )
	$startIndex = 0;
?>
<div class="pc_gallery pcgw-bottom-thumbs pcgw-<?php echo $data['style']; ?> clearfix" data-previewmode="<?php echo $data['previewMode']; ?>">
	<div class="pcgw-preview-wrap"><?php // pad_bottom, absolute ?>
		<div class="pcgw-preview"><?php // size, relative ?>
			<div class="pcgw-image-wrap"><?php // overflow ?>
				<div class="pcgw-image" style="background-size: <?php echo $data['previewMode']; ?>; <?php echo isset($data['images'][$startIndex]) ? htmlspecialchars('background-image: url(' . $this->chooseImageFromData($data['images'][$startIndex], 'preview', $data) . ');') : ''; ?>"></div>
			</div>
			<div class="pcgw-left"></div>
			<div class="pcgw-right"></div>
			<div class="pcgw-zoom"></div>
		</div>
	</div>
	<div class="pcgw-controls">
		<div class="pcgw-thumbs-wrap"><?php // size, absolute ?>
			<div class="pcgw-thumbs-wrap2"><?php // overflow, relative ?>
				<div class="pcgw-thumbs"><?php // slides ?>
					<div class="pcgw-thumbs-cont"><?php
						foreach( $data['images'] as $idx => $imgData ) {
							?><div
								class="pcgw-thumb<?php echo ($idx == $startIndex) ? ' active' : ''; ?>"
								data-preview="<?php echo htmlspecialchars($this->chooseImageFromData($imgData, 'preview', $data)); ?>"
								data-original="<?php echo htmlspecialchars($imgData['']); ?>"
							><a
								href="<?php echo htmlspecialchars($imgData['']); ?>"
								target="_blank"
								rel="lightbox[pc_gallery_<?php echo intval($data['index']); ?>]"
								title=""
								style="
									background-size: <?php echo $data['thumbnailMode']; ?>;
									background-image: url(<?php echo htmlspecialchars($this->chooseImageFromData($imgData, 'thumbnail', $data)); ?>);
								"
							><img
								src="<?php echo htmlspecialchars($this->chooseImageFromData($imgData, 'thumbnail', $data)); ?>"
								alt=""
								style="display:none;"
							/></a></div><?php
						}
					?></div>
					<div class="pcgw-highlighter-wrap"><?php // absolute ?>
						<div class="pcgw-highlighter"><?php // relative ?><?php echo $data['highlighterMarkup']; ?></div>
					</div>
				</div>
			</div>
		</div>
		<div class="pcgw-left"></div>
		<div class="pcgw-right"></div>
	</div>
</div>
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
<div class="pc_gallery pc_gallery_bottom_thumbs pc_gallery_<?php echo $data['style']; ?> clearfix" data-previewmode="<?php echo $data['previewMode']; ?>">
	<div class="pc_gallery_preview_wrap"><?php // pad_bottom, absolute ?>
		<div class="pc_gallery_preview"><?php // size, relative ?>
			<div class="pc_gallery_image_wrap"><?php // overflow ?>
				<div class="pc_gallery_image" style="background-size: <?php echo $data['previewMode']; ?>; <?php echo isset($data['images'][$startIndex]) ? htmlspecialchars('background-image: url(' . $this->chooseImageFromData($data['images'][$startIndex], 'preview', $data) . ');') : ''; ?>"></div>
			</div>
			<div class="pc_gallery_preview_left"></div>
			<div class="pc_gallery_preview_right"></div>
			<div class="pc_gallery_preview_zoom"></div>
		</div>
	</div>
	<div class="pc_gallery_controls">
		<div class="pc_gallery_thumbs_wrap"><?php // size, absolute ?>
			<div class="pc_gallery_thumbs_wrap2"><?php // overflow, relative ?>
				<div class="pc_gallery_thumbs"><?php // slides ?>
					<div class="pc_gallery_thumbs_cont"><?php
						foreach( $data['images'] as $idx => $imgData ) {
							?><div
								class="pc_gallery_thumb<?php echo ($idx == $startIndex) ? ' active' : ''; ?>"
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
					<div class="pc_gallery_thumb_highlighter_wrap"><?php // absolute ?>
						<div class="pc_gallery_thumb_highlighter"><?php // relative ?><?php echo $data['highlighterMarkup']; ?></div>
					</div>
				</div>
			</div>
		</div>
		<div class="pc_gallery_thumbs_left"></div>
		<div class="pc_gallery_thumbs_right"></div>
	</div>
</div>
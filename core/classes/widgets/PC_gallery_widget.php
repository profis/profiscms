<?php

class PC_gallery_widget extends PC_widget {
	static $galleryIndex = 0;

	public function Init($config = array()) {
		parent::Init($config);
		$this->_template_group = 'gallery';
		$this->_template = 'gallery';
	}

	protected function _get_default_config() {
		return array(
			// Change previewType to whatever thumbnail type you need. Set to empty string ('') to use original image when
			// drawing in preview area, however it is not recommended since image may be 5MB+ and loading it to show
			// preview to the user is way too much. Defaults to 'large'.
			'previewType' => 'large',

			// Change thumbnailType to one available in gallery and that matches the size you desire.
			// Defaults to 'small'.
			'thumbnailType' => 'small',

			// Which css3 background-size method will be applied to preview area. May be either 'contain' or 'cover'.
			// Defaults to 'contain'.
			'previewMode' => 'contain',

			// Which css3 background-size method will be applied to thumbnails. May be either 'contain' or 'cover'.
			// Defaults to 'cover'.
			'thumbnailMode' => 'cover',

			// Items may be:
			// - null, boolean or a number to indicate that 'extractFrom' option must be used (default).
			// - a string containing html code from which images will be extracted via PC_gallery::Extract_files_from_text()
			// - an array of image IDs (numeric values), URLs relative to base URL or absolute URLs to external images. May be mixed.
			'items' => null,

			// This is the name of the field of currently loaded page that will be used to extract images from.
			// Extracted images are not removed from the text - you have to do that manually.
			// May be 'text', 'info', 'info2', 'info3'. Defaults to 'text'.
			// This parameter is ignored when items is not NULL, boolean nor numeric.
			'extractFrom' => 'text',
			
			// Select which widget template to use when rendering. For example if you use view 'bottomThumbs',
			// then 'gallery.bottomThumbs.php' template will be used. Defaults to 'bottomThumbs'.
			'view' => 'bottomThumbs',

			// Select which stylesheet should be used for gallery. May be either 'light' or 'dark'. Defaults to 'dark'.
			'style' => 'dark',

			// Index of image to initially select in the gallery.
			'startIndex' => 0,
			
			// Any HTML that must appear inside thumbnail highlighter div. Defaults to ''.
			'highlighterMarkup' => '',
		);
	}
	
	public function get_data() {
		if( !trim(v($this->_config['thumbnailType'], '')) )
			throw new Exception('Cannot use original images as thumbnails');

		$this->_config['style'] = strtolower(v($this->_config['style'], 'dark'));
		if( $this->_config['style'] != 'light' && $this->_config['style'] != 'dark' )
			$this->_config['style'] = 'dark';

		$this->_config['previewMode'] = strtolower(v($this->_config['previewMode'], 'contain'));
		if( $this->_config['previewMode'] != 'cover' && $this->_config['previewMode'] != 'contain' )
			$this->_config['previewMode'] = 'contain';

		$this->_config['thumbnailMode'] = strtolower(v($this->_config['thumbnailMode'], 'cover'));
		if( $this->_config['thumbnailMode'] != 'cover' && $this->_config['thumbnailMode'] != 'contain' )
			$this->_config['thumbnailMode'] = 'cover';

		if( v($this->_config['previewType']) == '' ) {
			$previewType = null;
		}
		else {
			$previewType = $this->gallery->Get_thumbnail_type(v($this->_config['previewType'], 'large'));
			if( !$previewType ) {
				$this->_config['thumbnailType'] = 'large';
				$previewType = $this->gallery->Get_thumbnail_type($this->_config['previewType']);
			}
		}

		$thumbnailType = $this->gallery->Get_thumbnail_type($this->_config['thumbnailType']);
		if( !$thumbnailType ) {
			$this->_config['thumbnailType'] = 'small';
			$thumbnailType = $this->gallery->Get_thumbnail_type($this->_config['thumbnailType']);
		}

		$thumbs = array(
			'thumb-' . $thumbnailType['thumbnail_type'],
			$previewType ? ('thumb-' . $previewType['thumbnail_type']) : '',
			''
		);

		$items = $this->_config['items'];
		$images = array();

		if( is_array($items) ) {
			foreach( $items as $item ) {
				$imgData = array();
				if( preg_match('#^' . preg_quote($this->cfg['directories']['gallery']) . '/(.*)#', $item, $mtc) ) {
					$img = $this->gallery->Get_file_id_by_url($mtc[1]);
					if( $img['success'] )
						$item = $img['id'];
				}

				if( is_numeric($item) ) {
					$imgData = array();
					foreach( $thumbs as $thumb )
						if( !isset($imgData[$thumb]) ) {
							$img = $this->gallery->Get_file_by_id($item);
							if( $img && $img['success'] )
								$imgData[$thumb] = $this->cfg['directories']['gallery'] . '/' . $img['filedata']['path'] . ($thumb ? ($thumb . '/') : '') . $img['filedata']['filename'];
						}
				}
				else
					$imgData[''] = $item;

				if( !empty($imgData) )
					$images[] = $imgData;
			}
		}
		else {
			if( $items === null || is_bool($items) || is_numeric($items) ) {
				$extractFrom = v($this->_config['extractFrom'], 'text');
				if( !in_array($extractFrom, array('text', 'info', 'info2', 'info3')) )
					$extractFrom = 'text';
				$text = $this->site->loaded_page[$extractFrom];
			}
			else
				$text = "{$items}"; // force conversion to string
			$images = $this->gallery->Extract_files_from_text($text, $thumbs);
		}

		return array_merge($this->_config, array(
			'index' => self::$galleryIndex++,
			'thumbnailType' => $thumbs[0],
			'thumbnailTypeInfo' => $thumbnailType,
			'previewType' => $thumbs[1],
			'previewTypeInfo' => $previewType,
			'images' => $images,
		));
	}
	
	/**
	 * Tries to finds most suitable image URL from given list of available image URLs.
	 *
	 * @param array $imgData Associative array of image URLs where key is thumbnail type and value is URL.
	 * @param string $imgType Must be either 'preview' or 'thumbnail'.
	 * @param array $data An associative data array that is passed to the template (usually $data variable).
	 * @returns string A most suitable URL that was found or NULL otherwise.
	 */
	public function chooseImageFromData(&$imgData, $imgType, &$data) {
		if( $imgType != 'preview' && $imgType != 'thumbnail' )
			return null;
		if( isset($imgData[$k = $data[$imgType . 'Type']]) )
			return $imgData[$k];
		if( $imgType != 'preview' && isset($imgData[$k = $data['previewType']]) )
			return $imgData[$k];
		return isset($imgData['']) ? $imgData[''] : (empty($imgData) ? null : reset($imgData));
	}
}
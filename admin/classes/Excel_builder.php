<?php

class Excel_builder {

	/**
	 *
	 * @var string
	 */
	public $file_name;
	
	/**
	 *
	 * @var string
	 */
	public $lib_dir;
	
	/**
	 *
	 * @var XLSXStreamWriter
	 */
	protected $_wb;
	
	/**
	 *
	 * @var string
	 */
	protected $_output_file;
	
	function __construct($file_name, $lib_dir = '') {
		$this->file_name = $file_name;
		$this->lib_dir = $lib_dir;
		
		require_once $this->lib_dir . 'XLSXStreamWriter.php';
		$this->_wb = new XLSXStreamWriter();
	}

	
	
	
	public function make($sheet_name, &$labels, &$rows) {
		// @error_reporting(E_ALL);
		// ini_set("display_errors", true);
	
		// @header("Content-Type: application/vnd.ms-excel");
		$this->_output_file = $this->lib_dir . "tmp/" . uniqid("", true) . ".xlsx";

		$sheet = $this->_wb->addSheet($sheet_name);

		$this->_write_labels($sheet, $labels);
		$this->_write_rows($sheet, $rows);
		
		$this->_wb->close($this->_output_file);
		
		return $this->_output_file;
	}
	
	protected function _write_labels($sheet, &$labels) {
		// fontName, fontSize, color, bold, italic, underline, strike
		$boldFont = $this->_wb->addFont("Calibri", 11, "000000", true, false, false, false);
		// fillType, color1, color2 (only for grads)
		$headerFill = $this->_wb->addFill("solid", "FFEEDD");
		// leftType, rightType, topType, bottomType, leftColor, rightColor, topColor, bottomColor
		$allBorders = $this->_wb->addBorders("thin", "thin", "thin", "thin", "DADCDD", "DADCDD", "DADCDD", "DADCDD");
		// fontId, fillId, bordersId, hAlign, vAlign, wrap, shrink, rotate
		$headerStyle = $this->_wb->addStyle($boldFont, $headerFill, $allBorders, "center", "center", true, false, 0);
		
		$idx = 0;
		foreach($labels as $key => $value ) {
			$this->_wb->setCellValue($sheet, $idx, 0, $value);
			$this->_wb->setCellStyle($sheet, $idx, 0, $headerStyle);
			$idx++;
		}
	}
	
	protected function _write_rows($sheet, &$rows, $idy = 1) {
		for ($index = 0; $index < count($rows); $index++) {
			$idx = 0;
			$headerStyle = false;
			if (isset($rows[$index]['_meta'])) {
				if (v($rows[$index]['_meta']['background_color'])) {
					$headerFill = $this->_wb->addFill("solid", $rows[$index]['_meta']['background_color']);
					$headerStyle = $this->_wb->addStyle(null, $headerFill, null);
				}
				unset($rows[$index]['_meta']);
			}
			foreach($rows[$index] as $key => $value ) {
				$this->_wb->setCellValue($sheet, $idx, $idy, $value);
				if ($headerStyle) {
					$this->_wb->setCellStyle($sheet, $idx, $idy, $headerStyle);
				}
				$idx++;
			}
			$idy++;
		}
	}
	
	public function output() {
		//ob_end_clean(); // stop the buffering

		$file_name = $this->file_name .  "_" . date("Y-m-d_H-i-s") . ".xlsx";
		
		//@header("Content-Type: application/force-download");
		@header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		@header("Content-Disposition: attachment; filename=" . $file_name);
		readfile($this->_output_file);
		@unlink($this->_output_file);

		//ob_start();
	}
	
}

?>

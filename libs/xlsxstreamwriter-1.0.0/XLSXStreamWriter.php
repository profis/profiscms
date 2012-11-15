<?php
	/**
	* XLSXStreamWriter class for writing very big amounts of data to .xlsx file
	* in a streaming way
	*
	* Only row values and cell merging data are streamed. All other information
	* is kept in the memory until file is getting generated.
	*
	* @version 1.0.0
	* @category Libraries
	* @package BalticDEV
	* @subpackage XLSXStreamWriter
	* @author Viacheslav Soroka
	* @since 2012-08-17
	* @copyright 2012 Viacheslav Soroka
	* @license http://www.gnu.org/licenses/lgpl.html
	*/
	
	require_once "inc.zip2.php";
	
	class XLSXStreamWriter {
		const FF_NONE = 0;
		const FF_BOLD = 1;
		const FF_ITALIC = 2;
		const FF_UNDERLINE = 4;
		const FF_STRIKE = 8;
	
		const XML_HEADER = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
		private $tempDir;
		private $zipFile;
		
		protected $sheets;
		protected $activeSheet;
		protected $sharedStrings;
		protected $fonts;
		protected $fills;
		protected $borders;
		protected $cellXfs;
		
		public $company;
		public $manager;
		public $creator;
		public $title;
		public $description;
		public $subject;
		public $keywords;
		public $category;
		
		public function __construct() {
			$this->zipFile = null;
		
			$temp = dirname(__FILE__) . "/tmp";
			if( !is_dir($temp) ) {
				@mkdir($temp, 0777);
				if( !is_dir($temp) )
					throw new Exception("Cannot create a temporary directory at " . $temp);
			}
			while( is_dir($this->tempDir = $temp . "/" . uniqid("", true)) );
			
			@mkdir($this->tempDir, 0777);
			if( !is_dir($this->tempDir) ) {
				$this->tempDir = "";
				throw new Exception("Cannot create a temporary directory at " . $this->tempDir);
			}
			
			$this->company = "Unknown Company";
			$this->manager = "Unknown Manager";
			$this->creator = "Unknown Creator";
			$this->title = "Untitled";
			$this->description = "";
			$this->subject = "";
			$this->keywords = "";
			$this->category = "";
			
			$this->sheets = Array();
			$this->activeSheet = 0;
			$this->sharedStrings = Array();
			
			$this->fonts = Array(
				Array("Calibri", 11, self::FF_NONE, "000000"), // default calibri 11pt black font
			);
			
			$this->fills = Array(
				Array("none", "FFFFFF", "000000"), // no fill
				Array("gray125", "FFFFFF", "000000"), // no fill
			);
			
			$this->borders = Array(
				Array(), // empty array for "no borders" style
			);
			
			$this->cellXfs = Array(
				Array(null, null, null, null),
			);
		}
		
		public function close($outFileName) {
			if( !$this->tempDir )
				return;

			$this->flush();
			
			$this->zipFile = new ZipFile2();
			if( $this->zipFile->open($outFileName) ) {
				$this->_writeWorkbookData();
				$this->_writeSharedStrings();
				$this->_writeStyles();
				$this->_writeSheets();
			}
			
			$this->__destruct();
		}
		
		public function addSheet($sheetName) {
			$this->sheets[] = Array(
				$sheetName, // name
				1, // column count
				0, // flushed row count
				0,
				0,
				14.4, // default row height
				Array(), // cells
				Array(), // column defs
			);
			return count($this->sheets) - 1;
		}
		
		public function setActiveSheet($index) {
			$index = intval($index);
			if( $index > 0 && $index < count($this->sheets) )
				$this->activeSheet = $index;
		}
		
		public function freezeSheet($index, $col, $row) {
			$col = intval($col);
			$row = intval($row);
			$index = intval($index);
			if( $col < 0 || $row < 0 || $index < 0 || $index >= count($this->sheets) )
				return;
			$this->sheets[$index][3] = $col;
			$this->sheets[$index][4] = $row;
		}
		
		public function setCellValue($sheetIndex, $col, $row, $val) {
			if( $col < 0 || $row < 0 || $sheetIndex < 0 || $sheetIndex >= count($this->sheets) )
				return;
			$sheet = &$this->sheets[$sheetIndex];
			if( $row < $sheet[2] ) return;
			if( !isset($sheet[7][$row]) ) $sheet[7][$row] = Array();
			$c = $this->_cell($val);
			if( !isset($sheet[7][$row][$col]) )
				$sheet[7][$row][$col] = Array($c[0], $c[1], 0);
			else {
				$sheet[7][$row][$col][0] = $c[0];
				$sheet[7][$row][$col][1] = $c[1];
			}
		}
		
		public function setCellStyle($sheetIndex, $col, $row, $style) {
			if( $col < 0 || $row < 0 || $sheetIndex < 0 || $sheetIndex >= count($this->sheets) )
				return;
			$sheet = &$this->sheets[$sheetIndex];
			if( $row < $sheet[2] ) return;
			if( !isset($sheet[7][$row]) || !isset($sheet[7][$row][$col]) )
				$this->setCellValue($sheetIndex, $col, $row, '');
			$sheet[7][$row][$col][2] = $style;
		}
		
		public function setColumnWidth($sheetIndex, $col, $width) {
			if( $col < 0 || $sheetIndex < 0 || $sheetIndex >= count($this->sheets) )
				return;
			$sheet = &$this->sheets[$sheetIndex];
			if( !isset($sheet[6][$col]) ) $sheet[6][$col] = Array(9, 0);
			$sheet[6][$col][0] = $width;
		}
		
		public function setColumnStyle($sheetIndex, $col, $style) {
			if( $col < 0 || $sheetIndex < 0 || $sheetIndex >= count($this->sheets) )
				return;
			$sheet = &$this->sheets[$sheetIndex];
			if( !isset($sheet[6][$col]) ) $sheet[6][$col] = Array(9, 0);
			$sheet[6][$col][0] = $style;
		}
		
		public function addFont($name = "Calibri", $size = "11", $color = "000000", $bold = false, $italic = false, $underline = false, $strike = false) {
			$fnt = Array(
				trim($name),
				intval($size),
				($bold ? self::FF_BOLD : 0) | ($italic ? self::FF_ITALIC : 0) | ($underline ? self::FF_UNDERLINE : 0) | ($strike ? self::FF_STRIKE : 0),
				strtoupper($color)
			);
			
			foreach( $this->fonts as $idx => $font ) {
				if( strtolower($font[0]) == strtolower($fnt[0]) && $font[1] == $fnt[1] && $font[2] == $fnt[2] && $font[3] == $fnt[3] )
					return $idx;
			}
			$idx = count($this->fonts);
			$this->fonts[] = $fnt;
			return $idx;
		}
		
		public function addFill($pattern = "solid", $color1 = "FFFFFF", $color2 = "000000") {
			$fl = Array(
				$pattern,
				strtoupper($color1),
				strtoupper($color2),
			);
			
			foreach( $this->fills as $idx => $fill ) {
				if( strtolower($fill[0]) == strtolower($fl[0]) && $fill[1] == $fl[1] && $fill[2] == $fl[2] )
					return $idx;
			}
			$idx = count($this->fills);
			$this->fills[] = $fl;
			return $idx;
		}
		
		public function addBorders($leftType = null, $rightType = null, $topType = null, $bottomType = null, $leftColor = '000000', $rightColor = '000000', $topColor = '000000', $bottomColor = '000000') {
			$br = Array();
			if( $leftType !== null ) $br[0] = Array($leftType, $leftColor);
			if( $rightType !== null ) $br[1] = Array($rightType, $rightColor);
			if( $topType !== null ) $br[2] = Array($topType, $topColor);
			if( $bottomType !== null ) $br[3] = Array($bottomType, $bottomColor);
			
			foreach( $this->borders as $idx => $brd ) {
				$found = true;
				for( $i = 0; $found && $i < 4; $i++ ) {
					if( !isset($brd[$i]) && !isset($br[$i]) )
						continue;
					if( (isset($brd[$i]) && !isset($br[$i])) || (!isset($brd[$i]) && isset($br[$i])) || ($brd[$i] != $br[$i]) )
						$found = false;
				}
				if( $found )
					return $idx;
			}
			$idx = count($this->borders);
			$this->borders[] = $br;
			return $idx;
		}
		
		public function addStyle($fontIndex=null, $fillIndex=null, $borderIndex=null, $hAlign="general", $vAlign="bottom", $wrap = false, $shrink = false, $rotate = 0) {
			$al = Array(
				$hAlign,
				$vAlign,
				$wrap == true,
				$shrink == true,
				intval($rotate)
			);
			if( $al[0] == "general" && $al[1] = "bottom" && !$al[2] && !$al[3] && !$al[4] )
				$al = null;
			
			$stl = Array(
				intval($fontIndex),
				intval($fillIndex),
				intval($borderIndex),
				$al,
			);
			
			foreach( $this->cellXfs as $idx => $xf ) {
				if( $xf[0] == $stl[0] && $xf[1] == $stl[1] && $xf[2] == $stl[2] ) {
					if( $xf[3] === null && $al === null )
						return $idx;
					if( strtolower($xf[3][0]) == strtolower($al[0]) && strtolower($xf[3][1]) == strtolower($al[1])
						&& $xf[3][2] == $al[2] && $xf[3][3] == $al[3] && $xf[3][4] == $al[4] )
						return $idx;
				}
			}
			$idx = count($this->cellXfs);
			$this->cellXfs[] = $stl;
			return $idx;
			return 0;
		}
		
		public function mergeCells($sheetIndex, $col1, $row1, $col2, $row2) {
			if( $col1 < 0 || $row1 < 0 || $col2 < 0 || $row2 < 0 || $sheetIndex < 0 || $sheetIndex >= count($this->sheets) )
				return;
			$sheet = &$this->sheets[$sheetIndex];
			// if( $row1 < $sheet[2] || $row2 < $sheet[2] ) return;
			$sheet[8][] = self::i2c($col1) . ($row1 + 1) . ":" . self::i2c($col2) . ($row2 + 1);
		}
		
		public function flush() {
			foreach( $this->sheets as $idx => &$sheet ) {
				if( !empty($sheet[7]) && $fp = fopen($this->tempDir . "/" . $idx . ".tmp", "a") ) {				
					ksort($sheet[7]);
					foreach( $sheet[7] as $rowId => &$row ) {
						if( $rowId >= $sheet[2] ) $sheet[2] = $rowId + 1;
						ksort($row);
						reset($row);
						$firstCol = key($row);
						end($row);
						$lastCol = key($row);
						if( $lastCol >= $sheet[1] ) $sheet[1] = $lastCol + 1;
						
						fputs($fp, '<row r="' . ($rowId + 1) . '" spans="' . ($firstCol + 1) . ':' . ($lastCol + 1) . '">');
						foreach( $row as $colId => &$cell ) {
							$cid = self::i2c($colId) . ($rowId + 1);
							$need_cdata = strpos($cell[0], '<') !== false || strpos($cell[0], '&') !== false;
							fputs($fp, '<c r="' . $cid . '"' . ($cell[2] ? (' s="' . $cell[2] . '"') : '') . ($cell[1] ? (' t="' . $cell[1] . '"') : '') . '><v>' . ($need_cdata?'<![CDATA[':'') . $cell[0] . ($need_cdata?']]>':'') . '</v></c>');
						}
						fputs($fp, '</row>');
					}
					$sheet[7] = Array();
					fclose($fp);
				}

				if( !empty($sheet[8]) && $fp = fopen($this->tempDir . "/" . $idx . "m.tmp", "a") ) {
					foreach($sheet[8] as $mc)
						fputs($fp, '<mergeCell ref="' . $mc . '"/>');
					$sheet[8] = Array();
					fclose($fp);
				}				
			}
		}
		
		public function __destruct() {
			if( $this->zipFile !== null )
				$this->zipFile->close();
			$this->zipFile = null;

			if( !$this->tempDir || !is_dir($this->tempDir) )
				return;
			
			$files = @glob($this->tempDir . "/*.tmp");
			foreach($files as $f) @unlink($f);
			@rmdir($this->tempDir);
			$this->tempDir = "";
		}
		
		protected function _writeWorkbookData() {
			// <![CDATA[' . $this->creator . ']]>
		
			// create "/[Content_Types].xml"
		
			$sheets_xml_ct = $sheets_xml_app = $sheets_xml_wb = $sheets_xml_wbrel = "";
			foreach( $this->sheets as $idx => $sheet ) {
				$sheets_xml_ct .= '<Override PartName="/xl/worksheets/sheet' . ($idx + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
				$sheets_xml_app .= '<vt:lpstr><![CDATA[' . $sheet[0] . ']]></vt:lpstr>';
				$sheets_xml_wb .= '<sheet name="' . htmlspecialchars($sheet[0]) . '" sheetId="' . ($idx + 1) . '" r:id="rId' . ($idx + 4) . '"/>';
				$sheets_xml_wbrel .= '<Relationship Id="rId' . ($idx + 4) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($idx + 1) . '.xml"/>';
			}
			$xml = self::XML_HEADER . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/xl/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="vml" ContentType="application/vnd.openxmlformats-officedocument.vmlDrawing"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>' . $sheets_xml_ct . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>';
			$this->zipFile->addFile($xml, '[Content_Types].xml');
			
			// create "/_rels/.rels"
			
			$xml = self::XML_HEADER . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
			$this->zipFile->addFile($xml, '_rels/.rels');
			
			// create "/docProps/app.xml"

			$xml = self::XML_HEADER . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Microsoft Excel</Application><DocSecurity>0</DocSecurity><ScaleCrop>false</ScaleCrop><HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>' . count($this->sheets) . '</vt:i4></vt:variant></vt:vector></HeadingPairs><TitlesOfParts><vt:vector size="' . count($this->sheets) . '" baseType="lpstr">' . $sheets_xml_app . '</vt:vector></TitlesOfParts><Company><![CDATA[' . $this->company . ']]></Company><Manager><![CDATA[' . $this->manager . ']]></Manager><LinksUpToDate>false</LinksUpToDate><SharedDoc>false</SharedDoc><HyperlinksChanged>false</HyperlinksChanged><AppVersion>12.0000</AppVersion></Properties>';
			$this->zipFile->addFile($xml, 'docProps/app.xml');

			// create "/docProps/core.xml"
			
			$xml = self::XML_HEADER . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:creator><![CDATA[' . $this->creator . ']]></dc:creator><cp:lastModifiedBy><![CDATA[' . $this->creator . ']]></cp:lastModifiedBy><dcterms:created xsi:type="dcterms:W3CDTF">' . date('c') . '</dcterms:created><dcterms:modified xsi:type="dcterms:W3CDTF">' . date('c') . '</dcterms:modified><dc:title><![CDATA[' . $this->title . ']]></dc:title><dc:description><![CDATA[' . $this->description . ']]></dc:description><dc:subject><![CDATA[' . $this->subject . ']]></dc:subject><cp:keywords><![CDATA[' . $this->keywords . ']]></cp:keywords><cp:category><![CDATA[' . $this->category . ']]></cp:category></cp:coreProperties>';
			$this->zipFile->addFile($xml, 'docProps/core.xml');
			
			// create "/xl/workbook.xml"
			
			$xml = self::XML_HEADER . '<workbook xml:space="preserve" xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><fileVersion appName="xl" lastEdited="4" lowestEdited="4" rupBuild="4505"/><workbookPr codeName="ThisWorkbook"/><bookViews><workbookView activeTab="' . $this->activeSheet . '" autoFilterDateGrouping="1" firstSheet="0" minimized="0" showHorizontalScroll="1" showSheetTabs="1" showVerticalScroll="1" tabRatio="600" visibility="visible"/></bookViews><sheets>' . $sheets_xml_wb . '</sheets><definedNames/><calcPr calcId="124519" calcMode="auto" fullCalcOnLoad="0"/></workbook>';
			$this->zipFile->addFile($xml, 'xl/workbook.xml');
			
			// create "/xl/_rels/workbook.xml.rels"
			
			$xml = self::XML_HEADER . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>' . $sheets_xml_wbrel . '</Relationships>';
			$this->zipFile->addFile($xml, 'xl/_rels/workbook.xml.rels');
			
			// create "/xl/theme/theme1.xml"
			
			$xml = self::XML_HEADER . '<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="Office Theme"><a:themeElements><a:clrScheme name="Office"><a:dk1><a:sysClr val="windowText" lastClr="000000"/></a:dk1><a:lt1><a:sysClr val="window" lastClr="FFFFFF"/></a:lt1><a:dk2><a:srgbClr val="1F497D"/></a:dk2><a:lt2><a:srgbClr val="EEECE1"/></a:lt2><a:accent1><a:srgbClr val="4F81BD"/></a:accent1><a:accent2><a:srgbClr val="C0504D"/></a:accent2><a:accent3><a:srgbClr val="9BBB59"/></a:accent3><a:accent4><a:srgbClr val="8064A2"/></a:accent4><a:accent5><a:srgbClr val="4BACC6"/></a:accent5><a:accent6><a:srgbClr val="F79646"/></a:accent6><a:hlink><a:srgbClr val="0000FF"/></a:hlink><a:folHlink><a:srgbClr val="800080"/></a:folHlink></a:clrScheme><a:fontScheme name="Office"><a:majorFont><a:latin typeface="Cambria"/><a:ea typeface=""/><a:cs typeface=""/><a:font script="Jpan" typeface="ＭＳ Ｐゴシック"/><a:font script="Hang" typeface="맑은 고딕"/><a:font script="Hans" typeface="宋体"/><a:font script="Hant" typeface="新細明體"/><a:font script="Arab" typeface="Times New Roman"/><a:font script="Hebr" typeface="Times New Roman"/><a:font script="Thai" typeface="Tahoma"/><a:font script="Ethi" typeface="Nyala"/><a:font script="Beng" typeface="Vrinda"/><a:font script="Gujr" typeface="Shruti"/><a:font script="Khmr" typeface="MoolBoran"/><a:font script="Knda" typeface="Tunga"/><a:font script="Guru" typeface="Raavi"/><a:font script="Cans" typeface="Euphemia"/><a:font script="Cher" typeface="Plantagenet Cherokee"/><a:font script="Yiii" typeface="Microsoft Yi Baiti"/><a:font script="Tibt" typeface="Microsoft Himalaya"/><a:font script="Thaa" typeface="MV Boli"/><a:font script="Deva" typeface="Mangal"/><a:font script="Telu" typeface="Gautami"/><a:font script="Taml" typeface="Latha"/><a:font script="Syrc" typeface="Estrangelo Edessa"/><a:font script="Orya" typeface="Kalinga"/><a:font script="Mlym" typeface="Kartika"/><a:font script="Laoo" typeface="DokChampa"/><a:font script="Sinh" typeface="Iskoola Pota"/><a:font script="Mong" typeface="Mongolian Baiti"/><a:font script="Viet" typeface="Times New Roman"/><a:font script="Uigh" typeface="Microsoft Uighur"/><a:font script="Geor" typeface="Sylfaen"/></a:majorFont><a:minorFont><a:latin typeface="Calibri"/><a:ea typeface=""/><a:cs typeface=""/><a:font script="Jpan" typeface="ＭＳ Ｐゴシック"/><a:font script="Hang" typeface="맑은 고딕"/><a:font script="Hans" typeface="宋体"/><a:font script="Hant" typeface="新細明體"/><a:font script="Arab" typeface="Arial"/><a:font script="Hebr" typeface="Arial"/><a:font script="Thai" typeface="Tahoma"/><a:font script="Ethi" typeface="Nyala"/><a:font script="Beng" typeface="Vrinda"/><a:font script="Gujr" typeface="Shruti"/><a:font script="Khmr" typeface="DaunPenh"/><a:font script="Knda" typeface="Tunga"/><a:font script="Guru" typeface="Raavi"/><a:font script="Cans" typeface="Euphemia"/><a:font script="Cher" typeface="Plantagenet Cherokee"/><a:font script="Yiii" typeface="Microsoft Yi Baiti"/><a:font script="Tibt" typeface="Microsoft Himalaya"/><a:font script="Thaa" typeface="MV Boli"/><a:font script="Deva" typeface="Mangal"/><a:font script="Telu" typeface="Gautami"/><a:font script="Taml" typeface="Latha"/><a:font script="Syrc" typeface="Estrangelo Edessa"/><a:font script="Orya" typeface="Kalinga"/><a:font script="Mlym" typeface="Kartika"/><a:font script="Laoo" typeface="DokChampa"/><a:font script="Sinh" typeface="Iskoola Pota"/><a:font script="Mong" typeface="Mongolian Baiti"/><a:font script="Viet" typeface="Arial"/><a:font script="Uigh" typeface="Microsoft Uighur"/><a:font script="Geor" typeface="Sylfaen"/></a:minorFont></a:fontScheme><a:fmtScheme name="Office"><a:fillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:tint val="50000"/><a:satMod val="300000"/></a:schemeClr></a:gs><a:gs pos="35000"><a:schemeClr val="phClr"><a:tint val="37000"/><a:satMod val="300000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:tint val="15000"/><a:satMod val="350000"/></a:schemeClr></a:gs></a:gsLst><a:lin ang="16200000" scaled="1"/></a:gradFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:shade val="51000"/><a:satMod val="130000"/></a:schemeClr></a:gs><a:gs pos="80000"><a:schemeClr val="phClr"><a:shade val="93000"/><a:satMod val="130000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:shade val="94000"/><a:satMod val="135000"/></a:schemeClr></a:gs></a:gsLst><a:lin ang="16200000" scaled="0"/></a:gradFill></a:fillStyleLst><a:lnStyleLst><a:ln w="9525" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"><a:shade val="95000"/><a:satMod val="105000"/></a:schemeClr></a:solidFill><a:prstDash val="solid"/></a:ln><a:ln w="25400" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:prstDash val="solid"/></a:ln><a:ln w="38100" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:prstDash val="solid"/></a:ln></a:lnStyleLst><a:effectStyleLst><a:effectStyle><a:effectLst><a:outerShdw blurRad="40000" dist="20000" dir="5400000" rotWithShape="0"><a:srgbClr val="000000"><a:alpha val="38000"/></a:srgbClr></a:outerShdw></a:effectLst></a:effectStyle><a:effectStyle><a:effectLst><a:outerShdw blurRad="40000" dist="23000" dir="5400000" rotWithShape="0"><a:srgbClr val="000000"><a:alpha val="35000"/></a:srgbClr></a:outerShdw></a:effectLst></a:effectStyle><a:effectStyle><a:effectLst><a:outerShdw blurRad="40000" dist="23000" dir="5400000" rotWithShape="0"><a:srgbClr val="000000"><a:alpha val="35000"/></a:srgbClr></a:outerShdw></a:effectLst><a:scene3d><a:camera prst="orthographicFront"><a:rot lat="0" lon="0" rev="0"/></a:camera><a:lightRig rig="threePt" dir="t"><a:rot lat="0" lon="0" rev="1200000"/></a:lightRig></a:scene3d><a:sp3d><a:bevelT w="63500" h="25400"/></a:sp3d></a:effectStyle></a:effectStyleLst><a:bgFillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:tint val="40000"/><a:satMod val="350000"/></a:schemeClr></a:gs><a:gs pos="40000"><a:schemeClr val="phClr"><a:tint val="45000"/><a:shade val="99000"/><a:satMod val="350000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:shade val="20000"/><a:satMod val="255000"/></a:schemeClr></a:gs></a:gsLst><a:path path="circle"><a:fillToRect l="50000" t="-80000" r="50000" b="180000"/></a:path></a:gradFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:tint val="80000"/><a:satMod val="300000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:shade val="30000"/><a:satMod val="200000"/></a:schemeClr></a:gs></a:gsLst><a:path path="circle"><a:fillToRect l="50000" t="50000" r="50000" b="50000"/></a:path></a:gradFill></a:bgFillStyleLst></a:fmtScheme></a:themeElements><a:objectDefaults/><a:extraClrSchemeLst/></a:theme>';
			$this->zipFile->addFile($xml, 'xl/theme/theme1.xml');
		}
		
		private function _cell(&$str) {
			if( is_numeric($str) ) return Array($str, '');
			if( isset($this->sharedStrings[$str]) )
				return Array($this->sharedStrings[$str], 's');
			$idx = $this->sharedStrings[$str] = count($this->sharedStrings);
			return Array($idx, 's');
		}
		
		protected function _writeSharedStrings() {
			// each shared string is a node in sst: "<si><t>Name</t></si>";
		
			// create "/xl/sharedStrings.xml"
			
			$xml = self::XML_HEADER;
			//if( is_file($f = $this->tempDir . "/s.tmp") ) {
			if( !empty($this->sharedStrings) ) {
				$xml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" uniqueCount="' . count($this->sharedStrings) . '">';
				foreach( $this->sharedStrings as $str => $idx )
					$xml .= '<si><t><![CDATA[' . $str . ']]></t></si>';
				//$xml .= file_get_contents($f);
				$xml .= '</sst>';
			}
			else
				$xml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" uniqueCount="0" />';
			$this->zipFile->addFile($xml, 'xl/sharedStrings.xml');
			$this->sharedStrings = Array();
		}
		
		protected function _writeStyles() {
			// each shared string is a node in sst: "<si><t>Name</t></si>";
		
			// create "/xl/styles.xml"
			
			$xml = self::XML_HEADER . '<styleSheet xml:space="preserve" xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
			
			// append number formats
			$xml .= '<numFmts count="0"/>';
			
			// append fonts
			$xml .= '<fonts count="' . count($this->fonts) . '">';
			foreach( $this->fonts as $font )
				$xml .= '<font><name val="' . htmlspecialchars($font[0]) . '"/><sz val="' . intval($font[1]) . '"/><b val="' . (($font[2] & self::FF_BOLD) ? 1 : 0) . '"/><i val="' . (($font[2] & self::FF_ITALIC) ? 1 : 0) . '"/><u val="' . (($font[2] & self::FF_UNDERLINE) ? 'single' : 'none') . '"/><strike val="0"/><color rgb="FF' . htmlspecialchars($font[3]) . '"/></font>';
			$xml .= '</fonts>';

			// append fills
			$xml .= '<fills count="' . count($this->fills) . '">';
			foreach( $this->fills as $fill ) {
				if( $fill[0] == "none" )
					$xml .= '<fill><patternFill patternType="none"/></fill>';
				else
					$xml .= '<fill><patternFill patternType="' . htmlspecialchars($fill[0]) . '"><fgColor rgb="FF' . htmlspecialchars($fill[1]) . '"/><bgColor rgb="FF' . htmlspecialchars($fill[2]) . '"/></patternFill></fill>';
			}
			$xml .= '</fills>';
			
			// append borders
			$borderNames = preg_split('# #', 'left right top bottom');
			$xml .= '<borders count="' . count($this->borders) . '">';
			foreach( $this->borders as $border ) {
				if( empty($border) || !is_array($border) )
					$xml .= '<border/>';
				else {	
					$xml .= '<border>';
					foreach( $border as $k => $v ) {
						if( !isset($borderNames[$k]) )
							continue;
						$bn = $borderNames[$k];
						$xml .= '<' . $bn . ' style="' . htmlspecialchars($v[0]) . '"><color rgb="FF' . htmlspecialchars($v[1]) . '"/></' . $bn . '>';
					}
					$xml .= '</border>';
				}
			}
			$xml .= '</borders>';
			
			// append cell style Xfs
			$xml .= '<cellStyleXfs count="1">';
			$xml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>';
			$xml .= '</cellStyleXfs>';
			
			// append cell Xfs
			$xml .= '<cellXfs count="' . count($this->cellXfs) . '">';
			foreach( $this->cellXfs as $xf ) {
				$xml .= '<xf xfId="0" numFmtId="0" applynumberFormat="0"';
				$xml .= ' fontId="' . intval(isset($xf[0]) ? $xf[0] : 0) . '" applyFont="' . (isset($xf[0]) ? 1 : 0) . '"';
				$xml .= ' fillId="' . intval(isset($xf[1]) ? $xf[1] : 0) . '" applyFill="' . (isset($xf[1]) ? 1 : 0) . '"';
				$xml .= ' borderId="' . intval(isset($xf[2]) ? $xf[2] : 0) . '" applyBorder="' . (isset($xf[2]) ? 1 : 0) . '"';
				$xml .= ' applyAlignment="' . ((isset($xf[3]) && is_array($xf[3])) ? 1 : 0) . '"';
				$xml .= '><alignment';
				$xml .= ' horizontal="' . htmlspecialchars(isset($xf[3]) ? $xf[3][0] : 'general') . '"';
				$xml .= ' vertical="' . htmlspecialchars(isset($xf[3]) ? $xf[3][1] : 'bottom') . '"';
				$xml .= ' wrapText="' . ((isset($xf[3][2]) && $xf[3][2]) ? 'true' : 'false') . '"';
				$xml .= ' shrinkToFit="' . ((isset($xf[3][3]) && $xf[3][3]) ? 'true' : 'false') . '"';
				$xml .= ' textRotation="' . intval(isset($xf[3]) ? $xf[3][4] : 0) . '"';
				$xml .= '/></xf>';
			}
			$xml .= '</cellXfs>';

			// append cell styles
			$xml .= '<cellStyles count="1">';
			$xml .= '<cellStyle name="Normal" xfId="0" builtinId="0"/>';
			$xml .= '</cellStyles>';
			
			// append dxfs
			$xml .= '<dxfs count="0"/>';
			
			// append table styles
			$xml .= '<tableStyles defaultTableStyle="TableStyleMedium9" defaultPivotStyle="PivotTableStyle1"/>';
			
			$xml .= '</styleSheet>';
			
			$this->zipFile->addFile($xml, 'xl/styles.xml');
		}
	
		protected function _writeSheets() {
			foreach( $this->sheets as $idx => &$sheet ) {
				// create "/xl/worksheets/_rels/sheet#.xml.rels"
			
				$xml = self::XML_HEADER . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>';
				$this->zipFile->addFile($xml, 'xl/worksheets/_rels/sheet' . ($idx + 1) . '.xml.rels');

				// create "/xl/worksheets/sheet1.xml"
				$xml = self::XML_HEADER . '<worksheet xml:space="preserve" xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
				
				// append sheet "Pr" info ... whatever it is
				$xml .= '<sheetPr>';
				$xml .= '<outlinePr summaryBelow="1" summaryRight="1"/>';
				$xml .= '</sheetPr>';
				
				// append sheet dimensions
				if( !$sheet[2] ) // no rows were added
					$xml .= '<dimension ref="A1:A1"/>';
				else
					$xml .= '<dimension ref="A1:' . self::i2c($sheet[1] - 1) . $sheet[2] . '"/>';
				
				// append sheet views
				$xml .= '<sheetViews>';
				$xml .= '<sheetView tabSelected="0" workbookViewId="0" showGridLines="true" showRowColHeaders="1">';
				if( $sheet[3] && $sheet[4] ) {
					$spcell = self::i2c($sheet[3]) . ($sheet[4] + 1);
					$xml .= '<pane xSplit="' . $sheet[3] . '" ySplit="' . $sheet[4] . '" topLeftCell="' . $spcell . '" activePane="bottomRight" state="frozen"/>';
					$xml .= '<selection pane="topRight"/>';
					$xml .= '<selection pane="bottomLeft"/>';
					$xml .= '<selection pane="bottomRight" activeCell="' . $spcell . '" sqref="' . $spcell . '"/>';
				}
				else if( $sheet[3] ) {
					$spcell = self::i2c($sheet[3]) . "1";
					$xml .= '<pane xSplit="' . $sheet[3] . '" ySplit="0" topLeftCell="' . $spcell . '" activePane="right" state="frozen"/>';
					$xml .= '<selection pane="right" activeCell="' . $spcell . '" sqref="' . $spcell . '"/>';
				}
				else if( $sheet[4] ) {
					$spcell = "A" . ($sheet[4] + 1);
					$xml .= '<pane xSplit="0" ySplit="' . $sheet[4] . '" topLeftCell="' . $spcell . '" activePane="bottom" state="frozen"/>';
					$xml .= '<selection pane="bottom" activeCell="' . $spcell . '" sqref="' . $spcell . '"/>';
				}
				else
					$spcell = "A1";
				$xml .= '</sheetView>';
				$xml .= '</sheetViews>';
				
				// append sheet formatting
				$xml .= '<sheetFormatPr defaultRowHeight="' . $sheet[5] . '" outlineLevelRow="0" outlineLevelCol="0"/>';
				
				// append column formatting
				$xml .= '<cols>';
				for( $i = 0; $i < $sheet[1]; $i++ ) {
					if( isset($sheet[6][$i]) )
						$xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $sheet[6][$i][0] . '" customWidth="true" style="' . $sheet[6][$i][1] . '"/>';
					else
						$xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="9" customWidth="true" style="0"/>';
				}
				$xml .= '</cols>';
				
				// append rows
				$xml .= '<sheetData>';
				if( is_file($f = $this->tempDir . "/" . $idx . ".tmp") )
					$xml .= file_get_contents($f);
				$xml .= '</sheetData>';

				// append protection information
				$xml .= '<sheetProtection sheet="false" objects="false" scenarios="false" formatCells="false" formatColumns="false" formatRows="false" insertColumns="false" insertRows="false" insertHyperlinks="false" deleteColumns="false" deleteRows="false" selectLockedCells="false" sort="false" autoFilter="false" pivotTables="false" selectUnlockedCells="false"/>';

				// append merged cells info
				// merge cell XML entry: <mergeCell ref="A1:A2"/>
				if( is_file($f = $this->tempDir . "/" . $idx . "m.tmp") ) {
					$xml .= '<mergeCells>';
					$xml .= file_get_contents($f);
					$xml .= '</mergeCells>';
				}
				
				// append print options
				$xml .= '<printOptions gridLines="false" gridLinesSet="true"/><pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/><pageSetup paperSize="1" orientation="default" scale="100" fitToHeight="1" fitToWidth="1"/>';

				// append header and footer
				$xml .= '<headerFooter differentOddEven="false" differentFirst="false" scaleWithDoc="true" alignWithMargins="true"><oddHeader></oddHeader><oddFooter></oddFooter><evenHeader></evenHeader><evenFooter></evenFooter><firstHeader></firstHeader><firstFooter></firstFooter></headerFooter>';
				
				$xml .= '</worksheet>';
				
				$xml .= '';
				$this->zipFile->addFile($xml, 'xl/worksheets/sheet' . ($idx + 1) . '.xml');
			}
		}
			
		public static function i2c($idx) {
			static $cache = Array();
			if( $idx < 0 ) return "A";
			if( isset($cache[$idx]) ) return $cache[$idx];
			$ostr = "";
			$b = 65;
			$idxo = $idx;
			while($b == 65 || $idx > 0) {
				$ostr = chr($b + $idx % 26) . $ostr;
				$idx = (int)($idx / 26);
				$b = 64;
			}
			return $cache[$idxo] = $ostr;
		}
	}
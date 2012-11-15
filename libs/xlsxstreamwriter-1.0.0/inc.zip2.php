<?php
/**
 * Zip file creation class.
 * Makes zip files.
 *
 * Based on :
 *
 *  http://www.zend.com/codex.php?id=535&single=1
 *  By Eric Mueller <eric@themepark.com>
 *
 *  http://www.zend.com/codex.php?id=470&single=1
 *  by Denis125 <webmaster@atlant.ru>
 *
 *  a patch from Peter Listiak <mlady@users.sourceforge.net> for last modified
 *  date and time of the compressed file
 *
 * Official ZIP file format: http://www.pkware.com/appnote.txt
 *
 * @access  public
 */
class ZipFile2 {
	
	/**
	 * Central directory store
	 * @var string
	 */
	var $ctrl_dir = '';
	
	/**
	 * Item in Central directory store count
	 * @var int
	 */
	var $itemCount = 0;
	
	/**
	 * End of central directory record
	 * @var string
	 */
	var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";

	/**
	 * Last offset position
	 * @var int
	 */
	var $old_offset = 0;
	
	/**
	 * Zip data size
	 * @access private
	 */
	var $dataSize = 0;
	
	/**
	 * Zip file handle
	 * @access private
	 */
	var $fileHandle = null;
	
	/**
	 * Open zip file for writing. This must be called before any other operation.
	 * @param string $filename - filename to write to
	 * @access public
	 */
	function open($filename) {
		$this->old_offset = 0;
		$this->ctrl_dir = '';
		$this->itemCount = null;
		if ($this->fileHandle) {
			fclose($this->fileHandle);
			$this->fileHandle = null;
		}
		$this->dataSize = 0;
		$this->fileHandle = @fopen($filename, 'w');
		return !empty($this->fileHandle);
	}
	
	/**
	 * Finalize and close zip archive.
	 * You must call this method to close archive, o it won't be usable.
	 */
	function close() {
		if ($this->fileHandle) {
			
			//$data		= implode('', $this->datasec);

			fwrite($this->fileHandle, $this->ctrl_dir);
			fwrite($this->fileHandle, $this->eof_ctrl_dir);
			
			// total # of entries "on this disk"
			fwrite($this->fileHandle, pack('v', $this->itemCount));
			// total # of entries overall
			fwrite($this->fileHandle, pack('v', $this->itemCount));
			// size of central dir
			fwrite($this->fileHandle, pack('V', strlen($this->ctrl_dir)));
			// offset to start of central dir
			fwrite($this->fileHandle, pack('V', $this->dataSize));
			// .zip file comment length
			fwrite($this->fileHandle, "\x00\x00");
			
			fclose($this->fileHandle);
		}
	}
	
	/**
	 * Converts an Unix timestamp to a four byte DOS date and time format (date
	 * in high two bytes, time in low two bytes allowing magnitude comparison).
	 *
	 * @param  integer  the current Unix timestamp
	 *
	 * @return integer  the current date in a four byte DOS format
	 *
	 * @access private
	 */
	function unix2DosTime($unixtime = 0) {
		$timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

		if ($timearray['year'] < 1980) {
			$timearray['year']		= 1980;
			$timearray['mon']		= 1;
			$timearray['mday']		= 1;
			$timearray['hours']		= 0;
			$timearray['minutes']	= 0;
			$timearray['seconds']	= 0;
		}

		return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
				($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	}

	/**
	 * Adds "file" to archive
	 *
	 * @param  string   file contents
	 * @param  string   name of the file in the archive (may contains the path)
	 * @param  integer  the current timestamp
	 *
	 * @access public
	 */
    function addFile($data, $name, $time = 0) {
		$name = str_replace('\\', '/', $name);
		
		$hexdtime = pack('V', $this->unix2DosTime($time));
		$ds = 0;
		
		$ds += fwrite($this->fileHandle, "\x50\x4b\x03\x04");
		$ds += fwrite($this->fileHandle, "\x14\x00");			// ver needed to extract
		$ds += fwrite($this->fileHandle, "\x00\x00");			// gen purpose bit flag
		$ds += fwrite($this->fileHandle, "\x08\x00");			// compression method
		$ds += fwrite($this->fileHandle, $hexdtime);			// last mod time and date
		
		// "local file header" segment
		$unc_len	= strlen($data);
		$crc		= crc32($data);
		$zdata		= gzcompress($data); $data = null;
		$zdata		= substr($zdata, 2, -4);	// fix crc bug
		$c_len		= strlen($zdata);
		$ds += fwrite($this->fileHandle, pack('V', $crc));		// crc32
		$ds += fwrite($this->fileHandle, pack('V', $c_len));	// compressed filesize
		$ds += fwrite($this->fileHandle, pack('V', $unc_len));	// uncompressed filesize
		$ds += fwrite($this->fileHandle, pack('v', strlen($name)));	// length of filename
		$ds += fwrite($this->fileHandle, pack('v', 0));			// extra field length
		$ds += fwrite($this->fileHandle, $name);

		// "file data" segment
		$ds += fwrite($this->fileHandle, $zdata);
		
		$this->dataSize += $ds;
		
		// now add to central directory record
		$cdrec = "\x50\x4b\x01\x02";
		$cdrec .= "\x00\x00";					// version made by
		$cdrec .= "\x14\x00";					// version needed to extract
		$cdrec .= "\x00\x00";					// gen purpose bit flag
		$cdrec .= "\x08\x00";					// compression method
		$cdrec .= $hexdtime;					// last mod time & date
		$cdrec .= pack('V', $crc);				// crc32
		$cdrec .= pack('V', $c_len);			// compressed filesize
		$cdrec .= pack('V', $unc_len);			// uncompressed filesize
		$cdrec .= pack('v', strlen($name) );	// length of filename
		$cdrec .= pack('v', 0 );				// extra field length
		$cdrec .= pack('v', 0 );				// file comment length
		$cdrec .= pack('v', 0 );				// disk number start
		$cdrec .= pack('v', 0 );				// internal file attributes
		$cdrec .= pack('V', 32 );				// external file attributes - 'archive' bit set

		$cdrec .= pack('V', $this->old_offset);	// relative offset of local header
		$this->old_offset += $ds;

		$cdrec .= $name;

		// optional extra field, file comment goes here
		// save to central directory
		$this->ctrl_dir .= $cdrec;
		$this->itemCount++;
    }
	
	/**
	 * Adds entire dir contents recoursively to archive
	 *
	 * @param string $dir - dir to be added
	 * 
	 * @access public
	 */
	function addDirContents($dir, $rel_path = '') {
		$dir = preg_replace('#[/]+$#i', '', trim($dir));
		if (!is_dir($dir)) return false;
		
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ($file == '.' || $file == '..') continue;
				$type = filetype($dir.'/'.$file);
				if ($type == 'dir') {
					$this->addDirContents($dir.'/'.$file, $rel_path.'/'.$file);
				} else if ($type == 'file') {
					$this->addFile(file_get_contents($dir.'/'.$file),
						($rel_path ? ($rel_path.'/') : '').$file, filemtime($dir.'/'.$file));
				}
			}
			closedir($dh);
		}
	}
}
?>
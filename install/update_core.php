<?php
	@header('Content-Type: text/plain; charset=utf-8');

	$basePath = dirname(__FILE__);
	$snapshotDir = $basePath . '/snapshots';

	if( !is_file($versionFile = $basePath . '/../core/version.php') ) {
		echo 'Framework cannot be updated since it is not installed properly or has a very old version.';
		exit;
	}
	
	include $versionFile;

	function download($url, $post_vars = false, $out_file = '', $maxRedirs = 3) {
		$post_contents = '';
		if( $post_vars ) {
			if( is_array($post_vars) ) {
				foreach( $post_vars as $key => $val ) {
					$post_contents .= ($post_contents ? '&' : '').urlencode($key).'='.urlencode($val);
				}
			}
			else {
				$post_contents = $post_vars;
			}
		}
		
		if( $out_file ) {
			$fl = fopen($out_file, 'w+');
			if (!$fl) return false;
		}

		$uinf = parse_url($url);
		$host = $uinf['host'];
		$path = $uinf['path'];
		$path .= (isset($uinf['query']) && $uinf['query']) ? ('?'.$uinf['query']) : '';
		$headers = Array(
			($post_contents ? 'POST' : 'GET')." {$path} HTTP/1.1",
			"Host: {$host}",
		);
		if( $post_contents ) {
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			$headers[] = 'Content-Length: '.strlen($post_contents);
		}
		$headers[] = 'User-Agent: VDL/1.0';
		
		$response = new stdClass();
		$headerFunc = function($ch, $header) use ($response) {
			$response->header[] = $header;
			return strlen($header);
		};
		
		for( $redirs = $maxRedirs; $redirs > 0; $redirs-- ) {
			$response->header = array();
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);
			if( $out_file )
				curl_setopt($ch, CURLOPT_FILE, $fl);
			else
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 600);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADERFUNCTION, $headerFunc);

			if( $post_contents ) {
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_contents);
			}
			
			$data = curl_exec($ch);
			if( curl_errno($ch) )
				return false;
			
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if( ($code == 301 || $code == 302 || $code == 303 || $code == 307) && preg_match('#^Location:\\s*(.*?)\\s*$#im', implode('', $response->header), $mtc) ) {
				$prevUrl = $url;
				$url = $mtc[1];
				curl_close($ch);
				if( $out_file ) {
					// response was not what we needed so reset the file
					fseek($fl, 0, SEEK_SET);
					ftruncate($fl, 0);
				}
				$uinf = parse_url($url);
				$host = $uinf['host'];
				$path = $uinf['path'];
				$path .= (isset($uinf['query']) && $uinf['query']) ? ('?'.$uinf['query']) : '';
				$headers[0] = ($post_contents ? 'POST' : 'GET')." {$path} HTTP/1.1";
				$headers[1] = "Host: {$host}";
				// $headers['referer'] = "Referer: {$prevUrl}";
				continue;
			}
			
			curl_close($ch);
			break;
		}

		if( $out_file ) fclose($fl);
		
		return $data;
	}
	
	function rmdir_recursive($path) {
		$path = rtrim($path, '/');
		if( !is_dir($path) || !is_readable($path) || !is_writable($path) ) {
			return false;
		}
		$dir = opendir($path);
		while( $f = readdir($dir) ) {
			if( $f == '.' || $f == '..' )
				continue;
			$f = "{$path}/{$f}";
			
			if( is_dir($f) )
				rmdir_recursive($f);
			else
				unlink($f);
		}
		closedir($dir);
		return rmdir($path);
	}
	
	function mkdir_recursive($path, $chmod = 0775) {
		$pathArr = null;
		$openBaseDir = explode(':', ini_get('open_basedir'));
		foreach( $openBaseDir as $baseDir ) {
			if( $baseDir == '' )
				continue;
			$len = strlen($baseDir);
			if( substr($path, 0, $len) == $baseDir ) {
				$currentPath = $baseDir;
				$pathArr = explode('/', rtrim(substr($path, $len), '/'));
			}
		}
		if( $pathArr === null ) {
			$currentPath = '';
			$pathArr = explode('/', rtrim($path, '/'));
		}
		foreach( $pathArr as $name ) {
			$currentPath .= $name;
			if( $name != '' && $name != '.' && $name != '..' ) {
				if( !is_dir($currentPath) ) {
					if( !mkdir($currentPath) )
						return false;
					clearstatcache();
					if( !chmod($currentPath, $chmod) )
						return false;
				}
			}
			$currentPath .= '/';
		}
		return true;
	}

	$release = json_decode(download('https://api.github.com/repos/profis/profiscms/releases/latest'), true);
	if( !is_array($release) || !isset($release['tag_name'], $release['zipball_url']) ) {
		echo 'Framework cannot be updated since github API is currently unavailable or has changed.';
		exit;
	}
	
	$version = substr($release['tag_name'], 1);
	if( $version == PC_VERSION ) {
		echo "Already up-to-date.\n";
		if( isset($_REQUEST['clear']) ) {
			if( is_dir($snapshotDir) ) {
				if( rmdir_recursive($snapshotDir) )
					echo "Snapshots and backups are cleared\n";
				else
					echo "Failed clearing snapshots and backups\n";
			}
			else
				echo "Snapshots and backups are cleared\n";
		}
		exit;
	}

	echo "Creating directory for snapshots\n";
	if( !is_dir($snapshotDir) && !mkdir_recursive($snapshotDir) )
		exit;

	$releaseDir = $snapshotDir . '/' . $release['tag_name'];

	echo "Creating directory for latest release\n";
	if( is_dir($releaseDir) ) {
		echo "Removing already extacted files\n";
		if( !rmdir_recursive($releaseDir) )
			exit;
	}
	if( !mkdir_recursive($releaseDir) )
		exit;
	
	// print_r($release);
	
	$releaseFile = $snapshotDir . '/' . $release['tag_name'] . '.zip';
	@unlink($releaseFile);
	$result = download($release['zipball_url'], false, $releaseFile);
	
	if( !$result ) {
		echo "Failed to download latest release.\n";
		exit;
	}
	
	
	$zip = zip_open($releaseFile);
	if( !$zip ) {
		echo "Failed to open downloaded latest release ZIP file.\n";
		exit;
	}
	$extractEntries = array(
		'admin/',
		'core/',
		'libs/',
		'media/',
		// 'install/', // NEVER INCLUDE INSTALL DIRECTORY BY ITSELF!
		'install/data/',
		'install/update.php',
	);
	while( $entry = zip_read($zip) ) {
		$fileName = preg_replace('#^[^/]+/#', '', zip_entry_name($entry));
		if( $fileName == '/' )
			continue;
		$ok = false;
		foreach( $extractEntries as $filter ) {
			$len = strlen($filter);
			if( strlen($fileName) >= $len && substr($fileName, 0, $len) == $filter ) {
				$ok = true;
				break;
			}
		}
		if( !$ok )
			continue;
		$length = zip_entry_filesize($entry);
		echo "Extracting: {$fileName} ({$length} bytes) \n";
		$filePath = $releaseDir . '/' . $fileName;
		
		if( substr($fileName, -1) == '/' ) {
			$filePath = rtrim($filePath, '/');
			if( !is_dir($filePath) && !mkdir_recursive($filePath) ) {
				zip_close($zip);
				exit;
			}
			continue;
		}
		if( !zip_entry_open($zip, $entry, 'rb') ) {
			zip_close($zip);
			echo "Error opening entry in latest release ZIP archive.";
			exit;
		}

		$fh = fopen($filePath, 'wb');
		if( !$fh ) {
			zip_entry_close($entry);
			zip_close($zip);
			echo "Error opening target file for writing.";
			exit;
		}
		while( $length > 0 ) {
			$read = zip_entry_read($entry, min($length, 65536));
			if( empty($read) ) {
				fclose($fh);
				clearstatcache();
				chmod($filePath, 0664);
				zip_entry_close($entry);
				zip_close($zip);
				echo "Failed to read another {$length} bytes from the ZIP entry.";
				exit;
			}
			$size = strlen($read);
			$length -= $size;
			if( fwrite($fh, $read) != $size ) {
				fclose($fh);
				clearstatcache();
				chmod($filePath, 0664);
				zip_entry_close($entry);
				zip_close($zip);
				echo "Failed to read another {$size} bytes to the target file.";
				exit;
			}
		}
		fclose($fh);
		clearstatcache();
		chmod($filePath, 0664);
		zip_entry_close($entry);
	}
	zip_close($zip);
	
	echo "Creating backup of current version\n";
	$backupDir = $snapshotDir . '/v' . PC_VERSION . '.bak';
	if( is_dir($backupDir) ) {
		echo "Backup directory for current version already exists.";
		exit;
	}
	if( !mkdir_recursive($backupDir) )
		exit;

	$moved = array();
	function rollback($moved, $backupDir) {
		echo "Rolling back move operations\n";
		$err = false;
		foreach( $moved as $move ) {
			echo "Rolling back {$move[0]} to {$move[1]}\n";
			if( !rename($move[0], $move[1]) )
				$err = true;
		}
		if( $err )
			echo "CRITICAL ERROR: Failed to roll back all movement operations. Please check the server security and move directories/files back manually to restore the website functionality.";
		else
			rmdir($backupDir);
		exit;
	}
	
	$rootPath = dirname($basePath);
	echo "Moving current version to the backup directory\n";
	foreach( $extractEntries as $entry ) {
		$entry = rtrim($entry, '/');

		$from = $rootPath . '/' . $entry;
		if( !file_exists($from) )
			continue; // the path does not exist in old version
		
		$to = $backupDir . '/' . $entry;

		if( $dir = dirname($to) ) {
			if( !is_dir($dir) ) {
				echo "Creating required directory {$dir}\n";
				if( !mkdir_recursive($dir) )
					rollback($moved, $backupDir);
			}
		}

		echo "Moving {$from} to {$to}\n";
		if( !rename($from, $to) )
			rollback($moved, $backupDir);
		array_unshift($moved, array($to, $from));
	}
	
	echo "Moving latest release in place of current version\n";
	foreach( $extractEntries as $entry ) {
		$entry = rtrim($entry, '/');

		$from = $releaseDir . '/' . $entry;
		if( !file_exists($from) )
			continue; // the path does not exist in newer version

		$to = $rootPath . '/' . $entry;

		if( $dir = dirname($to) ) {
			if( !is_dir($dir) ) {
				echo "Creating required directory {$dir}\n";
				if( !mkdir_recursive($dir) )
					rollback($moved, $backupDir);
			}
		}
		
		echo "Moving {$from} to {$to}\n";
		if( !rename($from, $to) )
			rollback($moved, $backupDir);
		array_unshift($moved, array($to, $from));
	}
	
	echo "Removing downloaded latest release file\n";
	unlink($releaseFile);

	echo "Removing latest release temporary extraction directory\n";
	rmdir_recursive($releaseDir); // recursive, because it may contain empty subdirectories;
	
	echo "Core successfully updated to version " . $release['tag_name'] . "\n";
	echo "Do an additional check if update was successful and if needed add \"?clear=1\" to the end of current url in order to remove leftover snapshots and backups.\n";

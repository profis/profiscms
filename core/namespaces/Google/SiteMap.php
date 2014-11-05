<?php
# ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http:#www.gnu.org/licenses/>.

namespace Google;

/**
 * Class SiteMap
 *
 * A class that implements Google site map API.
 *
 * @todo Add support for video: https://support.google.com/webmasters/answer/183668?hl=en&ref_topic=6080646&rd=1
 * @todo Add support for news: https://support.google.com/news/publisher/answer/74288?hl=en&ref_topic=4359874
 *
 * @link http://www.sitemaps.org/protocol.html
 * @link https://support.google.com/webmasters/answer/183668?hl=en&ref_topic=6080646&rd=1
 * @package Google
 */
class SiteMap {
	private $target = null;
	private $targetOpened = false;

	public function SiteMap() {
	}

	/**
	 * Opens the target stream and writes the starting part of Google site map XML to the target.
	 *
	 * @param mixed $target Site map output target. It may be either a stream resource, string containing output file name or NULL for direct PHP output, which is equivalent to 'php://output'.
	 * @throws \InvalidArgumentException
	 * @see close()
	 */
	public function open($target = null) {
		if( $this->target != null )
			$this->close();
		if( $target === null )
			$target = 'php://output';
		if( is_string($target) ) {
			$this->targetOpened = true;
			$target = @fopen($target, 'w');
			if( !$target )
				throw new \InvalidArgumentException('Target is not writable');
		}

		if( is_resource($target) ) {
			if( get_resource_type($target) == 'stream' ) {
				$meta = stream_get_meta_data($target);
				if( strpos($meta['mode'], 'w') === 'false' ) {
					if( $this->targetOpened )
						fclose($target);
					throw new \InvalidArgumentException('Target is not writable');
				}
				$this->target = $target;
			}
			else
				throw new \InvalidArgumentException('Target is not a stream resource');
		}
		else
			throw new \InvalidArgumentException('Target is not a stream resource');

		$this->write('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n");
	}

	/**
	 * Outputs an URL node of Google site map XML.
	 *
	 * @param string $route URI, relative to the base URL of the site.
	 * @param string $changeFrequency How often is the page updated. May be either 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly' or 'never'. Defaults to 'weekly'.
	 * @param float $priority The priority of the page relative to other pages. May be a number between 0 and 1. Defaults to 0.5.
	 * @param string[] $images An array of image URLs associated with the page or NULL to skip adding images. Defaults to NULL.
	 * @throws \RuntimeException When SiteMap::open() was not called before calling this method.
	 */
	public function addURL($route, $changeFrequency = 'weekly', $priority = 0.5, $images = null) {
		global $cfg;
		$this->write("<url>");
		$this->write("<loc>" . htmlspecialchars($cfg['url']['base'] . ltrim($route, '/')) . "</loc>");
		if( is_array($images) )
			foreach( $images as $image )
				$this->write("<image:image><image:loc>" . htmlspecialchars($image) . "</image:loc></image:image>");
		$this->write("<changefreq>" . htmlspecialchars($changeFrequency) . "</changefreq>");
		if( $priority != 0.5 )
			$this->write("<priority>" . max(0, min(floatval($priority), 1)) . "</priority>");
		$this->write("</url>\n");
	}

	/**
	 * Writes text to the currently open target.
	 *
	 * @throws \RuntimeException When SiteMap::open() was not called before calling this method.
	 * @see open()
	 */
	public function write($text) {
		if( !$this->target )
			throw new \RuntimeException("Site map output is not open. Please use SiteMap::open() before writing.");
		fwrite($this->target, $text);
	}
	
	/**
	 * Closes the site map XML.
	 *
	 * @throws \RuntimeException When SiteMap::open() was not called before calling this method.
	 * @see open()
	 */
	public function close() {
		$this->write('</urlset>');

		if( $this->targetOpened )
			fclose($this->target);
		$this->target = null;
		$this->targetOpened = false;
	}
}
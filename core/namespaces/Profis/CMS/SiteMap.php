<?php
namespace Profis\CMS;

class SiteMap extends \Google\SiteMap {
	protected $pages = array();

	/**
	 * Adds a page to the queue that will be loaded at the end of the site map.
	 *
	 * @param int|array $id Single Id or an array of page Ids.
	 * @param string|array $language Single language code or an array of language codes.
	 * @param string $changeFrequency How often is the page updated. May be either 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly' or 'never'. Defaults to 'weekly'.
	 * @param float $priority The priority of the page relative to other pages. May be a number between 0 and 1. Defaults to 0.5.
	 * @param string[] $images An array of image URLs associated with the page or NULL to skip adding images. Defaults to NULL.
	 * @see addURL()
	 * @see flushPages()
	 */
	public function addPage($id, $language, $changeFrequency = 'weekly', $priority = 0.5, $images = null) {
		if( is_array($id) ) {
			foreach( $id as $v )
				$this->addPage($v, $language, $changeFrequency, $priority);
			return;
		}
		if( is_array($language) ) {
			foreach( $language as $lang )
				$this->addPage($id, $lang, $changeFrequency, $priority);
			return;
		}
		$this->pages["(" . $id . ",'" . $language . "')"] = array($changeFrequency, $priority, $images);
		if( count($this->pages) >= 100 )
			$this->flushPages();
	}

	/**
	 * Adds URLs of all queued pages to the site map and cleans page queue.
	 *
	 * @throws \DbException In case of a database usage error.
	 * @throws \RuntimeException When SiteMap::open() was not called before calling this method.
	 * @see addPage()
	 */
	public function flushPages() {
		global $cfg, $site, $db;
		if( !empty($this->pages) ) {
			$s = $db->prepare($q = "SELECT pid,route,ln FROM {$cfg['db']['prefix']}content WHERE (pid,ln) IN(" . implode(',', array_keys($this->pages)) . ")");
			if( !$s->execute() )
				throw new \DbException($s->errorInfo(), $q);

			while( $row = $s->fetch() ) {
				$key = "(" . $row['pid'] . ",'" . $row['ln'] . "')";
				$this->addURL($site->Get_link($row['route'], $row['ln']), $this->pages[$key][0], $this->pages[$key][1], $this->pages[$key][2]);
			}
			$this->pages = array();
		}
	}

	/**
	 * Flushes the page queue and closes the site map XML.
	 *
	 * @throws \DbException In case of a database usage error.
	 * @throws \RuntimeException When SiteMap::open() was not called before calling this method.
	 * @see open()
	 */
	public function close() {
		$this->flushPages();
		parent::close();
	}
}
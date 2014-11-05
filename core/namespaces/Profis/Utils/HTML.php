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

namespace Profis\Utils;

/**
 * Class HTML
 *
 * A collection of static methods to help with HTML generation.
 *
 * @package Profis\Utils
 */
class HTML {
	public static function openTag($tagName, $attributes = array(), $hasNoClosingTag = false) {
		$html = '<' . $tagName;
		foreach( $attributes as $key => $value ) {
			if( $value === null )
				continue;
			if( $value === false )
				$value = 0;
			else if( $value === true )
				$value = 1;
			if( is_object($value) )
				$value = $value->__toString();
			$html .= ' ' . $key . '="' . str_replace('"', '&quot;', htmlspecialchars($value)) . '"';
		}
		return $html . ($hasNoClosingTag ? ' />' : '>');
	}
	
	public static function closeTag($tagName) {
		return '</' . $tagName . '>';
	}

	public static function tag($tagName, $attributes = array(), $content = "", $hasNoClosingTag = false) {
		$html = self::openTag($tagName, $attributes, $hasNoClosingTag);
		if( $hasNoClosingTag )
			return $html;
		return $html . $content . self::closeTag($tagName);
	}

	public static function link($text = "", $href = "#", $attributes = array()) {
		if( !empty($text) && !isset($attributes['title']) )
			$attributes['title'] = strip_tags($text);
		if( !empty($href) )
			$attributes['href'] = $href;
		return self::tag('a', $attributes, $text);
	}
}
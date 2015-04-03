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

namespace Profis;

final class GlobalEvents {
	/** @var Event[] */
	private static $events = array();

	/**
	 * Add an event listener to the specified event pool.
	 *
	 * @param string $eventName A global event name.
	 * @param mixed $callback A callable handler.
	 * @param bool $addFirst TRUE to add to the beginning of the event listeners list.
	 */
	public static function addListener($eventName, $callback, $addFirst = false) {
		if( !isset(self::$events[$eventName]) )
			self::$events[$eventName] = new Event();
		self::$events[$eventName]->addListener($callback, $addFirst);
	}

	/**
	 * Calls all listeners attached to a specified global event.
	 *
	 * @param string $eventName A global event name.
	 * @param EventArgs $eventArgs Arguments that must be passed to event listeners.
	 */
	public static function invoke($eventName, EventArgs $eventArgs = null) {
		if( !isset(self::$events[$eventName]) )
			return;
		self::$events[$eventName]->invoke($eventArgs);
	}
}
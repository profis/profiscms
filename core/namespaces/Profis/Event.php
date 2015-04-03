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

class Event {
	private $listeners = array();

	/**
	 * Add a listener to this event.
	 *
	 * @param mixed $callback A callable handler.
	 * @param bool $addFirst TRUE to add to the beginning of the event listeners list.
	 */
	public function addListener($callback, $addFirst = false) {
		if( $addFirst )
			array_unshift($this->listeners, $callback);
		else
			$this->listeners[] = $callback;
	}

	/**
	 * Calls all event listeners attached to this event.
	 *
	 * @param EventArgs $eventArgs Arguments that must be passed to event listeners.
	 */
	public function invoke(EventArgs $eventArgs = null) {
		if( empty($this->listeners) )
			return;
		if( $eventArgs === null )
			$eventArgs = new EventArgs();
		foreach( $this->listeners as $callback ) {
			call_user_func($callback, $eventArgs);
			if( $eventArgs->isPropagationStopped() )
				break;
		}
	}
} 
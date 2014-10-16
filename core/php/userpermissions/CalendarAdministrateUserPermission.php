<?php

namespace userpermissions;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class CalendarAdministrateUserPermission extends \BaseUserPermission {

	public function getUserPermissionKey() { return 'CALENDAR_ADMINISTRATE'; }

	public function isForSite() { return true; }

	public function requiresUser() { return true; }

	public function requiresEditorUser() { return true; }

}

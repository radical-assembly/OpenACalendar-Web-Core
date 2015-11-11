<?php
/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */


$app->post('/api2/radicalassembly/list/{slug}/event/create.json', "org\openacalendar\\radicalassembly\siteapi2\controllers\EventController::createJson")
    	->before($appUserRequired)
    	->before($appUserPermissionCalendarChangeRequired)
    	->assert('slug', FRIENDLY_SLUG_REGEX);

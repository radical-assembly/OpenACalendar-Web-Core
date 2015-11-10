<?php
/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */


/* AP1 Routing */
$app->match('/api1/radicalassembly/list/{slug}/events.ical', "org\openacalendar\\radicalassembly\siteapi1\controllers\EventListController::ical")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/list/{slug}/events.json', "org\openacalendar\\radicalassembly\siteapi1\controllers\EventListController::json")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/list/{slug}/events.jsonp', "org\openacalendar\\radicalassembly\siteapi1\controllers\EventListController::jsonp")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/list/{slug}/events.csv', "org\openacalendar\\radicalassembly\siteapi1\controllers\EventListController::csv")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/list/{slug}/events.create.atom', "org\openacalendar\\radicalassembly\siteapi1\controllers\EventListController::atomCreate")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/list/{slug}/events.before.atom', "org\openacalendar\\radicalassembly\siteapi1\controllers\EventListController::atomBefore")
		->assert('slug', FRIENDLY_SLUG_REGEX);

/* AP2 Routing */
$app->post('/api2/radicalassembly/list/{slug}/event/create.json', "org\openacalendar\\radicalassembly\siteapi2\controllers\EventController::createJson")
    	->before($appUserRequired)
    	->before($appUserPermissionCalendarChangeRequired)
    	->assert('slug', FRIENDLY_SLUG_REGEX);

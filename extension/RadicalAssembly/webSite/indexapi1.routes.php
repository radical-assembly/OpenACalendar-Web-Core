<?php
/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */

$app->match('/api1/radicalassembly/list/{slug}/events.fullcalendar', "org\openacalendar\\radicalassembly\siteapi1\controllers\EventListController::fullcalendar")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/list/{slug}/events.json', "org\openacalendar\\radicalassembly\siteapi1\controllers\EventListController::json")
 		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/list/{slug}/events.ical', "org\openacalendar\\radicalassembly\siteapi1\controllers\EventListController::ical")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/list/{slug}/events.create.atom', "org\openacalendar\\radicalassembly\siteapi1\controllers\EventListController::atomCreate")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/list/{slug}/events.before.atom', "org\openacalendar\\radicalassembly\siteapi1\controllers\EventListController::atomBefore")
		->assert('slug', FRIENDLY_SLUG_REGEX);

$app->match('/api1/radicalassembly/venues.json', "org\openacalendar\\radicalassembly\siteapi1\controllers\VenueListController::listJson");
$app->match('/api1/radicalassembly/tags.json', "org\openacalendar\\radicalassembly\siteapi1\controllers\TagListController::listJson");
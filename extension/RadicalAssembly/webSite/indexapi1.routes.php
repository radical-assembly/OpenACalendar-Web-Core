<?php
/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */



$app->match('/api1/radicalassembly/events.ical', "org\openacalendar\\radicalassembly\siteapi1\controllers\RAEventListController::ical")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/events.json', "org\openacalendar\\radicalassembly\siteapi1\controllers\RAEventListController::json")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/events.jsonp', "org\openacalendar\\radicalassembly\siteapi1\controllers\RAEventListController::jsonp")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/events.csv', "org\openacalendar\\radicalassembly\siteapi1\controllers\RAEventListController::csv")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/events.create.atom', "org\openacalendar\\radicalassembly\siteapi1\controllers\RAEventListController::atomCreate")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/radicalassembly/events.before.atom', "org\openacalendar\\radicalassembly\siteapi1\controllers\RAEventListController::atomBefore")
		->assert('slug', FRIENDLY_SLUG_REGEX);

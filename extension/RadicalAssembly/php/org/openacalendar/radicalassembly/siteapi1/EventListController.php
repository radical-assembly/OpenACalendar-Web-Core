<?php

namespace org\openacalendar\radicalassembly\siteapi1\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use org\openacalendar\radicalassembly\api1exportbuilders\EventListCSVBuilder;
use org\openacalendar\radicalassembly\api1exportbuilders\EventListICalBuilder;
use org\openacalendar\radicalassembly\api1exportbuilders\EventListJSONBuilder;
use org\openacalendar\radicalassembly\api1exportbuilders\EventListJSONPBuilder;
use org\openacalendar\radicalassembly\api1exportbuilders\EventListATOMBeforeBuilder;
use org\openacalendar\radicalassembly\api1exportbuilders\EventListATOMCreateBuilder;

use repositories\builders\filterparams\EventFilterParams;

/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */
class EventListController {


	protected $parameters = array();

	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array();

		if (strpos($slug,"-") > 0) {
			$slugBits = explode("-", $slug, 2);
			$slug = $slugBits[0];
		}

		$clr = new CuratedListRepository();
		$this->parameters['curatedlist'] = $clr->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['curatedlist']) {
			return false;
		}

		return true;
	}


	function ical($slug, Request $request, Application $app) {

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}

		$ical = new EventListICalBuilder($app['currentSite'], $app['currentTimeZone'], $this->parameters['curatedlist']->getTitle());
		$ical->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
		$ical->build();
		return $ical->getResponse();

	}

	function json($slug, Request $request, Application $app) {

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}


		$json = new EventListJSONBuilder($app['currentSite'], $app['currentTimeZone']);
		$json->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);
		$json->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias", false));
        $json->setIncludeGroups($ourRequest->getGetOrPostBoolean("includeGroups", true));
		$json->setIncludeTags($ourRequest->getGetOrPostBoolean("includeTags", true));
		$json->build();
		return $json->getResponse();

	}


}

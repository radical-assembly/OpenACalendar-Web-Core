<?php

namespace org\openacalendar\radicalassembly\siteapi1\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use org\openacalendar\radicalassembly\api1exportbuilders\EventListICalBuilder;
use org\openacalendar\radicalassembly\api1exportbuilders\EventListJSONBuilder;
use org\openacalendar\radicalassembly\api1exportbuilders\EventListFullCalendarDataBuilder;
use org\openacalendar\curatedlists\repositories\CuratedListRepository;
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

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist.");
		}

		$ical = new EventListICalBuilder($app['currentSite'], $app['currentTimeZone'], $this->parameters['curatedlist']->getTitle());
		$ical->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);

		$tags = $ourRequest->getGetOrPostString('tags', '');
		if ($tags) $ical->getEventFilter()->setTags(explode(',', $tags));

		$groups = $ourRequest->getGetOrPostString('groups', '');
		if ($groups) $ical->getEventFilter()->setGroups(explode(',', $groups));

		$startAfter = $ourRequest->getGetOrPostString('start', '');
		if ($startAfter) $ical->getEventFilter()->setStartTime(new \DateTime($startAfter));

		$endBefore = $ourRequest->getGetOrPostString('end', '');
		if ($endBefore) $ical->getEventFilter()->setEndTime(new \DateTime($endBefore));

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

	function fullcalendar($slug, Request $request, Application $app) {

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "curatedlist does not exist");
		}

		$json = new EventListFullCalendarDataBuilder($app['currentSite'], $app['currentTimeZone']);
		$json->getEventRepositoryBuilder()->setCuratedList($this->parameters['curatedlist']);

		$tags = $ourRequest->getGetOrPostString('tags', '');
		if ($tags) $json->getEventFilter()->setTags(explode(',', $tags));

		$groups = $ourRequest->getGetOrPostString('groups', '');
		if ($groups) $json->getEventFilter()->setGroups(explode(',', $groups));

		$startAfter = $ourRequest->getGetOrPostString('start', '');
		if ($startAfter) $json->getEventFilter()->setStartTime(new \DateTime($startAfter));

		$endBefore = $ourRequest->getGetOrPostString('end', '');
		if ($endBefore) $json->getEventFilter()->setEndTime(new \DateTime($endBefore));

		$json->build();
		return $json->getResponse();

	}


}

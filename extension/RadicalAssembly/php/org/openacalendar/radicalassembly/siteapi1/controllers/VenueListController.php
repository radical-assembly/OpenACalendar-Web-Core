<?php

namespace org\openacalendar\radicalassembly\siteapi1\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\builders\VenueRepositoryBuilder;
use org\openacalendar\curatedlists\repositories\builders\CuratedListRepositoryBuilder;

/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */
class VenueListController
{

	public function listJson (Request $request, Application $app) {

		$vrb = new VenueRepositoryBuilder();
		$vrb->setSite($app['currentSite']);

        $lrb = new CuratedListRepositoryBuilder();
        $lrb->setSite($app['currentSite']);

		$ourRequest = new \Request($request);
		$vrb->setIncludeDeleted($ourRequest->getGetOrPostBoolean('include_deleted', false));

        $listTitle = $ourRequest->getGetOrPostString('list', 'adminapproved')

		$out = array ('venues'=> array());

		foreach($vrb->fetchAll() as $venue) {
            $events = $venue->getCachedFutureEvents();
            $cachedEvents = array();
            foreach($events as $event) {
                $lrb->setContainsEvent($event);
                $lists = $lrb->fetchAll();
                foreach($lists as $list) {
                    if ($list->getTitle() === $listTitle) {
                        $cachedEvents[] = $event;
                    }
                }
            }
			$out['venues'][] = array(
				'slug'=>$venue->getSlug(),
				'slugForURL'=>$venue->getSlugForUrl(),
				'title'=>$venue->getTitle(),
				'lat'=>$venue->getLat(),
				'lng'=>$venue->getLng(),
				'cachedFutureEvents'=>$events,
			);
		}

		return json_encode($out);


	}

}

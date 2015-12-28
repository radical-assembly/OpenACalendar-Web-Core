<?php

namespace org\openacalendar\radicalassembly\siteapi1\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\builders\TagRepositoryBuilder;
use org\openacalendar\curatedlists\repositories\builders\CuratedListRepositoryBuilder;

/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */
class TagListController
{

	public function listJson (Request $request, Application $app) {

		$trb = new TagRepositoryBuilder();
		$trb->setSite($app['currentSite']);

        $lrb = new CuratedListRepositoryBuilder();
        $lrb->setSite($app['currentSite']);

		$ourRequest = new \Request($request);
		$trb->setIncludeDeleted($ourRequest->getGetOrPostBoolean('include_deleted', false));
        $listTitle = $ourRequest->getGetOrPostString('list', 'adminapproved');

		$out = array ('tags'=> array());

		foreach($trb->fetchAll() as $tag) {
			$out['tags'][] = array(
				'slug'=>$tag->getSlug(),
				'slugForURL'=>$tag->getSlugForUrl(),
				'title'=>$tag->getTitle(),
                'description'=>$tag->getDescription(),
			);
		}

		return json_encode($out);


	}

}

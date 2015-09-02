<?php

namespace siteapi2\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\EventRepository;
use repositories\CountryRepository;
use repositories\UserAccountRepository;
use models\EventModel;
use models\SiteModel;
use models\UserAccountModel;
use models\EventEditMetaDataModel;
use \SearchForDuplicateEvents;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventController {
	
	/** @var \models\EventModel **/
	protected $event;

	protected function build($slug, Request $request, Application $app) {

		
		
		$repo = new EventRepository();
		$this->event = $repo->loadBySlug($app['currentSite'], $slug);
		if (!$this->event) {
			return false;
		}
		
		return true;
		
		
	}
	


	public function infoJson ($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Does not exist.");
		}
		
		$out = array(
			'event'=>array(
				'slug'=>$this->event->getSlug(),
				'slugForURL'=>$this->event->getSlugForUrl(),
				'summary'=>$this->event->getSummary(),
				'summaryDisplay'=>$this->event->getSummaryDisplay(),
				'description'=>$this->event->getDescription(),
				'url'=>$this->event->getUrl(),
				'ticket_url'=>$this->event->getTicketUrl(),
			),
		);
		
		return json_encode($out);
	}


	public function postInfoJson ($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Does not exist.");
		}

		$ourRequest = new \Request($request);

		$edits = false;
		if ($ourRequest->hasGetOrPost('summary') && $this->event->setSummaryIfDifferent($ourRequest->getGetOrPostString('summary', ''))) {
			$edits = true;
		}
		if ($ourRequest->hasGetOrPost('description') && $this->event->setDescriptionIfDifferent($ourRequest->getGetOrPostString('description', ''))) {
			$edits = true;
		}
		if ($ourRequest->hasGetOrPost('url') && $this->event->setUrlIfDifferent($ourRequest->getGetOrPostString('url', ''))) {
			$edits = true;
		}
		if ($ourRequest->hasGetOrPost('ticket_url') && $this->event->setTicketUrlIfDifferent($ourRequest->getGetOrPostString('ticket_url', ''))) {
			$edits = true;
		}

		if ($edits) {
			$repo = new EventRepository();
			$repo->edit($this->event, $app['apiUser']);
			$out = array(
				'edited'=>true,
			);
		} else {
			$out = array(
				'edited'=>false,
			);
		}

		return json_encode($out);
	}

	public function createInfoJson (Request $request, Application $app) {

		global $DB;

		$data = $request->request->all();
		$eventData = $data['event_data'] ? (array) json_decode($data['event_data']) : null; // Weak: use of json_decode assumes something about form of the POST data.

		if (!$eventData) {
			return json_encode(array(
				'success'=>false,
				'msg'=>'No parameter \'event_data\' was found.',
			));
		};

		// Get default user for event submission
		$userRepo = new UserAccountRepository();
		$defaultUser = $userRepo->loadByUserName($eventData['username']);
		if (!$defaultUser) {
			return json_encode(array(
				'success'=>false,
				'msg'=>'Username passed to server is invalid.'
			));
		}

		// Create event model and set fields
		$event = new EventModel();
		$event->setSiteId($app['currentSite']->getId());

		// Create country model
		$countryRepo = new CountryRepository();
		$country = $countryRepo->loadByTwoCharCode("GB");

		// Create venue model
		if ($eventData['is_physical']) {
			$venue = new VenueModel();
			$venue->setTitle($eventData['venue_name']);
			$venue->setAddress($eventData['venue_address']);
			$venue->setAddressCode($eventData['venue_address_code']);
			$venue->setLat($eventData['venue_lat']);
			$venue->setLong($eventData['venue_long']);
			$venue->setCountryId($country->getId());

			$searchForDuplicateVenues = new SearchForDuplicateVenues($venue, $app['currentSite']);
			$venueDupes = $searchForDuplicateVenues->getPossibleDuplicates();
			if ($venueDupes && count($venueDupes)>1) {
				return json_encode(array(
					'success'=>false,
					'msg'=>'Multiple duplicate venues detected'
				));
			} elseif ($venueDupes && count($venueDupes)>0) {
				return json_encode(array(
					'success'=>false,
				 	'msg'=>'Duplicate venue detected',
					'duplicate'=>json_encode($venueDupes)
				));
			}
		}

		$event->setSummary($eventData['summary']);
		$event->setDescription($eventData['description']);
		$utc = new \DateTimeZone('UTC');
		$event->setStartAt(new \DateTime($eventData['start_at'], $utc));
		$event->setEndAt(new \DateTime($eventData['end_at'], $utc));
		$event->setGroupId($eventData['group_id']);
		$event->setGroupTitle($eventData['group_title']);
		$event->setIsDeleted($eventData['is_deleted']);
		$event->setIsCancelled($eventData['is_cancelled']);
		$event->setTimezone("Europe/London"); //TODO
		$event->setCountryId($country->getId());
		$event->setUrl($eventData['url']);
		$event->setTicketUrl($eventData['ticket_url']);
		$event->setIsVirtual($eventData['is_virtual']);
		$event->setIsPhysical($eventData['is_physical']);

		// Check if event is dupe before continuing
		$searchForDuplicateEvents = new SearchForDuplicateEvents(
			$event,
			$app['currentSite'],
			$app['config']->findDuplicateEventsShow,
			$app['config']->findDuplicateEventsThreshhold,
			is_array($app['config']->findDuplicateEventsNoMatchSummary) ? $app['config']->findDuplicateEventsNoMatchSummary : array()
		);

		if ($searchForDuplicateEvents->getPossibleDuplicates()) {
			return json_encode(array(
				'success'=>false,
				'msg'=>'Duplicate event exists'
			));
		}

		// Create event edit metadata model
		$editMetaData = new EventEditMetaDataModel();
		$editMetaData->setUserAccount($defaultUser);

		// Instantiate event repository and update DB
		$eventRepo = new EventRepository();
		$eventRepo->createWithMetaData($event, $app['currentSite'], $editMetaData);

	 	return json_encode(array(
			'success'=>true
		));
	}
	
}

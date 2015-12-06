<?php

namespace org\openacalendar\radicalassembly\siteapi2\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\EventRepository;
use repositories\CountryRepository;
use repositories\VenueRepository;
use repositories\UserAccountRepository;
use models\EventModel;
use models\VenueModel;
use models\SiteModel;
use models\UserAccountModel;
use models\EventEditMetaDataModel;
use models\VenueEditMetaDataModel;
use \SearchForDuplicateEvents;
use org\openacalendar\radicalassembly\SearchForDuplicateVenues;

/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */
class EventController {

	public function createJson (Request $request, Application $app) {

		global $DB;

		$data = $request->request->all();
		$eventData = $data['event_data'];

		if (!$eventData) {
			return json_encode(array(
				'success'=>false,
				'msg'=>'No parameter \'event_data\' was found.',
			));
		};

		// Type conversion
		$eventData['is_physical'] = $eventData['is_physical'] === 'true';
		$eventData['is_virtual'] = $eventData['is_virtual'] === 'true';
		$eventData['is_deleted'] = $eventData['is_deleted'] === 'true';
		$eventData['is_cancelled'] = $eventData['is_cancelled'] === 'true';

		// Create event model and set fields
		$event = new EventModel();
		$event->setSiteId($app['currentSite']->getId());

		// Create country model
		$countryRepo = new CountryRepository();
		$country = $countryRepo->loadByTwoCharCode("GB");

		// Create venue model only if physical event
		if ($eventData['is_physical']) {
			$venue = new VenueModel();
			$venue->setTitle($eventData['venue_name']);
			$venue->setAddress($eventData['venue_address']);
			$venue->setAddressCode($eventData['venue_code']);
			$venue->setCountryId($country->getId());

			if (isset($eventData['venue_lat']) && isset($eventData['venue_lng'])) {
				$venue->setLat($eventData['venue_lat']);
				$venue->setLng($eventData['venue_lng']);
			} else {
				foreach ($app['extensions']->getExtensionsIncludingCore() as $extension) {
					$extension->addDetailsToVenue($venue);
				}
			}

			$searchForDuplicateVenues = new SearchForDuplicateVenues($venue, $app['currentSite']);
			$venueDupes = $searchForDuplicateVenues->getPossibleDuplicates();
			if ($venueDupes && count($venueDupes)>1) {
				return json_encode(array(
					'success'=>false,
					'msg'=>'Multiple duplicate venues detected'
				));
			} else {
				$editMetaData = new VenueEditMetaDataModel();
				$editMetaData->setUserAccount($app['apiUser']);
				$venueRepo = new VenueRepository();

				if ($venueDupes && count($venueDupes)>0) {
					if (! $venueDupes[0]->hasLatLng()) {
						if ($venue->hasLatLng()) {
							$venueDupes[0]->setLat($venue->getLat());
							$venueDupes[0]->setLng($venue->getLng());
						} else {
							foreach ($app['extensions']->getExtensionsIncludingCore() as $extension) {
								$extension->addDetailsToVenue($venueDupes[0]);
							}
						}
					}
					$venueRepo->editWithMetaData($venueDupes[0], $editMetaData);
					$event->setVenueId($venueDupes[0]->getId());
				} else {
					$venueRepo->createWithMetaData($venue, $app['currentSite'], $editMetaData);

					// Weak: only way I know of getting ID of newly created venue
					$venueDupes = $searchForDuplicateVenues->getPossibleDuplicates();
					$event->setVenueId($venueDupes[0]->getId());
				}
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
		$editMetaData->setUserAccount($app['apiUser']);

		// Instantiate event repository and update DB
		$eventRepo = new EventRepository();
		$eventRepo->createWithMetaData($event, $app['currentSite'], $editMetaData);

	 	return json_encode(array(
			'success'=>true
		));
	}

}

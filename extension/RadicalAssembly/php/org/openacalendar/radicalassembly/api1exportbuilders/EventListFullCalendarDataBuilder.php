<?php

namespace org\openacalendar\radicalassembly\api1exportbuilders;

use models\EventModel;
use models\SiteModel;
use models\VenueModel;
use models\CountryModel;
use models\AreaModel;
use repositories\builders\GroupRepositoryBuilder;
use repositories\builders\TagRepositoryBuilder;
use org\openacalendar\curatedlists\models\CuratedListModel;

/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */
class EventListFullCalendarDataBuilder extends BaseEventListBuilder {
    use TraitJSON;

    public function __construct(SiteModel $site = null, $timeZone  = null) {
		parent::__construct($site, $timeZone);
		$this->eventRepositoryBuilder->setAfterNow();
	}

    public function getContents() {
		return json_encode($this->events);
    }

	public function addEvent(EventModel $event, $groups = null, VenueModel $venue = null,
							 AreaModel $area = null, CountryModel $country = null, $eventTags = null, $eventMedias = null) {

        $out = array(
            'id'=>$event->getSlug(),
            'title'=>$event->getSummary(),
        );

        $startTimeZone = clone $event->getStartAt();
        $startTimeZone->setTimeZone(new \DateTimeZone($event->getTimezone()));
        $out['start'] = $startTimeZone->format(\DateTime::ATOM);

        $endTimeZone = clone $event->getEndAt();
        $endTimeZone->setTimeZone(new \DateTimeZone($event->getTimezone()));
        $out['end'] = $startTimeZone->format(\DateTime::ATOM);

        if ($event->getIsPhysical() && $event->getVenue()->hasLatLng()) {
            $out['venueid'] = $event->getVenue()->getSlug();
            $out['lat'] = $event->getVenue()->getLat();
            $out['lng'] = $event->getVenue()->getLng();
        }

		$this->events[] = $out;

	}

}

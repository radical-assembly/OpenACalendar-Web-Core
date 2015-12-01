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

    protected $tags;
    protected $groups;
    protected $startTime;
    protected $endTime;

    public function __construct(SiteModel $site = null, $timeZone  = null) {
		parent::__construct($site, $timeZone);
		$this->eventRepositoryBuilder->setAfterNow();
	}

    public function getContents() {
        $out = array('data'=>$this->events);
		return json_encode($out);
    }

    public function setCuratedList(CuratedListModel $curatedlist) {
        $this->eventRepositoryBuilder->setCuratedList($curatedlist);
    }

    public function setTags($tags) {
        if ($tags) {
        $this->tags = explode(urlencode(","), $tags);
    }
    }

    public function setGroups($groups) {
        if ($groups) {
        $this->groups = explode(urlencode(","), $groups);
    }
    }

    public function setStartTime($time) {
        if ($time) {
        $this->startTime = new \DateTime($time);
    }
    }

    public function setEndTime($time) {
        if ($time) {
        $this->endTime = new \DateTime($time);
    }
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

		$this->events[] = $out;

	}

    public function build() {
        $this->eventRepositoryBuilder->setAfter($this->startTime);
        $this->eventRepositoryBuilder->setBefore($this->endTime);

        if ($this->groups) {
            $grb = new GroupRepositoryBuilder();
            $grb->setSite($this->site);
            $grb->setIncludeDeleted(false);
            $groupModels = array();
            foreach ($this->groups as $group) {
                $grb->setFreeTextsearch($group);
                $groupModels = array_merge($groupModels, $grb->fetchAll());
            }
            $this->eventRepositoryBuilder->setGroup($groupModels);
        }

        if ($this->tags) {
            $trb = new TagRepositoryBuilder();
            $trb->setSite($this->site);
            $trb->setIncludeDeleted(false);
            $tagModels = array();
            foreach ($this->tags as $tag) {
                $trb->setFreeTextsearch($tag);
                $tagModels = array_merge($tagModels, $trb->fetchAll());
            }
            $this->eventRepositoryBuilder->setGroup($tagModels);
        }

        foreach ($this->eventRepositoryBuilder-fetchAll() as $event) {
            $this->addEvent($event);
        }

    }

}

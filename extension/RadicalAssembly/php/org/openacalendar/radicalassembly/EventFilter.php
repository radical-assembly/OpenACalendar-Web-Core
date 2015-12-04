<?php
/**
*
* @package org.openacalendar.radicalassembly
* @link https://oac.radicalassembly.com/ Radical Assembly
* @license GTFO License
* @copyright (c) 2015, Elias Malik
* @author Elias Malik <elias0789@gmail.com>
*/

namespace org\openacalendar\radicalassembly;


use models\EventModel;
use models\SiteModel;
use repositories\builders\GroupRepositoryBuilder;
use repositories\builders\TagRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;


/**
*
*/
class EventFilter
{

    protected $trb;
    protected $grb;
    protected $tags;
    protected $groups;
    protected $startTime;
    protected $endTime;

    public function setTags(array $tags) {
        if ($tags) $this->tags = $tags;
    }

    public function setGroups(array $groups) {
        if ($groups) $this->groups = $groups;
    }

    public function setStartTime(\DateTime $time) {
        if ($time) $this->startTime = $time;
    }

    public function setEndTime(\DateTime $time) {
        if ($time) $this->endTime = $time;
    }

    function __construct(SiteModel $site = null) {
        $this->grb = new GroupRepositoryBuilder();
        $this->trb = new TagRepositoryBuilder();
        if ($site) {
            $this->grb->setSite($site);
            $this->trb->setSite($site);
        }
    }

    public function filterEventRepositoryBuilder(EventRepositoryBuilder $erb) {
        if ($this->startTime) $erb->setAfter($this->startTime);
        if ($this->endTime) $erb->setBefore($this->endTime);
        return $this->filterArray($erb->fetchAll());
    }

    public function filterArray(array $events = array()) {
        return array_filter($events, array($this, 'isEventCompatible'));
    }

    public function isEventCompatible(EventModel $event) {
        $callback = function($o) {return $o->getTitle();};

        if ($this->groups) {
            $this->grb->setEvent($event);
            $groups = array_map($callback, $this->grb->fetchAll());
            $groupsIntersect = count(array_intersect($this->groups, $groups)) > 0;
        } else {
            $groupsIntersect = true;
        }

        if ($this->tags) {
            $this->trb->setTagsForEvent($event);
            $tags = array_map($callback, $this->trb->fetchAll());
            $tagsIntersect = count(array_intersect($this->tags, $tags)) > 0;
        } else {
            $tagsIntersect = true;
        }

        if ($this->startTime) { // Positive time difference => event starts at or after after startTime
            $interval = date_diff($event->getStartAt(), $this->startTime);
            $isAfterStart = $interval->invert == 0;
        } else {
            $isAfterStart = true;
        }

        if ($this->endTime) { // Negative time difference => event finishes before endTime
            $interval = date_diff($event->getEndAt(), $this->endTime);
            $isBeforeEnd = $interval->invert == 1;
        } else {
            $isBeforeEnd = true;
        }

        return ( $groupsIntersect && $tagsIntersect && $isAfterStart && $isBeforeEnd );
    }

}

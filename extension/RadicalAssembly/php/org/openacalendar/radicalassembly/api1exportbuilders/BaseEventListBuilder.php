<?php
namespace org\openacalendar\radicalassembly\api1exportbuilders;

use models\SiteModel;
use models\EventModel;
use models\VenueModel;
use models\AreaModel;
use models\CountryModel;
use repositories\builders\MediaRepositoryBuilder;
use repositories\builders\GroupRepositoryBuilder;
use repositories\builders\TagRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseEventListBuilder  extends BaseBuilder {

	protected $eventRepositoryBuilder;

	protected $events = array();

	protected $includeEventMedias = false;
	protected $includeGroups = false;
	protected $includeTags = false;

	/**
	 * @param boolean $includeEventMedias
	 */
	public function setIncludeEventMedias($includeEventMedias)
	{
		$this->includeEventMedias = $includeEventMedias;
	}

	/**
	 * @return boolean
	 */
	public function getIncludeEventMedias()
	{
		return $this->includeEventMedias;
	}

	/**
	 * @param boolean $includeGroups
	 */
	public function setIncludeGroups($includeGroups)
	{
		$this->includeGroups = $includeGroups;
	}

	/**
	 * @return boolean
	 */
	public function getIncludeGroups()
	{
		return $this->includeGroups;
	}

	/**
	 * @param boolean $includeTags
	 */
	public function setIncludeTags($includeTags)
	{
		$this->includeTags = $includeTags;
	}

	/**
	 * @return boolean
	 */
	public function getIncludeTags()
	{
		return $this->includeTags;
	}


	public function __construct(SiteModel $site = null, $timeZone = null, $title = null) {
		parent::__construct($site, $timeZone, $title);
		global $CONFIG;
		$this->eventRepositoryBuilder = new EventRepositoryBuilder();
		$this->eventRepositoryBuilder->setLimit($CONFIG->api1EventListLimit);
		$this->eventRepositoryBuilder->setIncludeCountryInformation(true);
		$this->eventRepositoryBuilder->setIncludeAreaInformation(true);
		if ($site) $this->eventRepositoryBuilder->setSite($site);
	}

	abstract public function addEvent(EventModel $event, $groups = array(), VenueModel $venue = null,
									  AreaModel $area = null, CountryModel $country = null, $eventTags = array(), $eventMedias = array());


	public function build() {
		foreach($this->eventRepositoryBuilder->fetchAll() as $event) {
			$eventMedias = null;
			$eventGroups = null;
			$eventTags = null;
			if ($this->includeEventMedias) {
				$mrb = new MediaRepositoryBuilder();
				$mrb->setEvent($event);
				$mrb->setIncludeDeleted(false);
				$eventMedias = $mrb->fetchAll();
			}
			if ($this->includeGroups) {
				$grb = new GroupRepositoryBuilder();
				$grb->setEvent($event);
				$grb->setIncludeDeleted(false);
				$eventGroups = $grb->fetchAll();
			}
			if ($this->includeTags) {
				$trb = new TagRepositoryBuilder();
				$trb->setSite($this->site);
				$trb->setIncludeDeleted(false);
				$trb->setTagsForEvent($event);
				$eventTags = $trb->fetchAll();
			}
			$this->addEvent($event, $eventGroups, $event->getVenue(), $event->getArea(), $event->getCountry(), $eventTags, $eventMedias);
		}
	}

	public function getEventRepositoryBuilder() { return $this->eventRepositoryBuilder; }



}

<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

use models\VenueModel;
use models\SiteModel;
use repositories\builders\VenueRepositoryBuilder;

class SearchForDuplicateVenues {

	/** @var VenueModel **/
	protected $venue;

	/** @var Site **/
	protected $site;

	protected $showVenuesCount = 3;

	protected $showVenuesThreshhold = 3;

	protected $wordsNotToMatchInSummary = array();

	function __construct(VenueModel $venue, SiteModel $site, $showVenuesCount=3, $showVenuesThreshhold=3, $wordsNotToMatchInSummary=array()) {
		$this->venue = $venue;
		$this->site = $site;
		$this->$showVenuesCount = $showVenuesCount;
		$this->$showVenuesThreshhold = $showVenuesThreshhold;
		$this->wordsNotToMatchInSummary = $wordsNotToMatchInSummary;
	}

	protected $notTheseSlugs = array();

	function setNotDuplicateSlugs($in) {
		foreach($in as $slug) {
			if ($slug) {
				$this->notTheseSlugs[] = $slug;
			}
		}
	}

	function getPossibleDuplicates() {

		// Get venues
		$venueRepoBuilder = new VenueRepositoryBuilder();
		$venueRepoBuilder->setSite($this->site);
		$venueRepoBuilder->setCountry($this->venue->getCountryId());
		$venueRepoBuilder->setIncludeDeleted(true);

		$venues = $venueRepoBuilder->fetchAll();

		$venuesWithScore = array();
		foreach($venues as $venue) {
			if (!in_array($venue->getSlug(), $this->notTheseSlugs)) {
				$venuesWithScore[] = array(
					'venue'=>$venue,
					'score'=>$this->getScoreForConsideredVenue($venue),
				);
			}
		}

		## sort
		$sortFunc = function($a,$b){
			if ($a['score'] == $b['score']) { return 0; }
			elseif ($a['score'] > $b['score']) { return 1;}
			elseif ($a['score'] < $b['score']) { return -1; };
		};
		usort($venuesWithScore, $sortFunc);

		## Results
		$results = array();
		foreach($venuesWithScore as $venueWithScore) {
			if (count($results) < $this->showVenuesCount && $venueWithScore['score'] >= $this->showVenuesThreshhold) {
				$results[] = $venueWithScore['venue'];
			}
		}

		return $results;

	}


	function getScoreForConsideredEvent(VenueModel $venue) {

		if ($this->venue-getIsDuplicateOfId()) {
			return PHP_INT_MAX;
		}

		$score = 0;

		if ($this->venue->hasLatLng() && $venue->hasLatLng()) {
			$compFunc = function($a,$b) {
				if (abs($b)< 1e-10) return INF;
				return abs(1 - float($a)/float($b));
			}
			if ($compFunc($this->venue->getLat(), $venue->getLat()) && $compFunc($this->venue->getLong(), $venue->getLong())) {
				$score+=2;
			}
		}

		if ($this->venue->getTitle() && $this->venue->getTitle() == $venue->getTitle()) {
			$score++;
		}
		if ($this->venue->getAddress() && $this->venue->getAddress() == $venue->getAddress()) {
			$score++;
		}
		if ($this->venue->getAddressCode() && $this->venue->getAddressCode() == $venue->getAddressCode()) {
			$score++;
		}
		if ($this->venue->getDescription()) {
			if ($this->venue->getDescription() == $venue->getDescription()) {
				$score++;
			} else {
				$bits1 = explode(" ", strtolower($this->venue->getDescription()));
				$bits2 = explode(" ", strtolower($venue->getDescription()));
				$flag = false;
				foreach($bits1 as $bit) {
					if ($bit && in_array($bit, $bits2) && !in_array($bit, $this->wordsNotToMatchInSummary)) {
						$flag = true;
					}
				}
				if ($flag) $score++;
			}
		}
		if ($this->event->getVenueId() && $this->event->getVenueId() == $event->getVenueId()) {
			$score++;
		}
		if ($this->venue->getAreaId() && $this->venue->getAreaId() == $venue->getAreaId()) {
			$score++;
		} elseif ($this->venue->getAreaId() && $venue->getArea() && $this->venue->getAreaId() == $venue->getArea()->getId()) {
			$score++;
		}


		return $score;
	}

}

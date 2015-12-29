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

use models\VenueModel;
use models\SiteModel;
use repositories\builders\VenueRepositoryBuilder;
use repositories\CountryRepository;

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
		$venueRepoBuilder->setIncludeDeleted(true);

		$countryRepo = new CountryRepository();
		$venueRepoBuilder->setCountry($countryRepo->loadById($this->venue->getCountryId()));

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


	function getScoreForConsideredVenue(VenueModel $venue) {

		if ($this->venue->getIsDuplicateOfId()) {
			return PHP_INT_MAX;
		}

		$score = 0;

		if ($this->venue->hasLatLng() && $venue->hasLatLng()) {
			$lat1 = (float)$this->venue->getLat();
			$lat2 = (float)$venue->getLat();
			$lng1 = (float)$this->venue->getLng();
			$lng2 = (float)$venue->getLng();
			$isEqualLat = abs($lat1-$lat2) < 1e-10 * max(abs($lat1), abs($lat2));
			$isEqualLng = abs($lng1-$lng2) < 1e-10 * max(abs($lng1), abs($lng2));
			if ($isEqualLat && $isEqualLng) {
				$score += 2;
			}
		}

		if ($this->venue->getTitle() && strtolower($this->venue->getTitle()) == strtolower($venue->getTitle())) {
			$score++;
		}
		if ($this->venue->getAddress() && strtolower($this->venue->getAddress()) == strtolower($venue->getAddress())) {
			$score++;
		}
		if ($this->venue->getAddressCode() && strtolower($this->venue->getAddressCode()) == strtolower($venue->getAddressCode())) {
			$score++;
		}
		if ($this->venue->getDescription()) {
			if (strtolower($this->venue->getDescription()) == strtolower($venue->getDescription())) {
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
		if ($this->venue->getAreaId() && $this->venue->getAreaId() == $venue->getAreaId()) {
			$score++;
		} elseif ($this->venue->getAreaId() && $venue->getArea() && $this->venue->getAreaId() == $venue->getArea()->getId()) {
			$score++;
		}


		return $score;
	}

}
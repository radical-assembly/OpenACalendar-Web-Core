<?php


namespace org\openacalendar\radicalassembly\sitefeatures;

/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */
class RadicalAssemblyFeature extends \BaseSiteFeature {

	function __construct()
	{
		$this->is_on = false;
	}

	public function getExtensionId()
	{
		return 'org.openacalendar.radicalassembly';
	}

	public function getFeatureId()
	{
		return 'RadicalAssembly';
	}


	public function getTitle() {
		return 'Radical Assembly';
	}

	public function getDescription() {
		return 'Extra functionality required for Radical Assembly website.';
	}

}

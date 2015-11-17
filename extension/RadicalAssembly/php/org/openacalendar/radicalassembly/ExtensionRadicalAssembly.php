<?php

namespace org\openacalendar\radicalassembly;

/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */

class ExtensionRadicalAssembly extends \BaseExtension {

	public function getId() {
		return 'org.openacalendar.radicalassembly';
	}

	public function getTitle() {
		return "Radical Assembly Extension";
	}

	public function getDescription() {
		return "Extra functionality for Radical Assembly site";
	}

	public function getSiteFeatures(\models\SiteModel $siteModel = null) {
		return array(
			new \org\openacalendar\radicalassembly\sitefeatures\RadicalAssemblyFeature(),
		);
	}
}

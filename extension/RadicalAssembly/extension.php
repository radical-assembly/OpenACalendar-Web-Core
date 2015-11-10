<?php

/**
 *
 * @package org.openacalendar.radicalassembly
 * @link https://oac.radicalassembly.com/ Radical Assembly
 * @license GTFO License
 * @copyright (c) 2015, Elias Malik
 * @author Elias Malik <elias0789@gmail.com>
 */


$app['extensions']->addExtension(__DIR__, new org\openacalendar\radicalassembly\ExtensionRadicalAssembly($app));

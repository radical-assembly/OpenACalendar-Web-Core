<?php

namespace repositories\builders;

use BaseExtension;
use Facebook\FacebookRequest;
use Facebook\FacebookSession;
use Facebook\GraphObject;
use models\CountryModel;
use models\SiteModel;
use models\GroupModel;
use models\ImportURLModel;
use repositories\ImportURLRepository;
use repositories\UserAccountRepository;
use repositories\UserGroupRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLRepositoryBuilder extends BaseRepositoryBuilder
{


    /** @var SiteModel * */
    protected $site;

    public function setSite(SiteModel $site)
    {
        $this->site = $site;
    }

    /** @var GroupModel * */
    protected $group;

    public function setGroup(GroupModel $group)
    {
        $this->group = $group;
        return $this;
    }


    protected function build()
    {

        if ($this->site) {
            $this->where[] = " import_url_information.site_id = :site_id ";
            $this->params['site_id'] = $this->site->getId();
        }

        if ($this->group) {
            $this->where[] = " import_url_information.group_id = :group_id ";
            $this->params['group_id'] = $this->group->getId();
        }
    }


    protected function buildStat()
    {
        global $DB;


        $sql = "SELECT import_url_information.* FROM import_url_information " .
            ($this->where ? " WHERE " . implode(" AND ", $this->where) : '') .
            " ORDER BY import_url_information.title ASC " . ($this->limit > 0 ? " LIMIT " . $this->limit : "");

        $this->stat = $DB->prepare($sql);
        $this->stat->execute($this->params);

    }


    public function fetchAll()
    {

        $this->buildStart();
        $this->build();
        $this->buildStat();


        $results = array();
        while ($data = $this->stat->fetch()) {
            $importURL = new ImportURLModel();
            $importURL->setFromDataBaseRow($data);
            $results[] = $importURL;
        }
        return $results;

    }

    public function fetchAndAddFacebookEvents()
    {
        print 'Fetching Facebook events';
        global $app;

        $siteRepo = new SiteRepositoryBuilder();
        /** @var SiteModel $currentSite */
        $currentSite = $siteRepo->fetchAll()[0];

        if (in_array('Europe/London',$currentSite->getCachedTimezonesAsList())) {
            $timezone = 'Europe/London';
        } else {
            $timezone  = $currentSite->getCachedTimezonesAsList()[0];
        }

        $groupId = $this->getDefaultGroup($currentSite);
        $countryId = $this->getDefaultCountry($currentSite,$timezone);
        $adminUser = $this->getAdminUser();

        if(!$groupId || !$countryId) {
            error_log('Didn\'t find group ('.$groupId.') or country ('.$countryId.')');
        }

        /** @var BaseExtension $extension */
        $extension = $app['extensions']->getExtensionById('org.openacalendar.facebook');
        $userToken = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('user_token'));
        $appSecret = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_secret'));
        $appID = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_id'));

        FacebookSession::setDefaultApplication($appID, $appSecret);
        $session = new FacebookSession($userToken);

        $url = '/me/events?since=' . time();
        $request = new FacebookRequest($session, 'GET', $url);
        $response = $request->execute();
        $graphObject = $response->getGraphObject();
        $graphObjects = [$graphObject];

        print 'Found '.count($graphObject->getPropertyAsArray('data')). ' events';

        while($graphObject->getProperty('paging') && $graphObject->getProperty('paging')->getProperty('next')) {
            $after = $graphObject->getProperty('paging')->getProperty('cursors')->getProperty('after');
            $url = '/me/events?after='.$after;
            $request = new FacebookRequest($session, 'GET', $url);
            $response = $request->execute();
            $graphObject = $response->getGraphObject();
            $graphObjects[] = $graphObject;
            print 'Found '.count($graphObject->getPropertyAsArray('data')). ' more events';
        }

        /** @var GraphObject $graphObject */
        foreach($graphObjects as $graphObject) {
            $data = $graphObject->getPropertyAsArray('data');
            foreach($data as $event) {
                $title = $event->getProperty('name');
                $url = 'https://facebook.com/event/'.$event->getProperty('id');

                print 'Adding '.$url;

                $importurl = new ImportURLModel();
                // we must setSiteId() here so loadClashForImportUrl() works
                $importurl->setSiteId($currentSite->getId());
                $importurl->setGroupId($groupId);
                $importurl->setCountryId($countryId);
                $importurl->setUrl($url);
                $importurl->setTitle($title);

                $importURLRepository = new ImportURLRepository();

                $clash = $importURLRepository->loadClashForImportUrl($importurl);
                if ($clash) {
                    $importurl->setIsEnabled(false);
                } else {
                    $importurl->setIsEnabled(true);
                }

                $importURLRepository->create($importurl, $currentSite, $adminUser);
            }
        }

    }

    private function getDefaultCountry($currentSite,$timeZoneName) {
        $crb = new CountryRepositoryBuilder();
        $crb->setSiteIn($currentSite);
        $countries = array();
        $defaultCountry = null;
        /** @var CountryModel $country */
        foreach($crb->fetchAll() as $country) {
            $countries[$country->getId()] = $country->getTitle();
            if ($defaultCountry == null && in_array($timeZoneName, $country->getTimezonesAsList())) {
                return $country->getId();
            }
        }
        return null;
    }

    private function getDefaultGroup($currentSite) {
        $groupRepositoryBuilder = new GroupRepositoryBuilder();
        $groupRepositoryBuilder->setSite($currentSite);
        $groupRepositoryBuilder->setIncludeDeleted(false);
        $groupRepositoryBuilder->setIncludeMediasSlugs(true);
        $groupRepositoryBuilder->setFreeTextsearch('Facebook');
        /** @var GroupModel[] $groups */
        $groups = $groupRepositoryBuilder->fetchAll();
        if($groups && count($groups)) {
            return $groups[0]->getId();
        }
        return null;
    }

    private function getAdminUser() {

        $repo = new UserAccountRepository();
        return $repo->loadByID(1);

    }
}


<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\DbHelper;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\AssetManager;
use Piwik\Date;
use Piwik\Common;
use Piwik\Db;
use Piwik\FrontController;
use Piwik\Option;
use Piwik\Url;
use Piwik\WidgetsList;

/**
 * Fixture for UI tests.
 */
class UITestFixture extends OmniFixture
{
    public function setUp()
    {
        parent::setUp();

        // make sure site has an early enough creation date (for period selector tests)
        Db::get()->update(Common::prefixTable("site"),
            array('ts_created' => '2011-01-01'),
            "idsite = 1"
        );

        $this->addOverlayVisits();
        $this->addNewSitesForSiteSelector();

        DbHelper::createAnonymousUser();
        UsersManagerAPI::getInstance()->setSuperUserAccess('superUserLogin', true);

        Option::set("Tests.forcedNowTimestamp", $this->now->getTimestamp());

        // launch archiving so tests don't run out of time
        $date = Date::factory($this->dateTime)->toString();
        VisitsSummaryAPI::getInstance()->get($this->idSite, 'year', $date);
        VisitsSummaryAPI::getInstance()->get($this->idSite, 'year', $date, urlencode($this->segment));
    }

    public function performSetUp($testCase, $setupEnvironmentOnly = false)
    {
        parent::performSetUp($testCase, $setupEnvironmentOnly);

        $this->setupDashboards();

        AssetManager::getInstance()->removeMergedAssets();

        $visitorIdDeterministic = bin2hex(Db::fetchOne(
            "SELECT idvisitor FROM " . Common::prefixTable('log_visit')
            . " WHERE idsite = 2 AND location_latitude IS NOT NULL LIMIT 1"));
        $this->testEnvironment->forcedIdVisitor = $visitorIdDeterministic;

        $this->testEnvironment->overlayUrl = $this->getLocalTestSiteUrl();
        echo "OVERLAY URL IN SETUP: " .$this->testEnvironment->overlayUrl . "\n";
        $this->createOverlayTestSite();

        $forcedNowTimestamp = Option::get("Tests.forcedNowTimestamp");
        if ($forcedNowTimestamp == false) {
            throw Exception("Incorrect fixture setup, Tests.forcedNowTimestamp option does not exist! Run the setup again.");
        }

        $this->testEnvironment->forcedNowTimestamp = $forcedNowTimestamp;
        $this->testEnvironment->save();
    }

    private function addOverlayVisits()
    {
        $baseUrl = $this->getLocalTestSiteUrl();

        $visitProfiles = array(
            array('', 'page-1.html', 'page-2.html', 'page-3.html', ''),
            array('', 'page-3.html', 'page-4.html'),
            array('', 'page-4.html'),
            array('', 'page-1.html', 'page-3.html', 'page-4.html'),
            array('', 'page-4.html', 'page-1.html'),
            array('', 'page-1.html', ''),
            array('page-4.html', ''),
            array('', 'page-2.html', 'page-3.html'),
            array('', 'page-1.html', 'page-2.html'),
            array('', 'page-6.html', 'page-5.html', 'page-4.html', 'page-3.html', 'page-2.html', 'page-1.html', ''),
            array('', 'page-5.html', 'page-3.html', 'page-1.html'),
            array('', 'page-1.html', 'page-2.html', 'page-3.html'),
            array('', 'page-4.html', 'page-3.html'),
            array('', 'page-1.html', ''),
            array('page-6.html', 'page-3.html', ''),
        );

        $date = Date::factory('yesterday');
        $t = self::getTracker($idSite = 3, $dateTime = $date->getDatetime(), $defaultInit = true);
        $t->enableBulkTracking();

        foreach ($visitProfiles as $visitCount => $visit) {
            $t->setNewVisitorId();
            $t->setIp("123.234.23.$visitCount");

            foreach ($visit as $idx => $action) {
                $t->setForceVisitDateTime($date->addHour($visitCount)->addHour(0.01 * $idx)->getDatetime());

                $url = $baseUrl . $action;
                $t->setUrl($url);

                if ($idx != 0) {
                    $referrerUrl = $baseUrl . $visit[$idx - 1];
                    $t->setUrlReferrer($referrerUrl);
                }

                self::assertTrue($t->doTrackPageView("page title of $action"));
            }
        }

        self::checkBulkTrackingResponse($t->doBulkTrack());
    }

    private function createOverlayTestSite()
    {
        $realDir = PIWIK_INCLUDE_PATH . "/tests/resources/overlay-test-site-real";
        if (is_dir($realDir)) {
            return;
        }

        $files = array('index.html', 'page-1.html', 'page-2.html', 'page-3.html', 'page-4.html', 'page-5.html', 'page-6.html');

        // copy templates to overlay-test-site-real
        mkdir($realDir);
        foreach ($files as $file) {
            copy(PIWIK_INCLUDE_PATH . "/tests/resources/overlay-test-site/$file",
                 PIWIK_INCLUDE_PATH . "/tests/resources/overlay-test-site-real/$file");
        }

        // replace URL in copied files
        $url = self::getRootUrl() . 'tests/PHPUnit/proxy/';
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $url = substr($url, strlen($scheme) + 3);

        foreach ($files as $file) {
            $path = PIWIK_INCLUDE_PATH . "/tests/resources/overlay-test-site-real/$file";

            $contents = file_get_contents($path);
            $contents = str_replace("%trackerBaseUrl%", $url, $contents);
            file_put_contents($path, $contents);
        }
    }

    private function getLocalTestSiteUrl()
    {
        return self::getRootUrl() . "tests/resources/overlay-test-site-real/";
    }

    private function addNewSitesForSiteSelector()
    {
        for ($i = 0; $i != 8; ++$i) {
            self::createWebsite("2011-01-01 00:00:00", $ecommerce = 1, $siteName = "Site #$i", $siteUrl = "http://site$i.com");
        }
    }

    /** Creates two dashboards that split the widgets up into different groups. */
    public function setupDashboards()
    {
        $dashboardColumnCount = 3;
        $dashboardCount = 4;
        
        $layout = array();
        for ($j = 0; $j != $dashboardColumnCount; ++$j) {
            $layout[] = array();
        }

        $dashboards = array();
        for ($i = 0; $i != $dashboardCount; ++$i) {
            $dashboards[] = $layout;
        }
        
        $oldGet = $_GET;
        $_GET['idSite'] = 1;
        
        // collect widgets & sort them so widget order is not important
        $allWidgets = array();
        foreach (WidgetsList::get() as $category => $widgets) {
            $allWidgets = array_merge($allWidgets, $widgets);
        }
        usort($allWidgets, function ($lhs, $rhs) {
            return strcmp($lhs['uniqueId'], $rhs['uniqueId']);
        });

        // group widgets so they will be spread out across 3 dashboards
        $groupedWidgets = array();
        $dashboard = 0;
        foreach ($allWidgets as $widget) {
            if ($widget['uniqueId'] == 'widgetSEOgetRank'
                || $widget['uniqueId'] == 'widgetReferrersgetKeywordsForPage'
                || $widget['uniqueId'] == 'widgetLivegetVisitorProfilePopup'
                || $widget['uniqueId'] == 'widgetActionsgetPageTitles'
                || strpos($widget['uniqueId'], 'widgetExample') === 0
            ) {
                continue;
            }
            
            $dashboard = ($dashboard + 1) % $dashboardCount;

            $widgetEntry = array(
                'uniqueId' => $widget['uniqueId'],
                'parameters' => $widget['parameters']
            );
            
            // dashboard images must have height of less than 4000px to avoid odd discoloration of last line of image
            $widgetEntry['parameters']['filter_limit'] = 5;

            $groupedWidgets[$dashboard][] = $widgetEntry;
        }
        
        // distribute widgets in each dashboard
        $column = 0;
        foreach ($groupedWidgets as $dashboardIndex => $dashboardWidgets) {
            foreach ($dashboardWidgets as $widget) {
                $column = ($column + 1) % $dashboardColumnCount;
                
                $dashboards[$dashboardIndex][$column][] = $widget;
            }
        }

        foreach ($dashboards as $id => $layout) {
            if ($id == 0) {
                $_GET['name'] = self::makeXssContent('dashboard name' . $id);
            } else {
                $_GET['name'] = 'dashboard name' . $id;
            }
            $_GET['layout'] = Common::json_encode($layout);
            $_GET['idDashboard'] = $id + 1;
            FrontController::getInstance()->fetchDispatch('Dashboard', 'saveLayout');
        }

        // create empty dashboard
        $dashboard = array(
            array(
                array(
                    'uniqueId' => "widgetVisitsSummarygetEvolutionGraphcolumnsArray",
                    'parameters' => array(
                        'module' => 'VisitsSummary',
                        'action' => 'getEvolutionGraph',
                        'columns' => 'nb_visits'
                    )
                )
            ),
            array(),
            array()
        );

        $_GET['name'] = 'D4';
        $_GET['layout'] = Common::json_encode($dashboard);
        $_GET['idDashboard'] = 5;
        $_GET['idSite'] = 2;
        FrontController::getInstance()->fetchDispatch('Dashboard', 'saveLayout');

        $_GET = $oldGet;
    }
}
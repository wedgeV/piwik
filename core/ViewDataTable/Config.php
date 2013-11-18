<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\ViewDataTable;
use Piwik\API\Request as ApiRequest;
use Piwik\Metrics;
use Piwik\Plugins\API\API;

/**
 * Contains base display properties for ViewDataTables. Manipulating these properties
 * in a ViewDataTable instance will change how its report will be displayed.
 * 
 * <a name="client-side-properties-desc"></a>
 * **Client Side Properties**
 * 
 * Client side properties are properties that should be passed on to the browser so
 * client side JavaScript can use them. Only affects ViewDataTables that output HTML.
 * 
 * **Overridable Properties**
 * 
 * Overridable properties are properties that can be set via the query string.
 * If a request has a query parameter that matches an overridable property, the property
 * will be set to the query parameter value.
 * 
 * **Defining new display properties**
 * 
 * If you are creating your own visualization and want to add new display properties for
 * it, extend this class and add your properties as fields.
 * 
 * Properties are marked as client side properties by calling the
 * [addPropertiesThatShouldBeAvailableClientSide](#addPropertiesThatShouldBeAvailableClientSide) method.
 * 
 * Properties are marked as overridable by calling the
 * [addPropertiesThatCanBeOverwrittenByQueryParams](#addPropertiesThatCanBeOverwrittenByQueryParams) method.
 * 
 * ### Example
 * 
 * **Defining new display properties**
 * 
 *     class MyCustomVizConfig extends Config
 *     {
 *         /**
 *          * My custom property. It is overridable.
 *          *\/
 *         public $my_custom_property = false;
 *
 *         /**
 *          * Another custom property. It is available client side.
 *          *\/
 *         public $another_custom_property = true;
 * 
 *         public function __construct()
 *         {
 *             parent::__construct();
 * 
 *             $this->addPropertiesThatShouldBeAvailableClientSide(array('another_custom_property'));
 *             $this->addPropertiesThatCanBeOverwrittenByQueryParams(array('my_custom_property'));
 *         }
 *     }
 *
 * @package Piwik
 * @subpackage Piwik_Visualization
 * @api
 */
class Config
{
    /**
     * The list of ViewDataTable properties that are 'Client Side Properties'.
     */
    public $clientSideProperties = array();

    /**
     * The list of ViewDataTable properties that can be overriden by query parameters.
     */
    public $overridableProperties = array(
        'show_footer',
        'show_footer_icons',
        'show_all_views_icons',
        'export_limit'
    );

    /**
     * Controls what footer icons are displayed on the bottom left of the DataTable view.
     * The value of this property must be an array of footer icon groups. Footer icon groups
     * have set of properties, including an array of arrays describing footer icons. For
     * example:
     *
     *     array(
     *         array( // footer icon group 1
     *             'class' => 'footerIconGroup1CssClass',
     *             'buttons' => array(
     *                 'id' => 'myid',
     *                 'title' => 'My Tooltip',
     *                 'icon' => 'path/to/my/icon.png'
     *             )
     *         ),
     *         array( // footer icon group 2
     *             'class' => 'footerIconGroup2CssClass',
     *             'buttons' => array(...)
     *         )
     *     )
     *
     * By default, when a user clicks on a footer icon, Piwik will assume the 'id' is
     * a viewDataTable ID and try to reload the DataTable w/ the new viewDataTable. You
     * can provide your own footer icon behavior by adding an appropriate handler via
     * DataTable.registerFooterIconHandler in your JavaScript code.
     *
     * The default value of this property is not set here and will show the 'Normal Table'
     * icon, the 'All Columns' icon, the 'Goals Columns' icon and all jqPlot graph columns,
     * unless other properties tell the view to exclude them.
     */
    public $footer_icons = false;

    /**
     * Array property mapping DataTable column names with their internationalized names.
     *
     * The default value for this property is set elsewhere. It will contain translations
     * of common metrics.
     */
    public $translations = array();

    /**
     * Controls whether the entire view footer is shown.
     */
    public $show_footer = true;

    /**
     * Controls whether the row that contains all footer icons & the limit selector is shown.
     */
    public $show_footer_icons = true;

    /**
     * Array property that determines which columns will be shown. Columns not in this array
     * should not appear in ViewDataTable visualizations.
     *
     * Example: `array('label', 'nb_visits', 'nb_uniq_visitors')`
     *
     * If this value is empty it will be defaulted to `array('label', 'nb_visits')` or
     * `array('label', 'nb_uniq_visitors')` if the report contains a nb_uniq_visitors column
     * after data is loaded.
     */
    public $columns_to_display = array();

    /**
     * Controls whether graph and non core viewDataTable footer icons are shown or not.
     */
    public $show_all_views_icons = true;

    /**
     * Contains the documentation for a report.
     */
    public $documentation = false;

    /**
     * Array property that stores documentation for individual metrics.
     *
     * E.g. `array('nb_visits' => '...', ...)`
     *
     * By default this is set to values retrieved from report metadata (via API.getReportMetadata API method).
     */
    public $metrics_documentation = array();

    /**
     * The URL to the report the view is displaying. Modifying this means clicking back to this report
     * from a Related Report will go to a different URL. Can be used to load an entire page instead
     * of a single report when going back to the original report.
     *
     * The URL used to request the report without generic filters.
     */
    public $self_url = '';

    /**
     * Contains the controller action to call when requesting subtables of the current report.
     *
     * By default, this is set to the controller action used to request the report.
     */
    public $subtable_controller_action = '';

    /**
     * The filter_limit query parameter value to use in export links.
     *
     * Defaulted to the value of the `[General] API_datatable_default_limit` INI config option.
     */
    public $export_limit = '';

    /**
     * TODO
     */
    public $report_id = '';

    /**
     * TODO
     */
    public $controllerName;

    /**
     * TODO
     */
    public $controllerAction;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->export_limit = \Piwik\Config::getInstance()->General['API_datatable_default_limit'];
        $this->translations = array_merge(
            Metrics::getDefaultMetrics(),
            Metrics::getDefaultProcessedMetrics()
        );
    }

    /**
     * TODO
     */
    public function setController($controllerName, $controllerAction)
    {
        $this->controllerName   = $controllerName;
        $this->controllerAction = $controllerAction;
        $this->report_id        = $controllerName . '.' . $controllerAction;

        $this->loadDocumentation();
    }

    /** Load documentation from the API */
    private function loadDocumentation()
    {
        $this->metrics_documentation = array();

        $report = API::getInstance()->getMetadata(0, $this->controllerName, $this->controllerAction);
        $report = $report[0];

        if (isset($report['metricsDocumentation'])) {
            $this->metrics_documentation = $report['metricsDocumentation'];
        }

        if (isset($report['documentation'])) {
            $this->documentation = $report['documentation'];
        }
    }

    /**
     * Marks display properties as client side properties. [Read this](#client-side-properties-desc)
     * to learn more.
     * 
     * @param array $propertyNames List of property names, eg, `array('show_limit_control', 'show_goals')`.
     */
    public function addPropertiesThatShouldBeAvailableClientSide(array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->clientSideProperties[] = $propertyName;
        }
    }

    /**
     * Marks display properties as overridable. [Read this](#overridable-properties-desc) to
     * learn more.
     * 
     * @param array $propertyNames List of property names, eg, `array('show_limit_control', 'show_goals')`.
     */
    public function addPropertiesThatCanBeOverwrittenByQueryParams(array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->overridableProperties[] = $propertyName;
        }
    }

    /**
     * Returns array of all property values in this config object. Property values are mapped
     * by name.
     * 
     * @return array eg, `array('show_limit_control' => 0, 'show_goals' => 1, ...)`
     */
    public function getProperties()
    {
        return get_object_vars($this);
    }

    /**
     * @ignore
     */
    public function setDefaultColumnsToDisplay($columns, $hasNbVisits, $hasNbUniqVisitors)
    {
        if ($hasNbVisits || $hasNbUniqVisitors) {
            $columnsToDisplay = array('label');

            // if unique visitors data is available, show it, otherwise just visits
            if ($hasNbUniqVisitors) {
                $columnsToDisplay[] = 'nb_uniq_visitors';
            } else {
                $columnsToDisplay[] = 'nb_visits';
            }
        } else {
            $columnsToDisplay = $columns;
        }

        $this->columns_to_display = array_filter($columnsToDisplay);
    }

    /**
     * Associates internationalized text with a metric. Overwrites existing mappings.
     * 
     * See [translations](#translations).
     * 
     * @param string $columnName The name of a column in the report data, eg, `'nb_visits'` or
     *                           `'goal_1_nb_conversions'`.
     * @param string $translation The internationalized text, eg, `'Visits'` or `"Conversions for 'My Goal'"`.
     */
    public function addTranslation($columnName, $translation)
    {
        $this->translations[$columnName] = $translation;
    }

    /**
     * Associates multiple translations with metrics.
     * 
     * See [translations](#translations) and [addTranslation](#addTranslation).
     * 
     * @param array $translations An array of column name => text mappings, eg,
     *                            ```
     *                            array(
     *                                'nb_visits' => 'Visits',
     *                                'goal_1_nb_conversions' => "Conversions for 'My Goal'"
     *                            )
     *                            ```
     */
    public function addTranslations($translations)
    {
        foreach ($translations as $key => $translation) {
            $this->addTranslation($key, $translation);
        }
    }
}
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

namespace Piwik\ViewDataTable\Visualization;
use Piwik\API\Request as ApiRequest;

/**
 * Contains base display properties for ViewDataTables. Manipulating these properties
 * in a ViewDataTable instance will change how its report will be displayed.
 * 
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
class Config extends \Piwik\ViewDataTable\Config
{
    /**
     * Controls whether the buttons and UI controls around the visualization or shown or
     * if just the visualization alone is shown.
     */
    public $show_visualization_only = false;

    /**
     * Controls whether the goals footer icon is shown.
     */
    public $show_goals = false;

    /**
     * Controls whether the 'Exclude Low Population' option (visible in the popup that displays after
     * clicking the 'cog' icon) is shown.
     */
    public $show_exclude_low_population = true;

    /**
     * Whether to show the 'Flatten' option (visible in the popup that displays after clicking the
     * 'cog' icon).
     */
    public $show_flatten_table = true;

    /**
     * Controls whether the footer icon that allows users to switch to the 'normal' DataTable view
     * is shown.
     */
    public $show_table = true;

    /**
     * Controls whether the 'All Columns' footer icon is shown.
     */
    public $show_table_all_columns = true;

    /**
     * Controls whether to display a tiny upside-down caret over the currently active view icon.
     */
    public $show_active_view_icon = true;

    /**
     * Related reports are listed below a datatable view. When clicked, the original report will
     * change to the clicked report and the list will change so the original report can be
     * navigated back to.
     */
    public $related_reports = array();

    /**
     * The report title. Used with related reports so report headings can be changed when switching
     * reports.
     *
     * This must be set if related reports are added.
     */
    public $title = '';

    /**
     * Controls whether a report's related reports are listed with the view or not.
     */
    public $show_related_reports = true;

    /**
     * Array property containing custom data to be saved in JSON in the data-params HTML attribute
     * of a data table div. This data can be used by JavaScript DataTable classes.
     *
     * e.g. array('typeReferrer' => ...)
     */
    public $custom_parameters = array();

    /**
     * Controls whether the limit dropdown (which allows users to change the number of data shown)
     * is always shown or not.
     *
     * Normally shown only if pagination is enabled.
     */
    public $show_limit_control = true;

    /**
     * Controls whether the search box under the datatable is shown.
     */
    public $show_search = true;

    /**
     * Controls whether the user can sort DataTables by clicking on table column headings.
     */
    public $enable_sort = true;

    /**
     * Controls whether the footer icon that allows users to view data as a bar chart is shown.
     */
    public $show_bar_chart = true;

    /**
     * Controls whether the footer icon that allows users to view data as a pie chart is shown.
     */
    public $show_pie_chart = true;

    /**
     * Controls whether the footer icon that allows users to view data as a tag cloud is shown.
     */
    public $show_tag_cloud = true;

    /**
     * Controls whether the user is allowed to export data as an RSS feed or not.
     */
    public $show_export_as_rss_feed = true;

    /**
     * Controls whether the 'Ecoommerce Orders'/'Abandoned Cart' footer icons are shown or not.
     */
    public $show_ecommerce = false;

    /**
     * Stores an HTML message (if any) to display under the datatable view.
     */
    public $show_footer_message = false;

    /**
     * Row metadata name that contains the tooltip for the specific row.
     */
    public $tooltip_metadata_name = false;

    /**
     * CSS class to use in the output HTML div. This is added in addition to the visualization CSS
     * class.
     */
    public $datatable_css_class = false;

    /**
     * The JavaScript class to instantiate after the result HTML is obtained. This class handles all
     * interactive behavior for the DataTable view.
     */
    public $datatable_js_type = 'DataTable';

    /**
     * If true, searching through the DataTable will search through all subtables.
     */
    public $search_recursive = false;

    /**
     * The unit of the displayed column. Valid if only one non-label column is displayed.
     */
    public $y_axis_unit = false;

    /**
     * Controls whether to show the 'Export as Image' footer icon.
     */
    public $show_export_as_image_icon = false;

    /**
     * Array of DataTable filters that should be run before displaying a DataTable. Elements
     * of this array can either be a closure or an array with at most three elements, including:
     * - the filter name (or a closure)
     * - an array of filter parameters
     * - a boolean indicating if the filter is a priority filter or not
     *
     * Priority filters are run before queued filters. These filters should be filters that
     * add/delete rows.
     *
     * If a closure is used, the view is appended as a parameter.
     */
    public $filters = array();

    /**
     * Controls whether the 'prev'/'next' links are shown in the DataTable footer. These links
     * change the 'filter_offset' query parameter, thus allowing pagination.
     */
    public $show_pagination_control = true;

    /**
     * Controls whether offset information (ie, '5-10 of 20') is shown under the datatable.
     */
    public $show_offset_information = true;

    /**
     * Controls whether annotations are shown or not.
     */
    public $hide_annotations_view = true;

    /**
     * TODO
     */
    public $report_last_updated_message = false;

    /**
     * TODO
     */
    public $metadata  = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addPropertiesThatCanBeOverwrittenByQueryParams(array(
            'show_goals',
            'show_exclude_low_population',
            'show_flatten_table',
            'show_table',
            'show_table_all_columns',
            'show_active_view_icon',
            'show_related_reports',
            'show_limit_control',
            'show_search',
            'enable_sort',
            'show_bar_chart',
            'show_pie_chart',
            'show_tag_cloud',
            'show_export_as_rss_feed',
            'show_ecommerce',
            'search_recursive',
            'show_export_as_image_icon',
            'show_pagination_control',
            'show_offset_information',
            'hide_annotations_view'
        ));

        $this->addPropertiesThatShouldBeAvailableClientSide(array(
            'show_limit_control'
        ));
    }

    /**
     * TODO
     */
    public function getFiltersToRun()
    {
        $priorityFilters     = array();
        $presentationFilters = array();

        foreach ($this->filters as $filterInfo) {
            if ($filterInfo instanceof \Closure) {
                $nameOrClosure = $filterInfo;
                $parameters    = array();
                $priority      = false;
            } else {
                @list($nameOrClosure, $parameters, $priority) = $filterInfo;
            }

            if ($priority) {
                $priorityFilters[] = array($nameOrClosure, $parameters);
            } else {
                $presentationFilters[] = array($nameOrClosure, $parameters);
            }
        }

        return array($priorityFilters, $presentationFilters);
    }

    /**
     * TODO
     */
    public function addRelatedReport($relatedReport, $title, $queryParams = array())
    {
        list($module, $action) = explode('.', $relatedReport);

        // don't add the related report if it references this report
        if ($this->controllerName == $module && $this->controllerAction == $action) {
            return;
        }

        $url = ApiRequest::getBaseReportUrl($module, $action, $queryParams);

        $this->related_reports[$url] = $title;
    }

    /**
     * TODO
     */
    public function addRelatedReports($relatedReports)
    {
        foreach ($relatedReports as $report => $title) {
            $this->addRelatedReport($report, $title);
        }
    }
}
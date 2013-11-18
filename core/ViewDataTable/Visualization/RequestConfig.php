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
use Piwik\Common;

/**
 * TODO
 *
 * @package Piwik
 * @subpackage Piwik_Visualization
 * @api
 */
class RequestConfig extends \Piwik\ViewDataTable\RequestConfig
{
    /**
     * An array property that contains query parameter name/value overrides for API requests made
     * by ViewDataTable.
     *
     * E.g. array('idSite' => ..., 'period' => 'month')
     *
     * Default value: array()
     */
    public $request_parameters_to_modify = array();

    /**
     * Whether to run generic filters on the DataTable before rendering or not.
     *
     * @see Piwik_API_DataTableGenericFilter
     *
     * Default value: false
     */
    public $disable_generic_filters = false;

    /**
     * Whether to run ViewDataTable's list of queued filters or not.
     *
     * NOTE: Priority queued filters are always run.
     *
     * Default value: false
     */
    public $disable_queued_filters = false;

    public function __construct()
    {
        $this->addPropertiesThatCanBeOverwrittenByQueryParams(array(
            'disable_generic_filters',
            'disable_queued_filters'
        ));
    }

    /**
     * Returns true if queued filters have been disabled, false if otherwise.
     *
     * @return bool
     */
    public function areQueuedFiltersDisabled()
    {
        return isset($this->disable_queued_filters) && $this->disable_queued_filters;
    }

    /**
     * Returns true if generic filters have been disabled, false if otherwise.
     *
     * @return bool
     */
    public function areGenericFiltersDisabled()
    {
        // if disable_generic_filters query param is set to '1', generic filters are disabled
        if (Common::getRequestVar('disable_generic_filters', '0', 'string') == 1) {
            return true;
        }

        if (isset($this->disable_generic_filters) && true === $this->disable_generic_filters) {
            return true;
        }

        return false;
    }

}

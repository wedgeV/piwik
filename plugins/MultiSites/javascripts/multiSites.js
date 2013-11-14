/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function setRowData(idsite, visits, pageviews, revenue, name, url, visitsSummaryValue, pageviewsSummaryValue, revenueSummaryValue) {
    this.idsite = idsite;
    this.visits = visits;
    this.revenue = revenue;
    this.name = name;
    this.url = url;
    this.pageviews = pageviews;
    this.visitsSummaryValue = parseFloat(visitsSummaryValue);
    this.pageviewsSummaryValue = parseFloat(pageviewsSummaryValue);
    this.revenueSummaryValue = parseFloat(revenueSummaryValue) || 0;
}

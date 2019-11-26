export default class UrlResolver {
    constructor() {
        this.dev = window.isDebug;
    }

    isDev() {
        return this.dev;
    }

    getPrefix() {
        return this.isDev() ? '/app_dev.php' : '';
    }

    getTable() {
        return this.getPrefix() + '/api/table';
    }

    getTableVersion(id) {
        return this.getPrefix() + '/api/version/' + id;
    }

    getCalculate() {
        return this.getPrefix() + '/api/calculate';
    }

    getSave() {
        return this.getPrefix() + '/api/save';
    }

    getRegions() {
        return this.getPrefix() + '/api/regions';
    }

    getRegionsRelatedToUser() {
        return this.getPrefix() + '/api/user/regions';
    }

    getMarkets() {
		return this.getPrefix() + '/api/countries';
    }

    getIndicators() {
        return this.getPrefix() + '/api/indicators';
    }

    getTechnologies() {
        return this.getPrefix() + '/api/technologies';
    }

    getYears() {
        return this.getPrefix() + '/api/years';
    }

    getUploadStatus() {
        return this.getPrefix() + '/api/upload-status';
    }

    sendDeadlineReminderToCountry(id) {
        return this.getPrefix() + '/api/country/' + id + '/send-deadline-reminder';
    }

    requestContribution(regionId) {
        return this.getPrefix() + '/api/regions/' + regionId + '/contribution';
    }

    getContributionCountryRequest(id) {
        return this.getPrefix() + '/api/contribution-country-request/' + id;
    }

    viewRequestContribution(countryId) {
        return this.getPrefix() + '/contribution/' + countryId;
    }

    countryContributorRequestContribution(countryId) {
        return this.getPrefix() + '/contribution/contributor/' + countryId;
    }

    indicatorContributorRequestContribution(id) {
        return this.getPrefix() + '/contribution/contributor/indicator/' + id;
    }

    getAdminContributionTable(countryId) {
        return this.getPrefix() + '/api/contribution/' + countryId + '/table';
    }

    getContributorContributionTable(id) {
        return this.getPrefix() + '/api/contribution/contributor/' + id + '/table';
    }

    getContributionComments(countryId) {
        return this.getPrefix() + '/api/contribution/' + countryId + '/comments';
    }

    saveContributorFeedback(countryId) {
        return this.getPrefix() + '/api/contribution/' + countryId + '/contributor-feedback';
    }

    saveContributorIndicatorFeedback(id) {
        return this.getPrefix() + '/api/contribution/' + id + '/contributor-indicator-feedback';
    }

    saveContributorComment(countryId) {
        return this.getPrefix() + '/api/contribution/' + countryId + '/contributor-comment';
    }

    saveAdminFeedback(countryId) {
        return this.getPrefix() + '/api/contribution/' + countryId + '/admin-feedback';
    }

    editFile() {
        return this.getPrefix() + '/edit';
    }

    exportVersion(id) {
        return this.getPrefix() + '/version/export/' + id;
    }

    exportModel() {
        return this.getPrefix() + '/api/sheet/export';
    }

    getParcBySegment() {
        return this.getPrefix() + '/api/reporting/parc-segment';
    }

    getParcByRegion() {
        return this.getPrefix() + '/api/reporting/parc-region';
    }

    getParcByTechnologyData() {
        return this.getPrefix() + '/api/reporting/parc-technology';
    }

    getMarketVolumeByRegion() {
        return this.getPrefix() + '/api/reporting/market-volume-region';
    }

    getMarketSizeByRegion() {
        return this.getPrefix() + '/api/reporting/market-size-region';
    }

    getMarketVolumeBySegment() {
        return this.getPrefix() + '/api/reporting/market-volume-segment';
    }

    getMarketSizeBySegment() {
        return this.getPrefix() + '/api/reporting/market-size-segment';
    }

    getMarketVolumeByTechnology() {
        return this.getPrefix() + '/api/reporting/market-volume-technology';
    }

    getMarketSizeByTechnology() {
        return this.getPrefix() + '/api/reporting/market-size-technology';
    }

    getMarketShareByRegion() {
        return this.getPrefix() + '/api/reporting/market-share-region';
    }

    getMarketShareByTechnology() {
        return this.getPrefix() + '/api/reporting/market-share-technology';
    }

    getSimpleIndicatorChart() {
        return this.getPrefix() + '/api/reporting/simple-indicator-chart';
    }
}

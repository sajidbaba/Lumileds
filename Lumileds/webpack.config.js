var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')
    .addEntry('app', './web/assets/js/app.js')
    .addEntry('user', './web/assets/js/user.js')
    .addEntry('edit', './web/assets/js/edit.js')
    .addEntry('contribution-admin-list', './web/assets/js/contribution/admin/list.js')
    .addEntry('contribution-admin-view', './web/assets/js/contribution/admin/view.js')
    .addEntry('contribution-contributor-list', './web/assets/js/contribution/contributor/list.js')
    .addEntry('contribution-contributor-country', './web/assets/js/contribution/contributor/country.js')
    .addEntry('contribution-contributor-indicator', './web/assets/js/contribution/contributor/indicator.js')
	.addEntry('upload', './web/assets/js/upload.js')
    .addEntry('versioning', './web/assets/js/versioning.js')
    .addEntry('reporting', './web/assets/js/reporting.js')
    .addEntry('scrollToTop', './web/assets/js/scrollToTop.js')
    .addStyleEntry('login', './web/assets/css/login.scss')
	.addStyleEntry('app-css', './web/assets/css/app.scss')
    .enableSassLoader()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .enableVueLoader()
    .autoProvidejQuery()
    .enableBuildNotifications()
    .enableVersioning()
	.configureBabel(function(babelConfig) {
		// add additional presets
		babelConfig.presets.push('es2015');
	})
;

// export the final configuration
module.exports = Encore.getWebpackConfig();

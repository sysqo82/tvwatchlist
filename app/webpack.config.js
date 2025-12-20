const Encore = require('@symfony/webpack-encore');
const { PurgeCSSPlugin } = require('purgecss-webpack-plugin');
const glob = require('glob-all');
const path = require('path');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    .enableReactPreset()

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
        // Target modern browsers to reduce polyfills
        config.targets = {
            browsers: [
                'last 2 Chrome versions',
                'last 2 Firefox versions',
                'last 2 Safari versions',
                'last 2 Edge versions'
            ]
        };
    })

    // enables Sass/SCSS support
    .enableSassLoader((options) => {
        options.api = 'modern';
        options.sassOptions = {
            silenceDeprecations: [
                'legacy-js-api',
                'import',
                'global-builtin',
                'color-functions'
            ],
            loadPaths: [
                'node_modules'
            ]
        };
    })

    // copy images to build directory
    .copyFiles({
        from: './images',
        to: 'images/[path][name].[ext]'
    })

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

// Add PurgeCSS in production to remove unused CSS
if (Encore.isProduction()) {
    Encore.addPlugin(new PurgeCSSPlugin({
        paths: glob.sync([
            path.join(__dirname, 'templates/**/*.html.twig'),
            path.join(__dirname, 'assets/**/*.jsx'),
            path.join(__dirname, 'assets/**/*.js')
        ]),
        safelist: {
            standard: [
                /^modal/,
                /^dropdown/,
                /^collapse/,
                /^show$/,
                /^fade$/,
                /^active$/,
                /^disabled$/,
                /^btn/,
                /^badge/,
                /^alert/,
                /^progress/,
                /^bento/,
                /^series/,
                /^movie/,
                /^archive/
            ],
            deep: [/^bootstrap/],
            greedy: []
        }
    }));
}

// Get webpack config and optimize
const webpackConfig = Encore.getWebpackConfig();

// Enable module concatenation (scope hoisting) for better tree shaking
if (Encore.isProduction()) {
    webpackConfig.optimization = {
        ...webpackConfig.optimization,
        usedExports: true,
        sideEffects: true,
        concatenateModules: true,
        minimize: true
    };
}

module.exports = webpackConfig;

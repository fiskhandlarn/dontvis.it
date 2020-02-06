const mix = require('laravel-mix');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const ImageminPlugin = require('imagemin-webpack-plugin').default;
const CopyWebpackPlugin = require('copy-webpack-plugin');
const FaviconsWebpackPlugin = require('favicons-webpack-plugin');

require('dotenv').config();

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

const publicPath = `public/assets`;

mix.setResourceRoot('../');
mix.setPublicPath(publicPath);

mix.webpackConfig({
  module: {
    rules: [
      {
        test: /\.scss$/,
        loader: 'import-glob-loader',
      },
    ],
  },
  plugins: [
    new FaviconsWebpackPlugin({
      logo: './resources/assets/images/favicons/favicon.svg',
      publicPath: '/assets',
      prefix: 'images/favicons',
      mode: 'webapp',
      devMode: 'webapp',
      inject: true,
      favicons: {
        background: '#000000',
        appName: 'dontvis.it',
        appDescription: 'dontvis.it, the idiot circumventor tool',
        icons: {
          android: true,
          appleIcon: true,
          appleStartup: false,
          coast: true,
          favicons: true,
          firefox: true,
          windows: true,
          yandex: true,
        },
      },
    }),
    new StyleLintPlugin({
      context: '// resources/assets/styles doesnt work o_O',
      context: 'resources',
    }),
    // // Copy the fonts folder
    // new CopyWebpackPlugin([{
    //   from: 'resources/assets/fonts/',
    //   to: 'fonts'
    // }]),
    // Copy the images folder and optimize all the images
    new CopyWebpackPlugin([{
      from: 'resources/assets/images/',
      to: 'images'
    }]),
    // Copy favicon.ico to web root
    new CopyWebpackPlugin([{
      from: 'public/assets/images/favicons/favicon.ico',
      to: '../'
    }]),
    // new ImageminPlugin({
    //   test: /\.svg$/i,
    //   svgo: {
    //     plugins: [
    //       {
    //         removeTitle: true
    //       },
    //       {
    //         removeStyleElement: true
    //       },
    //       {
    //         removeAttrs : {
    //           attrs : [ "class", "style" ]
    //         }
    //       }
    //     ]
    //   }
    // }),
  ],
});

// Compile styles.
mix.sass('resources/assets/styles/app.scss', 'styles', {
  includePaths: ['node_modules'],
});

if (process.env.DEBUG == "true") {
  mix.styles([`${publicPath}/styles/app.css`, 'node_modules/revenge.css/revenge.css'], `${publicPath}/styles/app.css`);
}

// Versioning.
mix.version();

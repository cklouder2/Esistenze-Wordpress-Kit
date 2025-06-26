const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';

  return {
    entry: {
      'admin': './assets/admin.js',
      'admin-style': './assets/admin.css',
      'quick-menu-admin': './modules/quick-menu-cards/assets/admin.js',
      'quick-menu-admin-edit': './modules/quick-menu-cards/assets/admin-edit.js',
      'quick-menu-admin-advanced': './modules/quick-menu-cards/assets/admin-edit-advanced.js',
      'quick-menu-admin-bulk': './modules/quick-menu-cards/assets/admin-edit-bulk.js',
      'quick-menu-admin-tools': './modules/quick-menu-cards/assets/admin-edit-tools.js',
      'quick-menu-style': './modules/quick-menu-cards/assets/style.css',
      'quick-menu-admin-style': './modules/quick-menu-cards/assets/admin.css',
      'smart-buttons-script': './modules/smart-product-buttons/assets/script.js',
      'smart-buttons-style': './modules/smart-product-buttons/assets/style.css',
      'category-styler-style': './modules/category-styler/assets/style.css',
      'custom-topbar-style': './modules/custom-topbar/assets/style.css'
    },
    output: {
      path: path.resolve(__dirname, 'dist'),
      filename: '[name].min.js',
      clean: true
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env']
            }
          }
        },
        {
          test: /\.css$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            'postcss-loader'
          ]
        },
        {
          test: /\.scss$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            'postcss-loader',
            'sass-loader'
          ]
        },
        {
          test: /\.(png|jpg|jpeg|gif|svg)$/,
          type: 'asset/resource',
          generator: {
            filename: 'images/[name][ext]'
          }
        },
        {
          test: /\.(woff|woff2|eot|ttf|otf)$/,
          type: 'asset/resource',
          generator: {
            filename: 'fonts/[name][ext]'
          }
        }
      ]
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: '[name].min.css'
      })
    ],
    optimization: {
      minimize: isProduction,
      splitChunks: {
        chunks: 'all',
        cacheGroups: {
          vendor: {
            test: /[\\/]node_modules[\\/]/,
            name: 'vendors',
            chunks: 'all'
          }
        }
      }
    },
    resolve: {
      extensions: ['.js', '.jsx', '.css', '.scss']
    },
    devtool: isProduction ? 'source-map' : 'eval-source-map',
    stats: {
      colors: true,
      modules: false,
      chunks: false,
      chunkModules: false
    }
  };
}; 
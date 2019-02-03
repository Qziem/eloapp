const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const outputDir = path.join(__dirname, "build/");

const isProd = process.env.NODE_ENV === 'production';

module.exports = {
  entry: './src/Index.bs.js',
  mode: isProd ? 'production' : 'development',
  output: {
    path: outputDir,
    publicPath: '/',
    filename: 'Index.js',
  },
  plugins: [
    new HtmlWebpackPlugin({
      template: '../index.html',
      inject: true
    })
  ],
  watch: true,
  watchOptions: {
    poll: true
  },
  devServer: {
    compress: true,
    contentBase: outputDir,
    port: process.env.PORT || 8080,
    historyApiFallback: true,
    proxy: {
      '/api': 'http://localhost:8000',
      '/fe/build/': 'http://localhost:8000',
    }
  },
  module: {
    rules: [{
      test: /\.scss$/,
      use: [{
        loader: "style-loader"
      }, {
        loader: "css-loader"
      }, {
        loader: "sass-loader",
      }]
    },
    {
      test: /\.css$/,
      use: ['style-loader', 'css-loader'],
    },
    {
      test: /\.(png|jpg|gif|woff|woff2|eot|ttf|svg)$/,
      use: [
        {
          loader: 'file-loader',
          options: {
            publicPath: 'fe/build/',
          },
        },
      ],
    },
    ]
  },
};

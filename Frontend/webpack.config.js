const path = require('path');
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');

module.exports = {
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src/'),
    },
    extensions: ['.js', '.vue'],
  },
  devServer: {
    hot: true,
    watchOptions: {
      aggregateTimeout: 300,
      poll: true,
    },
  },
  plugins: [
    new MomentLocalesPlugin({
      localesToKeep: ['en', 'de', 'tr'],
    }),
  ],
};

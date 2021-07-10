module.exports = {
  output: {
    // publicPath: '',
  },
  devServer: {
    headers: {
      'Access-Control-Allow-Credentials': 'true',
      'Vary': 'Origin',
    },
  },
};

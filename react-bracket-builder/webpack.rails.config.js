const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
  ...defaultConfig,
  entry: './src/railsEntry.tsx',
  output: {
    path: path.resolve(__dirname, '../app/assets/javascripts/react-bracket-builder'),
    filename: 'bundle.js',
    publicPath: '/assets/react-bracket-builder/',
  },
  externals: {
    // Don't bundle these, assume they're available globally
    'react': 'React',
    'react-dom': 'ReactDOM',
  },
  module: {
    ...defaultConfig.module,
    rules: [
      ...defaultConfig.module.rules,
      {
        test: /\.css$/i,
        use: [
          'style-loader',
          'css-loader',
          'postcss-loader',
        ],
      },
    ],
  },
};

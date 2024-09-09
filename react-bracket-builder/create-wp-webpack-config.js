const path = require('path')
const defaults = require('@wordpress/scripts/config/webpack.config')
console.info('loading wordpress webpack config')

function createWpWebpackConfig(...pathSegments) {
  return {
    ...defaults,
    output: {
      ...defaults.output,
      path: path.resolve(...pathSegments),
    },
    externals: {
      react: 'React',
      'react-dom': 'ReactDOM',
    },
  }
}

module.exports = createWpWebpackConfig

const path = require('path')
const defaults = require('@wordpress/scripts/config/webpack.config')
console.log('loading wordpress webpack config')

module.exports = {
  ...defaults,
  output: {
    ...defaults.output,
    path: path.resolve(__dirname, 'build', 'wordpress'),
  },
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',
  },
}

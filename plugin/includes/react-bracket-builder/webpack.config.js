const isWordPress = process.env.WP_ENV === 'true'

module.exports = isWordPress
  ? require('./webpack.wp.config')
  : require('./webpack.non-wp.config')

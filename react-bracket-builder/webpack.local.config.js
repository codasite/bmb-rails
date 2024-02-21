const createWpWebpackConfig = require('./create-wp-webpack-config')
console.log('outputting to build/wordpress')

module.exports = createWpWebpackConfig(__dirname, 'build', 'wordpress')

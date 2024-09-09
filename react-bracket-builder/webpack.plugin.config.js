const createWpWebpackConfig = require('./create-wp-webpack-config')
console.info(
  'outputting to plugin/Includes/react-bracket-builder/build/wordpress'
)

module.exports = createWpWebpackConfig(
  __dirname,
  '../plugin/Includes/react-bracket-builder/build/wordpress'
)
// module.exports = {
//   ...defaults,
//   output: {
//     ...defaults.output,
//     // path: path.resolve(__dirname, 'build', 'wordpress'),
//     path: path.resolve(
//       __dirname,
//       '../plugin/Includes/react-bracket-builder/build/wordpress'
//     ),
//   },
//   externals: {
//     react: 'React',
//     'react-dom': 'ReactDOM',
//   },
// }

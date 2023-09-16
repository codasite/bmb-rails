const defaults = require('@wordpress/scripts/config/webpack.config');

module.exports = {
	...defaults,
	externals: {
		react: 'React',
		'react-dom': 'ReactDOM',
	},
	// module: {
	// 	...defaults.module,
	// 	rules: [
	// 		...defaults.module.rules,
	// 		{
	// 			test: /\.css$/,
	// 			use: [
	// 				...defaults.module.rules.find((rule) => rule.test.toString().includes('css')).use,
	// 				{
	// 					loader: require.resolve('postcss-loader'),
	// 					options: {
	// 						postcssOptions: {
	// 							plugins: [
	// 								require('tailwindcss')(path.resolve(__dirname, 'tailwind.config.js')),
	// 								require('autoprefixer')
	// 							]
	// 						}
	// 					}
	// 				}
	// 			]
	// 		}

}; 
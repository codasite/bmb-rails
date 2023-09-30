const defaults = require('@wordpress/scripts/config/webpack.config');
const HtmlWebpackPlugin = require('html-webpack-plugin');

const htmlPlugin = new HtmlWebpackPlugin({
	template: './src/index.html',
	filename: './index.html',
});

module.exports = {
	...defaults,
	externals: {
		react: 'React',
		'react-dom': 'ReactDOM',
	},
	plugins: [
		...defaults.plugins,
		htmlPlugin,
	],
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
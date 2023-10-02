const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
console.log('loading standalone webpack config');

module.exports = {
	mode: 'production',
	devServer: {
		allowedHosts: 'all',
	},
	entry: './src/index.tsx',
	output: {
		path: path.resolve(__dirname, 'build', 'standalone'),
		filename: 'index.js',
	},
	plugins: [
		new HtmlWebpackPlugin({
			template: './src/index.html',
			filename: './index.html',
			inject: 'head',
			preload: ['src/assets/fonts/ClashDisplay-Variable.woff2'],

		}),
	],
	module: {
		rules: [
			{
				test: /\.(js|jsx|ts|tsx)$/,
				exclude: /node_modules/,
				loader: 'babel-loader',
			},
			{
				test: /\.svg$/,
				issuer: /\.(j|t)sx?$/,
				use: ['@svgr/webpack', 'url-loader'],
				type: 'javascript/auto',
			},
			{
				test: /\.(bmp|png|jpe?g|gif|webp)$/i,
				type: 'asset/resource',
				generator: {
					filename: 'images/[name].[hash:8][ext]',
				},
			},
			{
				test: /\.(woff|woff2|eot|ttf|otf)$/i,
				type: 'asset/resource',
				generator: {
					filename: 'fonts/[name].[hash:8][ext]',
				},
			},
			{
				test: /\.css$/i,
				use: [
					'style-loader',
					'css-loader',
					// 'postcss-loader',
				],
			}
		]
	},
	resolve: {
		extensions: ['.*', '.js', '.jsx', '.ts', '.tsx'],
	},
}; 
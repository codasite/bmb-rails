const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
console.log('loading non-wordpress webpack config');

const htmlPlugin = new HtmlWebpackPlugin({
	template: './src/index.html',
	filename: './index.html',
});

module.exports = {
	entry: './src/index.tsx',
	output: {
		path: path.resolve(__dirname, 'build', 'standalone'),
		filename: 'index.js',
	},
	plugins: [
		htmlPlugin,
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
		]
	},
	resolve: {
		extensions: ['.*', '.js', '.jsx', '.ts', '.tsx'],
	},
}; 
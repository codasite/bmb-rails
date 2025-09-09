const path = require('path')
const HtmlWebpackPlugin = require('html-webpack-plugin')

console.info('loading standalone bracket builder webpack config')

module.exports = {
  mode: 'development',
  devServer: {
    allowedHosts: 'all',
    port: 3001,
    open: true,
    hot: true,
  },
  entry: './src/standaloneEntry.tsx',
  output: {
    path: path.resolve(__dirname, 'build', 'standalone'),
    filename: 'bundle.js',
    publicPath: '/',
  },
  plugins: [
    new HtmlWebpackPlugin({
      template: './src/standalone.html',
      filename: './index.html',
      inject: 'body',
    }),
  ],
  module: {
    rules: [
      {
        test: /\.(ts|tsx)$/,
        exclude: /node_modules/,
        use: 'ts-loader',
      },
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            babelrc: false,
            configFile: false,
            presets: [
              ['@babel/preset-env', { 
                targets: { browsers: ['last 2 versions'] },
                modules: false
              }],
              ['@babel/preset-react', { 
                runtime: 'automatic' 
              }],
            ],
            plugins: [
              '@babel/plugin-transform-class-properties',
            ],
          },
        },
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
          'postcss-loader',
        ],
      },
    ],
  },
  resolve: {
    extensions: ['.*', '.js', '.jsx', '.ts', '.tsx'],
  },
}

import express from 'express';
import path from 'path';
import { createProxyMiddleware } from 'http-proxy-middleware';

console.log('react server')
const app = express();
// app.use(express.static('client/dist'))

const port = 3001;
const host = '0.0.0.0'

// forward requests to the dev server in development mode
const devHost = 'react-client'
const devPort = 8080


app.get('/hello', async (req, res) => {
	res.send('Hello from react server');
})

// app.get('*', (req, res) => {
// 	res.sendFile(path.join(__dirname, '../client/dist/index.html'))
// })

app.use('*', createProxyMiddleware({
	target: `http://${devHost}:${devPort}`,
	changeOrigin: true,
}))

app.listen(port, host, () => {
	console.log(`Example app listening at http://${host}:${port}`);
})
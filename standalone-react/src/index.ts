import express from 'express';
import path from 'path';

const app = express();
app.use(express.static('client/dist'))

const port = 3001;
const host = '0.0.0.0'


app.get('/hello', async (req, res) => {
	res.send('Hello from react server');
})

app.get('*', (req, res) => {
	res.sendFile(path.join(__dirname, './client/dist/index.html'))
})

app.listen(port, host, () => {
	console.log(`Example app listening at http://${host}:${port}`);
})
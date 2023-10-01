import express from 'express';

const app = express();
app.use(express.json());

const port = 3001;
const host = '0.0.0.0'


app.get('/', async (req, res) => {
	res.send('Hello from react server');
})

app.listen(port, host, () => {
	console.log(`Example app listening at http://${host}:${port}`);
})
import express from 'express';
import puppeteer from 'puppeteer';
import { PutObjectCommand, S3Client } from '@aws-sdk/client-s3'


const app = express();
app.use(express.json());
const port = 3000;
const host = '0.0.0.0'

interface ConvertParams {
	html: string;
	inchHeight: number;
	inchWidth: number;
	s3Bucket: string;
	s3Key: string;
	pdf?: boolean;
	deviceScaleFactor?: number;
}

interface ConvertResponse {
	imageUrl?: string;
	error?: string;
}

app.post('/encode', async (req, res) => {
	const {
		picks,
		matches,
	} = req.body;

	const encodedPicks = encodeURIComponent(JSON.stringify(picks));
	const encodedMatches = encodeURIComponent(JSON.stringify(matches));

	const encoded = {
		picks: encodedPicks,
		matches: encodedMatches,
	}

	res.send(encoded);
})

app.get('/', async (req, res) => {
	res.send('Hello World!!!!');
})


app.post('/', async (req, res) => {
	const {
		html,
		url,
		queryParams,
		s3Bucket,
		s3Key,
		pdf,
		deviceScaleFactor = 1,
		inchHeight = 16,
		inchWidth = 11,

	} = req.body;

	// const {
	// 	inch_height,
	// 	inch_width,
	// } = queryParams;



	if (inchHeight && !Number.isInteger(inchHeight)) {
		return res.status(400).send('inch_height must be an integer');
	}

	if (inchWidth && !Number.isInteger(inchWidth)) {
		return res.status(400).send('inch_width must be an integer');
	}

	const pxHeight = inchHeight * 96;
	const pxWidth = inchWidth * 96;


	console.time('start')
	console.time('launch')
	const browser = await puppeteer.launch({ headless: true });
	console.timeEnd('launch')
	console.time('newPage')
	const page = await browser.newPage();
	console.timeEnd('newPage')

	console.time('setViewport')
	await page.setViewport({
		height: pxHeight,
		width: pxWidth,
		deviceScaleFactor
	});
	console.timeEnd('setViewport')

	console.time('goto')
	if (html) {
		console.log('html')
		await page.setContent(html, { waitUntil: 'networkidle0' });
	} else if (url) {
		console.log('url')
		const queryString = Object.keys(queryParams).map(key => key + '=' + queryParams[key]).join('&');
		const path = url + (queryString ? '?' + queryString : '')
		try {
			await page.goto(path, { waitUntil: 'networkidle0' });
		}
		catch (err) {
			console.log(err)
			return res.status(400).send('invalid url');
		}
	} else {
		return res.status(400).send('html or url is required');
	}
	console.timeEnd('goto')

	console.time('screenshot')

	let file: Buffer;
	if (pdf) {
		file = await page.pdf({
			height: pxHeight,
			width: pxWidth,
			omitBackground: true,
			printBackground: true,
		});
	} else {
		file = await page.screenshot({
			// fullPage: true,
			omitBackground: true,
		});
	}

	console.timeEnd('screenshot')
	const extension = pdf ? 'pdf' : 'png';
	const contentType = pdf ? 'application/pdf' : 'image/png';
	try {
		console.time('uploadToS3')
		const imgUrl = await uploadToS3(file, extension, contentType, s3Bucket, s3Key);
		console.timeEnd('uploadToS3')
		res.send(imgUrl);
	} catch (err: any) {
		res.status(500).send(err);
	} finally {
		console.timeEnd('start')
		await browser.close();
	}
})

const uploadToS3 = async (buffer: Buffer, extension: string, contentType: string, bucket: string, fileName: string): Promise<string> => {
	const s3 = new S3Client();
	const command = new PutObjectCommand({
		Bucket: bucket,
		Key: fileName,
		Body: buffer,
		ContentType: contentType,
	});
	return s3.send(command).then((data) => {
		return `https://${bucket}.s3.amazonaws.com/${fileName}`;
	})

}

app.listen(port, host, () => {
	console.log(`Example app listening at http://${host}:${port}`);
})
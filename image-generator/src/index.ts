import express from 'express'
import puppeteer from 'puppeteer'
import { PutObjectCommand, S3Client } from '@aws-sdk/client-s3'

import os from 'os'

const app = express()
app.use(express.json())
const port = 3000
const host = '0.0.0.0'

app.post('/encode', async (req, res) => {
  const { picks, matches } = req.body

  const encodedPicks = encodeURIComponent(JSON.stringify(picks))
  const encodedMatches = encodeURIComponent(JSON.stringify(matches))

  const encoded = {
    picks: encodedPicks,
    matches: encodedMatches,
  }

  res.send(encoded)
})

app.get('/', async (req, res) => {
  const user = os.userInfo()
  res.send(`Hello World! ${user.username}`)
})

app.get('/ping', async (req, res) => {
  res.send('pong')
})

interface ObjectStorageUploader {
  upload: (buffer: Buffer, contentType: string, body: any) => Promise<string>
}

const s3Uploader: ObjectStorageUploader = {
  upload: async (buffer, contentType, storageOptions) => {
    if (!storageOptions) {
      throw new Error('storageOptions is required')
    }
    const { bucket, key } = storageOptions
    if (!bucket || !key) {
      throw new Error('bucket and key are required')
    }

    const s3 = new S3Client({ region: process.env.AWS_REGION })
    const command = new PutObjectCommand({
      Bucket: bucket,
      Key: key,
      Body: buffer,
      ContentType: contentType,
    })
    return s3.send(command).then((data) => {
      return JSON.stringify({
        image_url: `https://${bucket}.s3.amazonaws.com/${key}`,
      })
    })
  },
}

interface GenerateRequest {
  url?: string
  queryParams?: any
  storageOptions?: any
  storageService?: string
  pdf?: boolean
  deviceScaleFactor?: number
  inchHeight?: number
  inchWidth?: number
}

const generateBracketImage = async (req: GenerateRequest, res: any) => {
  const {
    url,
    queryParams,
    storageOptions,
    storageService,
    pdf,
    deviceScaleFactor = 1,
    inchHeight = 16,
    inchWidth = 11,
  } = req.body

  const clientUrl = url ?? process.env.CLIENT_URL

  if (inchHeight && !Number.isInteger(inchHeight)) {
    return res.status(400).send('inch_height must be an integer')
  }

  if (inchWidth && !Number.isInteger(inchWidth)) {
    return res.status(400).send('inch_width must be an integer')
  }

  const pxHeight = inchHeight * 96
  const pxWidth = inchWidth * 96

  console.time('start')
  console.time('launch')
  const browser = await puppeteer.launch({ headless: 'new' })
  console.timeEnd('launch')
  console.time('newPage')
  const page = await browser.newPage()
  console.timeEnd('newPage')

  console.time('setViewport')
  await page.setViewport({
    height: pxHeight,
    width: pxWidth,
    deviceScaleFactor,
  })
  console.timeEnd('setViewport')

  console.time('goto')
  if (clientUrl) {
    const queryString = Object.keys(queryParams)
      .map((key) => key + '=' + queryParams[key])
      .join('&')
    const path = clientUrl + (queryString ? '?' + queryString : '')
    try {
      await page.goto(path, { waitUntil: 'networkidle0' })
    } catch (err) {
      console.log(err)
      return res.status(400).send('invalid url')
    }
  } else {
    return res.status(400).send('html or url is required')
  }
  console.timeEnd('goto')

  console.time('screenshot')

  let file: Buffer
  if (pdf) {
    file = await page.pdf({
      height: pxHeight,
      width: pxWidth,
      omitBackground: true,
      printBackground: true,
    })
  } else {
    file = await page.screenshot({
      // fullPage: true,
      omitBackground: true,
    })
  }

  console.timeEnd('screenshot')
  const extension = pdf ? 'pdf' : 'png'
  const contentType = pdf ? 'application/pdf' : 'image/png'
  let uploader: ObjectStorageUploader
  if (storageService === 's3') {
    uploader = s3Uploader
  }
  if (!uploader) {
    return res.status(400).send('storageService is required')
  }
  try {
    console.time('uploadToS3')
    const image_url = await uploader.upload(file, contentType, storageOptions)
    res.send(image_url)
  } catch (err: any) {
    console.error(err)
    res.status(500).send(err)
  } finally {
    console.timeEnd('uploadToS3')
    console.timeEnd('start')
    await browser.close()
  }
}

app.post('/generate', async (req, res) => {})

app.post('/test', async (req, res) => {
  console.log(req.body)
  res.send(req.body)
})

app.listen(port, host, () => {
  console.log(`Example app listening at http://${host}:${port}`)
})

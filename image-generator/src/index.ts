import express from 'express'
import puppeteer from 'puppeteer'
import { PutObjectCommand, S3Client } from '@aws-sdk/client-s3'

import os from 'os'

const app = express()
app.use(express.json())
const port = 3000
const host = '0.0.0.0'

class ValidationError extends Error {
  constructor(message: string) {
    super(message)
    this.name = 'ValidationError'
  }
}

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
      throw new ValidationError('storageOptions is required')
    }
    const { bucket, key } = storageOptions
    if (!bucket || !key) {
      throw new ValidationError('bucket and key are required')
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

const validateParams = (req: GenerateRequest) => {
  const validStorages = ['s3']
  const errors = []
  const { inchHeight, inchWidth, url, storageOptions, storageService } = req
  if (inchHeight && !Number.isInteger(inchHeight)) {
    errors.push('inch_height must be an integer')
  }
  if (inchWidth && !Number.isInteger(inchWidth)) {
    errors.push('inch_width must be an integer')
  }
  if (!url) {
    errors.push('url is required')
  }
  if (!storageService || !validStorages.includes(storageService)) {
    errors.push('storageService is required. Valid options: ' + validStorages)
  }
  if (errors.length) {
    throw new ValidationError(errors.join(', '))
  }
}

const generateBracketImage = async (req: GenerateRequest) => {
  if (!req.url) {
    req.url = process.env.CLIENT_URL
  }

  validateParams(req)

  const {
    url,
    queryParams,
    storageOptions,
    storageService,
    pdf,
    deviceScaleFactor = 1,
    inchHeight = 16,
    inchWidth = 11,
  } = req

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
  const queryString = Object.keys(queryParams)
    .map((key) => key + '=' + queryParams[key])
    .join('&')
  const path = url + (queryString ? '?' + queryString : '')
  try {
    await page.goto(path, { waitUntil: 'networkidle0' })
  } catch (err) {
    console.log(err)
    browser.close()
    throw new Error(`Error loading ${path}`)
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
  let image_url
  try {
    console.time('uploadToS3')
    image_url = await uploader.upload(file, contentType, storageOptions)
  } catch (err: any) {
    console.error(err)
    throw new Error('Error uploading to S3')
  } finally {
    console.timeEnd('uploadToS3')
    console.timeEnd('start')
    await browser.close()
  }
  return image_url
}

app.post('/generate', async (req, res) => {
  try {
    const image_url = await generateBracketImage(req.body)
    res.send(image_url)
  } catch (err: any) {
    if (err.name === 'ValidationError') {
      res.status(400).send('ValidationError: ' + err.message)
      return
    }
    console.error(err)
    res.status(500).send('Error generating image: ' + err.message)
  }
})

app.post('/test', async (req, res) => {
  console.log(req.body)
  res.send(req.body)
})

app.listen(port, host, () => {
  console.log(`Example app listening at http://${host}:${port}`)
})

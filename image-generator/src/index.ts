import express from 'express'
import puppeteer from 'puppeteer'
import { PutObjectCommand, S3Client } from '@aws-sdk/client-s3'
import http from 'http'
//import aws

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
  const randInt = Math.floor(Math.random() * 1000000)
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
    inchWidth = 12,
  } = req

  const pxHeight = inchHeight * 96
  const pxWidth = inchWidth * 96

  // const browser = await puppeteer.launch({ headless: 'new' })
  const browser = await puppeteer.launch({ headless: 'new' })
  const page = await browser.newPage()

  console.time('setViewport ' + randInt)
  await page.setViewport({
    height: pxHeight,
    width: pxWidth,
    deviceScaleFactor,
  })
  console.timeEnd('setViewport ' + randInt)

  const queryString = Object.entries(queryParams)
    .map(([key, value]) => {
      if (typeof value === 'object') {
        value = encodeURIComponent(JSON.stringify(value))
      }
      return key + '=' + encodeURIComponent(value as any)
    })
    .join('&')
  const path = url + (queryString ? '?' + queryString : '')
  console.time('goto ' + randInt)
  try {
    await page.goto(path, { waitUntil: 'networkidle0' })
    // await page.setContent('<div>HIIIIII</div>', { waitUntil: 'networkidle0' })
  } catch (err) {
    console.error(err)
    browser.close()
    throw new Error(`Error loading ${path}`)
  } finally {
    console.timeEnd('goto ' + randInt)
  }

  console.time('screenshot ' + randInt)

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
  console.timeEnd('screenshot ' + randInt)

  const extension = pdf ? 'pdf' : 'png'
  const contentType = pdf ? 'application/pdf' : 'image/png'
  let uploader: ObjectStorageUploader
  if (storageService === 's3') {
    uploader = s3Uploader
  }
  let image_url
  console.time('upload ' + randInt)
  try {
    image_url = await uploader.upload(file, contentType, storageOptions)
  } catch (err: any) {
    console.error(err)
    throw new Error('Error uploading to S3')
  } finally {
    await browser.close()
    console.timeEnd('upload ' + randInt)
  }
  return image_url
}

app.post('/generate', async (req, res) => {
  const theme = req.body.queryParams.theme
  const position = req.body.queryParams.position
  try {
    console.time(`generateBracketImage ${theme} ${position}`)
    const image_url = await generateBracketImage(req.body)
    console.log(image_url)
    res.send(image_url)
  } catch (err: any) {
    if (err.name === 'ValidationError') {
      res.status(400).send('ValidationError: ' + err.message)
      return
    }
    console.error(err)
    res.status(500).send('Error generating image: ' + err.message)
  } finally {
    console.timeEnd(`generateBracketImage ${theme} ${position}`)
  }
})

app.listen(port, host, () => {
  console.log(`Image Generator listening at http://${host}:${port}`)
})

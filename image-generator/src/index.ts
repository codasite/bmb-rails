import os from 'os'
import express, { Request, Response, NextFunction } from 'express'
import puppeteer from 'puppeteer'
import * as Sentry from '@sentry/node'
import { PutObjectCommand, S3Client } from '@aws-sdk/client-s3'
import { generatorImageSchema } from './schema'
import { HEADLESS, HOST, PORT } from './config'

Sentry.init()

if (!process.env.CLIENT_URL) {
  throw new Error('CLIENT_URL not set')
}
if (!process.env.AWS_REGION) {
  throw new Error('AWS_REGION not set')
}

const app = express()
app.use(express.json())
const port = PORT
const host = HOST

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

app.get('/ping-react', async (req, res) => {
  const url = process.env.CLIENT_URL
  try {
    const response = await fetch(url + '/ping')
    const data = await response.text()
    res.send('react says: ' + data)
  } catch (err: any) {
    res.send('error: ' + err.message || 'unknown error')
  }
})

app.get('/ping-react-browser', async (req, res) => {
  const browser = await puppeteer.launch({ headless: 'new' })
  try {
    const page = await browser.newPage()
    await page.goto(process.env.CLIENT_URL || '')
    res.send('opened browser')
  } catch (err: any) {
    res.send('error opening browser: ' + err.message || 'unknown error')
  } finally {
    await browser.close()
  }
})

interface S3StorageOptions {
  bucket: string
  key: string
}

interface ObjectStorageUploader {
  upload: (
    buffer: Buffer,
    contentType: string,
    body: S3StorageOptions
  ) => Promise<string>
}

const s3Uploader: ObjectStorageUploader = {
  upload: async (buffer, contentType, storageOptions) => {
    const s3 = new S3Client({ region: process.env.AWS_REGION })
    const { bucket, key } = storageOptions
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

const takeScreenshot = async (req: GenerateRequest) => {
  const { deviceScaleFactor = 1, inchHeight = 16, inchWidth = 12 } = req

  const url = process.env.CLIENT_URL
  const pxHeight = inchHeight * 96
  const pxWidth = inchWidth * 96
  const browser = await puppeteer.launch({ headless: HEADLESS })

  try {
    const page = await browser.newPage()
    let pageError = null

    await page.setViewport({
      height: pxHeight,
      width: pxWidth,
      deviceScaleFactor,
    })

    const queryString = Object.entries(req.queryParams)
      .map(([key, value]) => {
        if (typeof value === 'object') {
          value = JSON.stringify(value)
        }
        return key + '=' + encodeURIComponent(value as any)
      })
      .join('&')
    const path = url + (queryString ? '?' + queryString : '')
    page.on('pageerror', (err) => {
      pageError = err
    })
    const res = await page.goto(path, { waitUntil: 'networkidle0' })
    if (!res.ok()) {
      throw new Error(
        'Failed to load page. Status: ' + res.status() + ' ' + res.statusText()
      )
    }
    if (pageError) {
      throw new Error('Error in page console: ' + pageError)
    }

    let file: Buffer
    if (req.pdf) {
      file = await page.pdf({
        height: pxHeight,
        width: pxWidth,
        omitBackground: true,
        printBackground: true,
      })
    } else {
      file = await page.screenshot({
        omitBackground: true,
      })
    }

    return file
  } finally {
    await browser.close()
  }
}

const uploadFile = async (file: Buffer, req: GenerateRequest) => {
  const contentType = req.pdf ? 'application/pdf' : 'image/png'
  let uploader: ObjectStorageUploader
  if (req.storageService === 's3') {
    uploader = s3Uploader
  } else {
    throw new Error('Unknown storage service: ' + req.storageService)
  }
  let image_url
  image_url = await uploader.upload(file, contentType, req.storageOptions)
  return image_url
}

app.post(
  '/generate',

  // validate request body
  (req: Request, res: Response, next: NextFunction) => {
    const { error } = generatorImageSchema.validate(req.body)
    if (error) {
      return next(new ValidationError(error.message))
    }
    next()
  },

  // generate image
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      const file = await takeScreenshot(req.body)
      const image_url = await uploadFile(file, req.body)
      res.status(201).send(image_url)
    } catch (err) {
      next(err)
    }
  },

  async (err: Error, req: Request, res: Response, next: NextFunction) => {
    console.error(err)
    // Attempt to close the browser in case of an error, if appropriate
    if (err.name === 'ValidationError') {
      // Return after sending the response to stop execution
      return res.status(400).send('ValidationError: ' + err.message)
    }
    // Use else if to ensure mutually exclusive execution
    else if (err instanceof Error) {
      // Ensure err is an instance of Error to access its message safely
      return res.status(500).send('Error generating image: ' + err.message)
    } else {
      // Handle cases where err might not be an Error instance
      return res.status(500).send('Unknown error')
    }
  }
)

app.listen(port, host, () => {
  console.info(`Image Generator listening at http://${host}:${port}`)
})

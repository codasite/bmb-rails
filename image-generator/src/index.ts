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

app.get('/react', async (req, res) => {
  // http.get('http://react-server:3001/hello', (resp) => {
  //   let data = ''
  //   resp.on('data', (chunk) => {
  //     data += chunk
  //   })
  //   resp.on('end', () => {
  //     console.log(data)
  //     res.send(data)
  //   })
  // }).on('error', (err) => {
  //   console.error(err)
  //   res.send('Error')
  // })
  const browser = await puppeteer.launch({ headless: 'new' })
  const page = await browser.newPage()
  await page.goto('http://react-server:3001/hello', { waitUntil: 'networkidle0' })
  const text = await page.evaluate(() => document.body.textContent + 'this is from server')
  await browser.close()
  res.send(text)
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

    const s3 = new S3Client({ region: process.env.AWS_REGION, credentials: {
      accessKeyId: process.env.AWS_ACCESS_KEY_ID,
      secretAccessKey: process.env.AWS_SECRET_ACCESS_KEY,
     }})
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
  console.log('req', req)
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
  console.log('req.url', req.url )

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

  // const browser = await puppeteer.launch({ headless: 'new' })
  const browser = await puppeteer.launch({ headless: 'new' })
  const page = await browser.newPage()

  await page.setViewport({
    height: pxHeight,
    width: pxWidth,
    deviceScaleFactor,
  })

  const queryString = Object.entries(queryParams)
    .map(([key, value]) => {
      if (typeof value === 'object') {
        value = encodeURIComponent(JSON.stringify(value))
      }
      return key + '=' + encodeURIComponent(value as any)
    })
    .join('&')
  const path = url + (queryString ? '?' + queryString : '')
  try {
    await page.goto(path, { waitUntil: 'networkidle0' })
  } catch (err) {
    console.error(err)
    browser.close()
    throw new Error(`Error loading ${path}`)
  }

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

  const extension = pdf ? 'pdf' : 'png'
  const contentType = pdf ? 'application/pdf' : 'image/png'
  let uploader: ObjectStorageUploader
  if (storageService === 's3') {
    uploader = s3Uploader
  }
  let image_url
  try {
    image_url = await uploader.upload(file, contentType, storageOptions)
  } catch (err: any) {
    console.error(err)
    throw new Error('Error uploading to S3')
  } finally {
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
  console.log('done')
})

app.post('/test', async (req, res) => {
  res.send(req.body)
})

app.listen(port, host, () => {
  console.log(`Image Generator listening at http://${host}:${port}`)
})

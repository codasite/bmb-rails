import { logger } from '../../../utils/Logger'
import { camelCaseKeys, snakeCaseKeys } from '../utils/caseUtils'

export interface RequestOptions {
  method?: string
  body?: any
  snakeCaseBody?: boolean
  camelCaseResponse?: boolean
}

export class HttpError extends Error {
  data: any

  constructor({ message, data }) {
    super(message)
    this.name = 'HttpError'
    this.data = data
  }
}

export class WpHttpClient {
  private baseUrl: string = ''
  private nonce: string = ''
  constructor(baseUrl: string, nonce: string) {
    this.baseUrl = baseUrl
    this.nonce = nonce
  }
  async performRequest(
    path: string,
    options: RequestOptions = {}
  ): Promise<any> {
    let {
      method = 'GET',
      body = {},
      snakeCaseBody = true,
      camelCaseResponse = true,
    } = options
    if (snakeCaseBody) {
      body = snakeCaseKeys(body)
    }
    const request = {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      },
    }
    if (method !== 'GET') {
      request['body'] = JSON.stringify(body)
    } else if (Object.keys(body).length > 0) {
      // pass params as query string
      path +=
        '?' +
        Object.entries(body)
          .map(([key, value]) => `${key}=${value}`)
          .join('&')
    }
    try {
      const response = await fetch(`${this.baseUrl}${path}/`, request)
      if (!response.ok) {
        const contentType = response.headers.get('content-type')
        let jsonData: any = null
        let textData: string = ''
        try {
          if (contentType?.includes('application/json')) {
            jsonData = await response.json()
          } else {
            textData = await response.text()
          }
        } catch (error) {
          console.error('Error parsing response:', error)
        }
        throw new HttpError({
          message: `${response.status}: ${response.statusText} - ${
            textData || JSON.stringify(jsonData)
          }`,
          data: jsonData,
        })
      }
      let responseData = await response.json()
      if (camelCaseResponse) {
        responseData = camelCaseKeys(responseData)
      }
      return responseData
    } catch (error) {
      logger.error(error)
      throw error
    }
  }
}

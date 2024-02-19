import { camelCaseKeys, snakeCaseKeys } from '../utils/caseUtils'
import * as Sentry from '@sentry/react'

export interface RequestOptions {
  method?: string
  body?: any
  snakeCaseBody?: boolean
  camelCaseResponse?: boolean
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
        const text = await response.text()
        throw new Error(
          `HTTP Error ${response.status}: ${response.statusText} - ${text} - ${request['body']}`
        )
      }
      let responseData = await response.json()
      if (camelCaseResponse) {
        responseData = camelCaseKeys(responseData)
      }
      return responseData
    } catch (error) {
      Sentry.captureException(error)
      throw error
    }
  }
}

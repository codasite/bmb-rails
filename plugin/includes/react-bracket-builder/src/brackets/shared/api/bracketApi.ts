import * as Sentry from '@sentry/react'
import { PlayReq, PlayRes, BracketReq, BracketRes } from './types/bracket'

interface RequestOptions {
  method?: string
  body?: any
  snakeCaseBody?: boolean
  camelCaseResponse?: boolean
}
declare var wpbb_ajax_obj: any
class BracketApi {
  private baseUrl: string = ''
  private bracketsPath: string = 'brackets'
  private playsPath: string = 'plays'
  private nonce: string = ''
  constructor() {
    if (typeof wpbb_ajax_obj !== 'undefined') {
      this.baseUrl = wpbb_ajax_obj.rest_url
      this.nonce = wpbb_ajax_obj.nonce
    }
  }
  async createBracket(bracket: BracketReq): Promise<BracketRes> {
    const options: RequestOptions = { method: 'POST', body: bracket }
    return await this.performRequest(this.bracketsPath, options)
  }
  async updateBracket(
    bracketId: number,
    bracket: Partial<BracketReq>
  ): Promise<BracketRes> {
    const options: RequestOptions = { method: 'PATCH', body: bracket }
    return await this.performRequest(
      `${this.bracketsPath}/${bracketId}`,
      options
    )
  }
  async createPlay(play: PlayReq): Promise<PlayRes> {
    const options: RequestOptions = { method: 'POST', body: play }
    return await this.performRequest(this.playsPath, options)
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
      const response = await fetch(`${this.baseUrl}${path}`, request)
      if (!response.ok) {
        const text = await response.text()
        throw new Error(
          `HTTP Error ${response.status}: ${response.statusText} - ${text}`
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

// Utility function to convert snake_case to camelCase
function toCamelCase(str: string): string {
  return str.replace(/([-_][a-z])/g, (group) =>
    group.toUpperCase().replace('-', '').replace('_', '')
  )
}

// Recursive function to convert object keys to camelCase
export function camelCaseKeys(obj: any): any {
  if (Array.isArray(obj)) {
    return obj.map((value) => camelCaseKeys(value))
  } else if (typeof obj === 'object' && obj !== null) {
    return Object.entries(obj).reduce((accumulator: any, [key, value]) => {
      accumulator[toCamelCase(key)] = camelCaseKeys(value)
      return accumulator
    }, {})
  }
  return obj
}

function camelCaseToSnakeCase(str: string): string {
  return str.replace(/[A-Z]/g, (match) => `_${match.toLowerCase()}`)
}

// Recursive function to convert object keys to snake_case
function snakeCaseKeys(obj: any): any {
  if (Array.isArray(obj)) {
    return obj.map((value) => snakeCaseKeys(value))
  } else if (typeof obj === 'object' && obj !== null) {
    return Object.entries(obj).reduce((accumulator: any, [key, value]) => {
      accumulator[camelCaseToSnakeCase(key)] = snakeCaseKeys(value)
      return accumulator
    }, {})
  }
  return obj
}
export const bracketApi = new BracketApi()

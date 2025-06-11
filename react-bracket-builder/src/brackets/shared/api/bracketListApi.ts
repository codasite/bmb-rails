import { RequestOptions, WpHttpClient } from './wpHttpClient'
import { WpbbAppObj } from '../../../utils/WpbbAjax'

declare var wpbb_app_obj: WpbbAppObj

export interface BracketListRequest {
  page?: number
  per_page?: number
  status?: string
  tags?: string[]
}

export interface BracketListResponse {
  html: string
  pagination: {
    current_page: number
    total_pages: number
    total_items: number
    per_page: number
    has_more: boolean
  }
}

export class BracketListApi {
  private bracketsPath: string = 'bracket-list-html'
  private client: WpHttpClient

  constructor() {
    if (typeof wpbb_app_obj !== 'undefined') {
      this.client = new WpHttpClient(wpbb_app_obj.restUrl!, wpbb_app_obj.nonce!)
    }
  }

  async getBracketList(
    params: BracketListRequest = {}
  ): Promise<BracketListResponse> {
    const options: RequestOptions = {
      method: 'GET',
      body: params,
      snakeCaseBody: true,
      camelCaseResponse: true,
    }
    console.log('params', params)

    return await this.client.performRequest(this.bracketsPath, options)
  }
}

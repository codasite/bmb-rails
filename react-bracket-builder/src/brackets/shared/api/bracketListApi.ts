import { RequestOptions, WpHttpClient } from './wpHttpClient'
import { WpbbAppObj } from '../../../utils/WpbbAjax'

declare var wpbb_app_obj: WpbbAppObj

export interface BracketListRequest {
  page?: number
  perPage?: number
  status?: string
  tags?: string[]
}

export interface BracketListResponse {
  html: string
  pagination: {
    currentPage: number
    totalPages: number
    totalItems: number
    perPage: number
    hasMore: boolean
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

    return await this.client.performRequest(this.bracketsPath, options)
  }
}

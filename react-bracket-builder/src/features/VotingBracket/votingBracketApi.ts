import {
  RequestOptions,
  WpHttpClient,
} from '../../brackets/shared/api/wpHttpClient'
import { WpbbAppObj } from '../../utils/WpbbAjax'
declare var wpbb_app_obj: WpbbAppObj
export class VotingBracketApi {
  private bracketsPath: string = 'brackets'
  private client: WpHttpClient
  constructor() {
    if (typeof wpbb_app_obj !== 'undefined') {
      this.client = new WpHttpClient(wpbb_app_obj.restUrl!, wpbb_app_obj.nonce!)
    }
  }

  async completeRound(bracketId: number) {
    const options: RequestOptions = { method: 'POST' }
    return await this.client.performRequest(
      `${this.bracketsPath}/${bracketId}/complete-round`,
      options
    )
  }
}

export const votingBracketApi = new VotingBracketApi()

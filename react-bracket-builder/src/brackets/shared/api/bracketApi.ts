import { BracketReq, BracketRes, PlayReq, PlayRes } from './types/bracket'
import { PaymentIntentReq, PaymentIntentRes } from './types/stripe'
import { NotificationReq } from './types/notification'
import { RequestOptions, WpHttpClient } from './wpHttpClient'

declare var wpbb_app_obj: any
export class BracketApi {
  private bracketsPath: string = 'brackets'
  private notificationsPath: string = 'notifications'
  private playPath: string = 'plays'
  private client: WpHttpClient
  constructor() {
    if (typeof wpbb_app_obj !== 'undefined') {
      this.client = new WpHttpClient(wpbb_app_obj.rest_url, wpbb_app_obj.nonce)
    }
  }
  async removeNotification(notificationId: number): Promise<boolean> {
    const options: RequestOptions = { method: 'DELETE' }
    return await this.client.performRequest(
      `${this.notificationsPath}/${notificationId}`,
      options
    )
  }

  async createNotification(
    notification: Partial<NotificationReq>
  ): Promise<NotificationReq> {
    const options: RequestOptions = { method: 'POST', body: notification }
    return await this.client.performRequest(this.notificationsPath, options)
  }
  async createBracket(bracket: BracketReq): Promise<BracketRes> {
    const options: RequestOptions = { method: 'POST', body: bracket }
    return await this.client.performRequest(this.bracketsPath, options)
  }
  async updateBracket(
    bracketId: number,
    bracket: Partial<BracketReq>
  ): Promise<BracketRes> {
    const options: RequestOptions = { method: 'PATCH', body: bracket }
    return await this.client.performRequest(
      `${this.bracketsPath}/${bracketId}`,
      options
    )
  }
  async deleteBracket(bracketId: number): Promise<boolean> {
    const options: RequestOptions = { method: 'DELETE' }
    return await this.client.performRequest(
      `${this.bracketsPath}/${bracketId}`,
      options
    )
  }
  async createPlay(play: PlayReq): Promise<PlayRes> {
    const options: RequestOptions = { method: 'POST', body: play }
    return await this.client.performRequest(this.playPath, options)
  }
  async getPlay(playId: number): Promise<PlayRes> {
    return await this.client.performRequest(`${this.playPath}/${playId}`)
  }
  async generatePlayImages(playId: number): Promise<PlayRes> {
    const options: RequestOptions = { method: 'POST' }
    return await this.client.performRequest(
      `${this.playPath}/${playId}/generate-images`,
      options
    )
  }
  async createStripePaymentIntent(
    req: PaymentIntentReq
  ): Promise<PaymentIntentRes> {
    const options: RequestOptions = { method: 'POST', body: req }
    return await this.client.performRequest(`stripe/payment-intent`, options)
  }
  async getStripeOnboardingLink(): Promise<string> {
    const options: RequestOptions = { method: 'POST' }
    return await this.client.performRequest(`stripe/onboarding-link`, options)
  }
}

export const bracketApi = new BracketApi()

import { BracketReq, BracketRes, PlayReq, PlayRes } from './types/bracket'
import { RequestOptions, RailsHttpClient } from './railsHttpClient'

export class RailsBracketApi {
  private bracketsPath: string = 'brackets'
  private playPath: string = 'plays'
  private client: RailsHttpClient
  
  constructor(baseUrl?: string) {
    this.client = new RailsHttpClient(baseUrl)
  }

  async createBracket(bracket: BracketReq): Promise<BracketRes> {
    const options: RequestOptions = { 
      method: 'POST', 
      body: { bracket: bracket }
    }
    return await this.client.performRequest(this.bracketsPath, options)
  }
  
  async getBrackets(): Promise<BracketRes[]> {
    return await this.client.performRequest(this.bracketsPath)
  }
  
  async getBracket(bracketId: number): Promise<BracketRes> {
    return await this.client.performRequest(`${this.bracketsPath}/${bracketId}`)
  }
  
  async updateBracket(
    bracketId: number,
    bracket: Partial<BracketReq>
  ): Promise<BracketRes> {
    const options: RequestOptions = { 
      method: 'PATCH', 
      body: { bracket: bracket }
    }
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
    const options: RequestOptions = { 
      method: 'POST', 
      body: { play: play }
    }
    return await this.client.performRequest(this.playPath, options)
  }
  
  async getPlay(playId: number): Promise<PlayRes> {
    return await this.client.performRequest(`${this.playPath}/${playId}`)
  }
  
  async updatePlay(playId: number, play: PlayReq): Promise<PlayRes> {
    const options: RequestOptions = { 
      method: 'PATCH', 
      body: { play: play }
    }
    return await this.client.performRequest(
      `${this.playPath}/${playId}`,
      options
    )
  }
}

export const railsBracketApi = new RailsBracketApi()

import { PlayRes } from '../api/types/bracket'
import { bracketApi } from '../api/bracketApi'

export class PlayCache {
  static PLAY_ID_COOKIE_NAME = 'play_id'

  setCachedPlayId(playId: number) {
    const expiryDate = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000) // 30 days from now
    document.cookie = `${
      PlayCache.PLAY_ID_COOKIE_NAME
    }=${playId}; path=/; expires=${expiryDate.toUTCString()}`
  }

  getCachedPlayId(): number | null {
    const cookie = document.cookie
      .split(';')
      .find((cookie) => cookie.includes(PlayCache.PLAY_ID_COOKIE_NAME))
    if (cookie) {
      return parseInt(cookie.split('=')[1])
    }
    return null
  }

  async getCachedPlay(): Promise<PlayRes | null> {
    const playId = this.getCachedPlayId()
    if (!playId) {
      return null
    }
    const play = await bracketApi.getPlay(playId)
    return play
  }
}

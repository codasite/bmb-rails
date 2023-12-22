import { PlayCache } from '../../shared/storages/PlayCache'
import { bracketApi } from '../../shared/api/bracketApi'

export const addToApparelHandler = async (
  playId: number,
  redirectUrl: string
) => {
  if (playId) {
    await bracketApi.generatePlayImages(playId)
    const playCache = new PlayCache()
    playCache.setCachedPlayId(playId)
  }
  window.location.href = redirectUrl
}

import { PlayCache } from '../../shared/storages/PlayCache'
export const addToApparelHandler = (playId: number, redirectUrl: string) => {
  if (playId) {
    const playCache = new PlayCache()
    playCache.setCachedPlayId(playId)
  }
  window.location.href = redirectUrl
}

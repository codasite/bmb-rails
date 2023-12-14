import { PlayReq } from '../api/types/bracket'

export class PlayStorage {
  private _searchParam: string
  private _storageKeyPrefix: string
  constructor(searchParam: string, storageKeyPrefix: string) {
    this._searchParam = searchParam
    this._storageKeyPrefix = storageKeyPrefix
  }

  loadPlay(bracketId: number): PlayReq | null {
    const shouldLoad = new URLSearchParams(window.location.search).get(
      this._searchParam
    )
    if (!shouldLoad) {
      return null
    }
    const storedPlay = sessionStorage.getItem(
      `${this._storageKeyPrefix}${bracketId}`
    )
    if (storedPlay) {
      return JSON.parse(storedPlay)
    }
    return null
  }

  storePlay(play: PlayReq, bracketId: number) {
    sessionStorage.setItem(
      `${this._storageKeyPrefix}${bracketId}`,
      JSON.stringify(play)
    )
    const currentUrl = new URL(window.location.href)
    currentUrl.searchParams.set(this._searchParam, 'true')
    window.history.replaceState(null, '', currentUrl.href)
  }
}

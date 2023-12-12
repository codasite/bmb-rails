import { MatchTree } from '../../shared/models/MatchTree'

export class MatchTreeStorage {
  private _searchParam: string
  private _storageKeyPrefix: string
  constructor(searchParam: string, storageKeyPrefix: string) {
    this._searchParam = searchParam
    this._storageKeyPrefix = storageKeyPrefix
  }

  loadMatchTree(bracketId: number): MatchTree | null {
    const loadStoredPicks = new URLSearchParams(window.location.search).get(
      this._searchParam
    )
    if (!loadStoredPicks) {
      return null
    }
    const storedMatchTree = sessionStorage.getItem(
      `${this._storageKeyPrefix}${bracketId}`
    )
    if (storedMatchTree) {
      return MatchTree.deserialize(JSON.parse(storedMatchTree))
    }
  }

  storeMatchTree(tree: MatchTree, bracketId: number) {
    sessionStorage.setItem(
      `${this._storageKeyPrefix}${bracketId}`,
      JSON.stringify(tree.serialize())
    )
    const currentUrl = new URL(window.location.href)
    currentUrl.searchParams.set(this._searchParam, 'true')
    window.history.replaceState(null, '', currentUrl.href)
  }
}

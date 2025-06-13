export interface MoreOptionsConfig {
  mostPopularPicks: boolean
  shareBracket: boolean
  editBracket: boolean
  setFee: boolean
  duplicateBracket: boolean
  lockTournament: boolean
  deleteBracket: boolean
}

export interface BracketData {
  id?: number
  title?: string
  month?: string
  year?: string
  fee?: number
  playBracketUrl?: string
  copyBracketUrl?: string
  mostPopularPicksUrl?: string
  goLiveUrl?: string
  liveRoundIndex?: number
  isFinalRound?: boolean
  notificationId?: number
  moreOptions?: MoreOptionsConfig
}

import { Nullable } from '../../../../utils/types'
import { WildcardPlacement } from '../../models/WildcardPlacement'

interface phpDate {
  date: string
  timezone_type: number
  timezone: string
}

export interface TeamRes {
  id?: number
  name: string
}

export interface TeamReq {
  name: string
}

export interface MatchRes {
  id?: number
  roundIndex: number
  matchIndex: number
  team1?: Nullable<TeamRes>
  team2?: Nullable<TeamRes>
}

export interface MatchReq {
  roundIndex: number
  matchIndex: number
  team1?: TeamReq
  team2?: TeamReq
}

export interface MatchPick {
  roundIndex: number
  matchIndex: number
  winningTeamId: number
  percentage?: number
}

export interface TeamRepr {
  id?: number
  name: string
}

export interface MatchTreeRepr {
  rounds: Nullable<MatchRepr>[][]
  wildcardPlacement?: WildcardPlacement
}
export interface MatchRepr {
  id?: number
  roundIndex: number
  matchIndex: number
  team1?: TeamRepr
  team2?: TeamRepr
  team1Wins?: boolean
  team2Wins?: boolean
  pick?: MatchPick
}
export interface PostBase {
  id: number
  title: string
  status: string
  author: number
  authorDisplayName: string
  publishedDate: phpDate
  thumbnailUrl?: string
  url?: string
}
export interface BracketReq {
  title: string
  month: string
  year: string
  numTeams: number
  status?: string
  wildcardPlacement: WildcardPlacement
  matches: MatchReq[]
  results?: MatchPick[]
  updateNotifyPlayers?: boolean
  fee?: number
  isVoting?: boolean
}
export interface BracketRes extends PostBase {
  month: string
  year: string
  numTeams: number
  wildcardPlacement: WildcardPlacement
  matches?: MatchRes[]
  isOpen?: boolean
  isPrintable?: boolean
  fee?: number
  results?: MatchPick[]
  mostPopularPicks?: MatchPick[]
  isVoting?: boolean
}
export interface PlayReq {
  id?: number
  bracketId?: number
  title?: string
  status?: string
  picks: MatchPick[]
  bustedId?: number
  generateImages?: boolean
  createStripePaymentIntent?: boolean
}
export interface PlayRes extends PostBase {
  bracketId: number
  picks: MatchPick[]
  bracket?: BracketRes
  bustedId?: number
  bustedPlay?: PlayRes
  isBustable?: boolean
  stripePaymentIntentClientSecret?: string
  stripePaymentAmount?: number
  accuracyScore?: number
  totalScore?: number
}

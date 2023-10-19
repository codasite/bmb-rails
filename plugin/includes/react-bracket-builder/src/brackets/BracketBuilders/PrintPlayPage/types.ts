import { MatchPicks, MatchRes } from '../../shared/api/types/bracket'

export interface PrintSchema {
  queryName: string
  localName?: string
  type: string
  default: any
}

export interface PrintParams {
  theme?: string
  position?: string
  inchHeight?: number
  inchWidth?: number
  title?: string
  date?: string
  picks?: MatchPicks[]
  matches?: MatchRes[]
  numTeams?: number
}

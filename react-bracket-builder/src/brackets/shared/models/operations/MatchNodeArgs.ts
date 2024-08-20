import { Nullable } from '../../../../utils/types'
import { MatchPick } from '../../api'
import { Team } from '../Team'
import { MatchNode } from './MatchNode'

export interface MatchNodeArgs {
  id?: number
  matchIndex: number
  roundIndex: number
  team1?: Nullable<Team>
  team2?: Nullable<Team>
  team1Wins?: boolean
  team2Wins?: boolean
  left?: Nullable<MatchNode>
  right?: Nullable<MatchNode>
  parent?: Nullable<MatchNode>
  depth: number
  pick?: MatchPick
}

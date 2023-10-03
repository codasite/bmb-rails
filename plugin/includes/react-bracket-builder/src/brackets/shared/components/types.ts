import { Round, MatchNode, Team } from '../models/MatchTree'
import { Nullable } from '../../../utils/types'
import { MatchTree } from '../models/MatchTree'
import { Match } from '@sentry/react/types/reactrouterv3'
import { ActionButtonProps } from './ActionButtons'

export interface TeamSlotProps {
  team?: Nullable<Team>
  match: MatchNode
  matchPosition?: string
  teamPosition?: string
  height?: number
  width?: number
  fontSize?: number
  fontWeight?: number
  textColor?: string
  backgroundColor?: string
  borderColor?: string
  matchTree: MatchTree
  onTeamClick?: (match: MatchNode, team?: Nullable<Team>) => void
  setMatchTree?: (matchTree: MatchTree) => void
  getTeamClass?: (
    roundIndex: number,
    matchIndex: number,
    position: string
  ) => string
  children?: React.ReactNode
}

export interface MatchBoxProps {
  match: Nullable<MatchNode>
  matchPosition: string
  matchTree: MatchTree
  teamGap?: number
  teamHeight?: number
  teamWidth?: number
  teamFontSize?: number
  setMatchTree?: (matchTree: MatchTree) => void
  TeamSlotComponent?: React.FC<TeamSlotProps>
  onTeamClick?: (match: MatchNode, team?: Nullable<Team>) => void
  MatchBoxChildComponent?: React.FC<MatchBoxChildProps>
}

export interface MatchColumnProps {
  matches: Nullable<MatchNode>[]
  matchPosition: string
  teamGap?: number
  teamHeight?: number
  teamWidth?: number
  teamFontSize?: number
  matchGap?: number
  matchTree: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  MatchBoxComponent?: React.FC<MatchBoxProps>
  MatchBoxChildComponent?: React.FC<MatchBoxChildProps>
  TeamSlotComponent?: React.FC<TeamSlotProps>
  onTeamClick?: (match: MatchNode, team?: Nullable<Team>) => void
}

export interface BracketProps {
  getBracketHeight?: (numRounds: number) => number
  getBracketWidth?: (numRounds: number) => number
  getTeamGap?: (depth: number) => number
  getTeamHeight?: (numRounds: number) => number
  getTeamWidth?: (numRounds: number) => number
  getTeamFontSize?: (numRounds: number) => number
  getFirstRoundMatchGap?: (numRounds: number) => number
  getSubsequentMatchGap?: (numRounds: number) => number
  setMatchTree?: (matchTree: MatchTree) => void
  onTeamClick?: (match: MatchNode, team?: Nullable<Team>) => void
  matchTree: MatchTree
  MatchColumnComponent?: React.FC<MatchColumnProps>
  MatchBoxComponent?: React.FC<MatchBoxProps>
  MatchBoxChildComponent?: React.FC<MatchBoxChildProps>
  TeamSlotComponent?: React.FC<TeamSlotProps>
  BracketComponent?: React.FC<BracketProps> // for nesting brackets
  title?: string
  date?: string
  darkMode?: boolean
  lineStyle?: object
  lineColor?: string
  lineWidth?: number
  darkLineColor?: string
}

export interface BracketActionButtonProps extends ActionButtonProps {
  matchTree?: MatchTree
}

export interface PaginatedBracketProps extends BracketProps {
  onFinished?: () => void
  NextButtonComponent?: React.FC<ActionButtonProps>
  FinalButtonComponent?: React.FC<ActionButtonProps>
}

export interface ScaledBracketProps extends BracketProps {
  scale?: number
}

export interface MatchBoxChildProps {
  match: MatchNode
  matchTree: MatchTree
  matchPosition?: string
  TeamSlotComponent?: React.FC<TeamSlotProps>
}

export type HOC = (Component: React.FC<any>) => React.FC<any>

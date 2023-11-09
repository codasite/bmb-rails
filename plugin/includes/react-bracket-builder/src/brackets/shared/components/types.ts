import { MatchTree } from '../models/MatchTree'
import { Nullable } from '../../../utils/types'
import { ActionButtonProps } from './ActionButtons'
import { Team } from '../models/Team'
import { MatchNode } from '../models/operations/MatchNode'

type TeamClickCallback = (
  match: MatchNode,
  position: string,
  team?: Nullable<Team>
) => void

type TeamClickDisabledCallback = (
  match: MatchNode,
  position: string,
  team?: Nullable<Team>
) => boolean

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
  textPaddingX?: number
  backgroundColor?: string
  borderColor?: string
  borderWidth?: number
  matchTree: MatchTree
  getFontSize?: (numRounds: number) => number
  onTeamClick?: TeamClickCallback
  onTeamFocus?: TeamClickCallback
  teamClickDisabled?: TeamClickDisabledCallback
  setMatchTree?: (matchTree: MatchTree) => void
  getTeamClass?: (
    roundIndex: number,
    matchIndex: number,
    position: string
  ) => string
  children?: React.ReactNode
  placeholder?: React.ReactNode
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
  onTeamClick?: TeamClickCallback
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
  onTeamClick?: TeamClickCallback
}

export interface BracketProps {
  getBracketHeight?: (numRounds: number) => number
  getBracketWidth?: (numRounds: number) => number
  getTeamGap?: (depth: number) => number
  getTeamHeight?: (numRounds: number) => number
  getTeamWidth?: (numRounds: number) => number
  getFirstRoundMatchGap?: (numRounds: number) => number
  getSubsequentMatchGap?: (
    prevMatchHeight: number,
    prevMatchGap: number,
    matchHeight: number
  ) => number
  setMatchTree?: (matchTree: MatchTree) => void
  onTeamClick?: TeamClickCallback
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
  // undefined means all columns
  columnsToRender?: number[]
  renderWinnerAndLogo?: boolean
}

export interface BracketActionButtonProps extends ActionButtonProps {
  matchTree?: MatchTree
}

export interface PaginatedBracketProps extends BracketProps {
  onFinished?: () => void
  NavButtonsComponent?: React.FC<PaginatedNavButtonsProps>
}

export interface PaginatedNavButtonsProps {
  PrevButtonComponent?: React.FC<ActionButtonProps>
  NextButtonComponent?: React.FC<ActionButtonProps>
  FullBracketBtnComponent?: React.FC<ActionButtonProps>
  FinalButtonComponent?: React.FC<ActionButtonProps>
  disablePrev?: boolean
  disableNext?: boolean
  hasNext?: boolean
  onPrev?: () => void
  onNext?: () => void
  onFinished?: () => void
  onFullBracket?: () => void
}

// why is this different from PaginatedBracketProps?
export interface PaginatedDefaultBracketProps extends PaginatedBracketProps {
  page: number
  setPage: (page: number) => void
  disableNext?: (currentRoundMatches: Array<Nullable<MatchNode>>) => boolean
  forcePageAllPicked?: boolean
}

export interface ScaledBracketProps extends BracketProps {
  scale?: number
  windowWidth?: number
  paddingX?: number
  paddingY?: number
}

export interface MatchBoxChildProps {
  match: MatchNode
  matchTree: MatchTree
  matchPosition?: string
  TeamSlotComponent?: React.FC<TeamSlotProps>
}

export type HOC = (Component: React.FC<any>) => React.FC<any>

import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context'

export interface PlayBuilderProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
  handleApparelClick?: () => void
  processing?: boolean
}

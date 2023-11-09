import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context/context'

export interface BustPlayBuilderProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
  processing?: boolean
  handleSubmit?: () => void
}

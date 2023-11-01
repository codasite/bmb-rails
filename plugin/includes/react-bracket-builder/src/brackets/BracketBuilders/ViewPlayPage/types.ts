import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context'

export interface ViewPlayPageProps {
  bracketMeta: BracketMeta
  setBracketMeta: (bracketMeta: BracketMeta) => void
  matchTree: MatchTree
  setMatchTree: (matchTree: MatchTree) => void
  bracketPlay: any
  apparelUrl: string
  darkMode: boolean
  setDarkMode: (darkMode: boolean) => void
}

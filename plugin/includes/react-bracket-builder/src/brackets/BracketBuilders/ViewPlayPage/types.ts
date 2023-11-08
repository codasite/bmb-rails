import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context/context'
import { PlayRes } from '../../shared/api/types/bracket'

export interface ViewPlayPageProps {
  bracketMeta: BracketMeta
  setBracketMeta: (bracketMeta: BracketMeta) => void
  matchTree: MatchTree
  setMatchTree: (matchTree: MatchTree) => void
  bracketPlay: PlayRes
  redirectUrl: string
  darkMode: boolean
  setDarkMode: (darkMode: boolean) => void
}

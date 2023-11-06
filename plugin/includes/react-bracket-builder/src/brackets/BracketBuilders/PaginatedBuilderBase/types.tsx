import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context'

export interface PaginatedBuilderProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  // This is the tree used to render the current page. Defaults to the props.matchTree
  // The buster bracket uses a different tree to determine the current page
  pagedTree?: MatchTree
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
  processing?: boolean
  handleSubmit?: () => void
  StartPageComponent?: React.FC<StartPageProps>
  BracketPagesComponent: React.FC<BracketPagesProps>
  EndPageComponent: React.FC<EndPageProps>
}

export interface StartPageProps {
  matchTree?: MatchTree
  onStart: () => void
}

export interface BracketPagesProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  onFinished: () => void
}

export interface EndPageProps {
  matchTree?: MatchTree
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
  processing?: boolean
  handleSubmit?: () => void
  onEditClick?: () => void
  canEdit?: boolean
}

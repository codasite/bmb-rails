import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context/context'

export interface PaginatedBuilderProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
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
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
  processing?: boolean
  handleSubmit?: () => void
  onEditClick?: () => void
  canEdit?: boolean
}

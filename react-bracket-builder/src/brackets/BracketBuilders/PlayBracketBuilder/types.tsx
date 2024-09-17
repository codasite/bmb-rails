import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context/context'

export interface PlayBuilderProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
  handleApparelClick?: () => Promise<void>
  handleSubmitPicksClick?: () => Promise<void>
  processingAddToApparel?: boolean
  addToApparelError?: boolean
  processingSubmitPicks?: boolean
  submitPicksError?: boolean
  canPlay?: boolean
  showRegisterModal?: boolean
  setShowRegisterModal?: (showRegisterModal: boolean) => void
  showPaymentModal?: boolean
  setShowPaymentModal?: (showPaymentModal: boolean) => void
}

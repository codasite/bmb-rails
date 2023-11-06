import { createContext } from 'react'

export interface BracketResultsBuilderContextState {
  notifyParticipants?: boolean
  toggleNotifyParticipants?: () => void
}

export const BracketResultsBuilderContext =
  createContext<BracketResultsBuilderContextState>({})

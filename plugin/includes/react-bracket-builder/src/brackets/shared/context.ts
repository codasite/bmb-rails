import { createContext } from 'react'
import { MatchTree } from './models/MatchTree'

export interface BracketMeta {
  title?: string
  date?: string
}

export interface MatchTreeContextState {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
}

export const MatchTreeContext = createContext<MatchTreeContextState>({})
export const DarkModeContext = createContext(false)
export const BracketMetaContext = createContext<BracketMeta>({})

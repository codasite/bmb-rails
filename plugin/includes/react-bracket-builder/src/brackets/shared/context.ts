import { createContext } from 'react'
import { MatchTree } from './models/MatchTree'

export interface BracketMeta {
  title?: string
  date?: string
}

export const DarkModeContext = createContext(false)
export const BracketMetaContext = createContext<BracketMeta>({})
export const CallbackContext = createContext<() => void>(() => {})

export interface MatchTreeContextState {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
}

export const BusterMatchTreeContext = createContext<MatchTreeContextState>({})
export const BusteeMatchTreeContext = createContext<MatchTreeContextState>({})

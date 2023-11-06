import { createContext } from 'react'

interface BracketBusterContext {
  busteeDisplayName?: string
  busteeThumbnail?: string
  busterDisplayName?: string
  busterThumbnail?: string
}

export const BracketBusterContext = createContext<BracketBusterContext>({})

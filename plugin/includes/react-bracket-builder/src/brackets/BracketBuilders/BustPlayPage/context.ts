import { createContext } from 'react'

interface BracketBusterContext {
  busteeDisplayName?: string
  busteeThumbnail?: string
  busterDisplayName?: string
  busterThumbnail?: string
  buttonText?: string
}

export const BracketBusterContext = createContext<BracketBusterContext>({})

import { Round } from '../Round'
import { MatchNode } from './MatchNode'

export const getParent = (
  matchIndex: number,
  roundIndex: number,
  rounds: Round[]
): MatchNode | null => {
  if (roundIndex === rounds.length - 1) {
    // last round does not have a parent
    return null
  }
  const parentIndex = Math.floor(matchIndex / 2)
  return rounds[roundIndex + 1].matches[parentIndex]
}

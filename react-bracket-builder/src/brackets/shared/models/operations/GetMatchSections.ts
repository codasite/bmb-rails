import { Round } from '../Round'
import { Nullable } from '../../../../utils/types'
import { MatchNode } from './MatchNode'

export function getFirstMatches(rounds: Round[]): Nullable<MatchNode>[][] {
  if (rounds.length < 7) {
    return []
  }
  return [rounds[0].matches]
}

export function getSideMatches(rounds: Round[]): {
  left: Nullable<MatchNode>[][]
  right: Nullable<MatchNode>[][]
} {
  const sideRounds = rounds.slice(0, rounds.length - 1)
  return sideRounds.reduce(
    (matches, round) => ({
      left: [...matches.left, round.matches.slice(0, round.matches.length / 2)],
      right: [round.matches.slice(round.matches.length / 2), ...matches.right],
    }),
    { left: [], right: [] }
  )
}

export function getFinalMatches(rounds: Round[]): Nullable<MatchNode>[][] {
  return [rounds[rounds.length - 1].matches]
}

import { Round } from '../Round'
import { Nullable } from '../../../../utils/types'
import { MatchNode } from './MatchNode'

export function getFirstMatches(rounds: Round[]): MatchNode[] {
  if (rounds.length < 7) {
    return []
  }
  return rounds[0].matches.filter((match) => match !== null)
}

export function getSideMatches(rounds: Round[]): {
  left: Nullable<MatchNode>[][]
  right: Nullable<MatchNode>[][]
} {
  const sideRounds = rounds.slice(rounds.length < 7 ? 0 : 1, rounds.length - 1)
  return sideRounds.reduce(
    (matches, round) => ({
      left: [...matches.left, round.matches.slice(0, round.matches.length / 2)],
      right: [round.matches.slice(round.matches.length / 2), ...matches.right],
    }),
    { left: [], right: [] }
  )
}

export function getFinalMatches(rounds: Round[]): Nullable<MatchNode>[] {
  return rounds[rounds.length - 1].matches
}

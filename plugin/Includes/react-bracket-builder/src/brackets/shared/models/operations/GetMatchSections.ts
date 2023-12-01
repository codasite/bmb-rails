import { Round } from '../Round'
import { Nullable } from '../../../../utils/types'
import { MatchNode } from './MatchNode'

export function getLeftMatches(rounds: Round[]): Nullable<MatchNode>[][] {
  const sideMatches = rounds.slice(0, rounds.length - 1)
  return sideMatches.map((round) =>
    round.matches.slice(0, round.matches.length / 2)
  )
}

export function getRightMatches(rounds: Round[]): Nullable<MatchNode>[][] {
  const sideMatches = rounds.slice(0, rounds.length - 1)
  return sideMatches.map((round) =>
    round.matches.slice(round.matches.length / 2)
  )
}

export function getFinalMatches(rounds: Round[]): Nullable<MatchNode>[][] {
  return [rounds[rounds.length - 1].matches]
}

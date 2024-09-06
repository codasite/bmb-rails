import { MatchRepr, MatchRes } from '../../api/types/bracket'
import { getNullMatches } from './GetNullMatches'
import { Nullable } from '../../../../utils/types'
import { fillInEmptyMatches } from './FillInEmptyMatches'

export const matchReprFromRes = (numRounds: number, matches: MatchRes[]) => {
  const nullableMatches = getNullMatches(numRounds) as Nullable<MatchRepr>[][]
  for (const match of matches) {
    if (match.roundIndex >= nullableMatches.length) {
      throw new Error(
        `Invalid round index ${match.roundIndex} for match ${JSON.stringify(
          match
        )}`
      )
    }
    if (match.matchIndex >= nullableMatches[match.roundIndex].length) {
      throw new Error(
        `Invalid match index ${match.matchIndex} for match ${JSON.stringify(
          match
        )}`
      )
    }
    // Filter out null teams
    const repr = Object.entries(match).reduce((rep, [key, value]) => {
      if (value === null) {
        return rep
      }
      return {
        ...rep,
        [key]: value,
      }
    }, {} as MatchRepr)
    nullableMatches[match.roundIndex][match.matchIndex] = repr
  }
  const filledMatches = fillInEmptyMatches(nullableMatches)
  return filledMatches
}

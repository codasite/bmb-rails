import { WildcardPlacement } from '../WildcardPlacement'
import { Nullable } from '../../../../utils/types'
import { MatchRepr } from '../../api/types/bracket'
import { getFirstRoundMatches } from './GetFirstRoundMatches'
import { getNumRounds } from './GetNumRounds'

export const matchReprFromNumTeams = (
  numTeams: number,
  wildcardPlacement: WildcardPlacement = WildcardPlacement.Top
): Nullable<MatchRepr>[][] => {
  const numRounds = getNumRounds(numTeams)
  const rounds = Array.from({ length: numRounds }).map((round, roundIndex) => {
    const depth = numRounds - roundIndex - 1
    const maxMatches = 2 ** depth
    if (roundIndex === 0) {
      const matches = getFirstRoundMatches(
        numTeams,
        wildcardPlacement,
        maxMatches
      )
      return matches
    } else {
      const matches = Array.from({ length: maxMatches }).map(
        (match, matchIndex) => {
          return { roundIndex: roundIndex, matchIndex: matchIndex }
        }
      )
      return matches
    }
  })
  return rounds
}

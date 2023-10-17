import { WildcardPlacement } from '../WildcardPlacement'
import { Nullable } from '../../../../utils/types'
import { MatchRepr } from '../../api/types/bracket'
import { getWildcardRange } from './GetWildcardRange'
import { getNumRounds } from './GetNumRounds'

export const getFirstRoundMatches = (
  numTeams: number,
  wildcardPlacement?: WildcardPlacement,
  maxMatches?: number
): Nullable<MatchRepr>[] => {
  // This somehow works to get the number of matches that are not null in the first round
  if (!maxMatches) {
    maxMatches = 2 ** (getNumRounds(numTeams) - 1)
  }
  if (!wildcardPlacement) {
    wildcardPlacement = WildcardPlacement.Top
  }
  const matchCount = numTeams - maxMatches
  const leftSideCount = Math.ceil(matchCount / 2)
  const rightSideCount = Math.floor(matchCount / 2)
  const leftRange = getWildcardRange(
    0,
    maxMatches / 2,
    leftSideCount,
    wildcardPlacement
  )
  const rightRange = getWildcardRange(
    maxMatches / 2,
    maxMatches,
    rightSideCount,
    wildcardPlacement
  )
  const ranges = [...leftRange, ...rightRange]
  const matches = Array.from({ length: maxMatches }).map(
    (match, matchIndex) => {
      const inRange = ranges.some((range) => {
        return matchIndex >= range.min && matchIndex < range.max
      })
      if (!inRange) {
        return null
      }
      return { roundIndex: 0, matchIndex: matchIndex }
    }
  )
  return matches
}

import { MatchPick } from '../../brackets/shared/api/types/bracket'

export default function mergePicksFromPlayAndResults(
  results: MatchPick[],
  picks: MatchPick[],
  liveRoundIndex: number
) {
  if (!picks || picks.length === 0) {
    return results
  }
  const liveRoundPicks = picks.filter(
    (pick) => pick.roundIndex === liveRoundIndex
  )
  return [...results, ...liveRoundPicks]
}

import { BracketRes } from '../api/types/bracket'
import { MatchTree } from '../models/MatchTree'

export const loadBracketMPP = (
  bracket: BracketRes,
  setMatchTree: (matchTree: MatchTree) => void
) => {
  const picks = bracket?.mostPopularPicks
  const matches = bracket?.matches
  const numTeams = bracket?.numTeams
  if (picks && matches && numTeams) {
    const tree = MatchTree.fromPicks(numTeams, matches, picks)
    if (tree) {
      setMatchTree(tree)
    }
  }
}

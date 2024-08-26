import { PlayRes } from '../api/types/bracket'
import { MatchTree } from '../models/MatchTree'

export const loadPlay = (
  play: PlayRes,
  setMatchTree: (matchTree: MatchTree) => void
) => {
  const picks = play?.picks
  const bracket = play?.bracket
  const matches = bracket?.matches
  const numTeams = bracket?.numTeams
  if (picks && matches && numTeams) {
    const tree = MatchTree.fromPicks(bracket, picks)
    if (tree) {
      setMatchTree(tree)
    }
  }
}

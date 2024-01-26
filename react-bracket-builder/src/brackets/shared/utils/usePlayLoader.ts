import { useEffect } from 'react'
import { getBracketMeta } from '../components/Bracket/utils'
import { MatchTree } from '../models/MatchTree'
import { PlayRes } from '../api/types/bracket'
import { BracketMeta } from '../context/context'

export const usePlayLoader = (
  play: PlayRes,
  setBracketMeta: (bracketMeta: BracketMeta) => void,
  setMatchTree: (matchTree: MatchTree) => void
) => {
  useEffect(() => {
    const picks = play?.picks
    const meta = getBracketMeta(play?.bracket)
    setBracketMeta(meta)
    const bracket = play?.bracket
    const matches = bracket?.matches
    const numTeams = bracket?.numTeams
    if (picks && matches && numTeams) {
      const tree = MatchTree.fromPicks(numTeams, matches, picks)
      if (tree) {
        setMatchTree(tree)
      }
    }
  }, [play, setBracketMeta, setMatchTree])
}

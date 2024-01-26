import { PlayRes } from '../api/types/bracket'
import { getBracketMeta } from '../components/Bracket/utils'
import { BracketMeta } from '../context/context'

export const loadPlayMeta = (
  play: PlayRes,
  setBracketMeta: (bracketMeta: BracketMeta) => void
) => {
  const bracketTitle = play?.bracket?.title
  const authorDisplayName = play?.authorDisplayName
  const meta = getBracketMeta(play?.bracket)
  const title = authorDisplayName
    ? `${authorDisplayName}'s ${bracketTitle} picks`
    : `${bracketTitle} picks`
  setBracketMeta({ ...meta, title })
}

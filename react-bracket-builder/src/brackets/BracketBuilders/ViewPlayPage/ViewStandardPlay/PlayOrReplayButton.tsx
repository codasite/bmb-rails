import { GreenLink } from '../../GreenLink'
import {
  PlayIcon,
  getPlayBracketUrl,
  PlayRes,
  getReplayPlayUrl,
} from '../../../shared'
import { wpbbAjax } from '../../../../utils/WpbbAjax'

export const PlayOrReplayButton = (props: { play?: PlayRes }) => {
  const bracket = props.play?.bracket
  const { isUserPlayAuthor } = wpbbAjax.getAppObj()

  if (!bracket?.isOpen) {
    return null
  }

  const url = isUserPlayAuthor
    ? getReplayPlayUrl(props.play)
    : getPlayBracketUrl(bracket)
  const text = isUserPlayAuthor ? 'Replay Tournament' : 'Play Tournament'

  return url ? (
    <GreenLink href={url}>
      <PlayIcon className="tw-h-16 sm:tw-h-24" />
      {text}
    </GreenLink>
  ) : null
}

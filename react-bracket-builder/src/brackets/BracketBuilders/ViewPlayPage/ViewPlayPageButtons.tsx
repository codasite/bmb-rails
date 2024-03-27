import { AddToApparel } from '../AddToApparel'
import { BustPicksButton } from '../BustPicksButton'
import { GreenLink } from '../GreenLink'
import {
  EyeIcon,
  PlayIcon,
  getPlayBracketUrl,
  getBracketResultsUrl,
  PlayRes,
  getReplayPlayUrl,
} from '../../shared'
import { addExistingPlayToApparelHandler } from './addExistingPlayToApparel'
import { wpbbAjax } from '../../../utils/WpbbAjax'
import { useState } from 'react'

const PlayOrReplayButton = (props: { play?: PlayRes }) => {
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

export const ViewPlayPageButtons = (props: { play?: PlayRes }) => {
  const [apparelError, setApparelError] = useState(false)
  const [apparelProcessing, setApparelProcessing] = useState(false)
  const play = props.play
  const bracket = play?.bracket
  const viewResultsUrl =
    bracket?.results?.length > 0 ? getBracketResultsUrl(bracket) : undefined
  const handleAddApparel = async () => {
    await addExistingPlayToApparelHandler({
      playId: play?.id,
      setProcessing: setApparelProcessing,
      onError: () => setApparelError(true),
    })
  }
  // const showBustButton = props.handleBustPlay !== undefined
  const showBustButton = false

  return (
    <div className="tw-flex tw-flex-col sm:tw-gap-15">
      <AddToApparel
        variant="grey"
        handleApparelClick={handleAddApparel}
        error={apparelError}
        processing={apparelProcessing}
      />
      <div
        className={`tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-gap-15 ${
          showBustButton ? 'tw-mt-30' : 'tw-mt-15 sm:tw-mt-0'
        } `}
      >
        <PlayOrReplayButton play={play} />
        {viewResultsUrl && (
          <GreenLink href={viewResultsUrl}>
            <EyeIcon className="tw-h-16 sm:tw-h-24" />
            View Results
          </GreenLink>
        )}
        {/* {showBustButton && <BustPicksButton onClick={props.handleBustPlay} />} */}
      </div>
    </div>
  )
}

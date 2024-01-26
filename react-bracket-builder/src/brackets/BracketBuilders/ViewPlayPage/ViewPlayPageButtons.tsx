import { AddToApparel } from '../AddToApparel'
import { BustPicksButton } from '../BustPicksButton'
import { GreenLink } from '../GreenLink'
import {
  EyeIcon,
  PlayIcon,
  getPlayBracketUrl,
  getBracketResultsUrl,
  PlayRes,
} from '../../shared'
import { addToApparelHandler } from './utils'

export const ViewPlayPageButtons = (props: {
  play?: PlayRes
  addApparelUrl: string
}) => {
  const play = props.play
  const bracket = play?.bracket
  const playBracketUrl = bracket?.isOpen
    ? getPlayBracketUrl(bracket)
    : undefined
  const viewResultsUrl =
    bracket?.results?.length > 0 ? getBracketResultsUrl(bracket) : undefined
  const handleAddApparel = async () => {
    await addToApparelHandler(props.play?.id, props.addApparelUrl)
  }
  // const showBustButton = props.handleBustPlay !== undefined
  const showBustButton = false
  return (
    <div className="tw-flex tw-flex-col sm:tw-gap-15">
      <AddToApparel variant="grey" handleApparelClick={handleAddApparel} />
      <div
        className={`tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-gap-15 ${
          showBustButton ? 'tw-mt-30' : 'tw-mt-15 sm:tw-mt-0'
        } `}
      >
        {playBracketUrl && (
          <GreenLink href={playBracketUrl}>
            <PlayIcon className="tw-h-16 sm:tw-h-24" />
            Play Tournament
          </GreenLink>
        )}
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

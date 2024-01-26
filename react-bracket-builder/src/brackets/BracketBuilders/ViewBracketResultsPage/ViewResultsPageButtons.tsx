import { AddToApparel } from '../AddToApparel'
import { BustPicksButton } from '../BustPicksButton'
import { GreenLink } from '../GreenLink'
import {
  EyeIcon,
  PlayIcon,
  getPlayBracketUrl,
  getBracketResultsUrl,
  PlayRes,
  BracketRes,
} from '../../shared'
// import { addToApparelHandler } from './utils'

export const ViewResultsPageButtons = (props: {
  bracket?: BracketRes
  // addApparelUrl: string
}) => {
  // const handleAddApparel = async () => {
  //   await addToApparelHandler(props.play?.id, props.addApparelUrl)
  // }
  return (
    <div className="tw-flex tw-flex-col sm:tw-gap-15">
      {/* <AddToApparel variant="grey" handleApparelClick={handleAddApparel} /> */}
      {/* <GreenLink href={viewResultsUrl}>
        <EyeIcon className="tw-h-16 sm:tw-h-24" />
        View My Picks
      </GreenLink> */}
    </div>
  )
}

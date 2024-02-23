import { ActionButton } from '../../shared/components/ActionButtons'
import { AddToApparel } from '../AddToApparel'
import { CircleCheckBrokenIcon } from '../../shared'
import { MatchTree } from '../../shared/models/MatchTree'
import { SubmitPicksButton } from './SubmitPicksButton'
import { PlayBuilderProps } from './types'

export const PlayBuilderButtons = (props: PlayBuilderProps) => {
  const {
    matchTree,
    handleApparelClick,
    handleSubmitPicksClick,
    processingAddToApparel,
    processingSubmitPicks,
    addToApparelError,
    submitPicksError,
  } = props
  const disableButtons =
    processingAddToApparel ||
    processingSubmitPicks ||
    (matchTree && !matchTree.allPicked())
  const disableApparel = disableButtons || !handleApparelClick
  const showSubmitPicks = handleSubmitPicksClick !== undefined
  const showAddToApparel = handleApparelClick !== undefined
  return (
    <>
      {showAddToApparel && (
        <AddToApparel
          handleApparelClick={handleApparelClick}
          disabled={disableApparel}
          processing={processingAddToApparel}
          error={addToApparelError}
        />
      )}
      {showSubmitPicks && (
        <SubmitPicksButton
          onClick={handleSubmitPicksClick}
          disabled={disableButtons}
          processing={processingSubmitPicks}
          error={submitPicksError}
        />
      )}
    </>
  )
}

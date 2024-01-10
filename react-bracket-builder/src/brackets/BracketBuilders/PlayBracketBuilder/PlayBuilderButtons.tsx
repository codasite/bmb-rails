import { ActionButton } from '../../shared/components/ActionButtons'
import { AddToApparel } from '../AddToApparel'
import { CircleCheckBrokenIcon } from '../../shared'
import { MatchTree } from '../../shared/models/MatchTree'

interface PlayBuilderButtonProps {
  matchTree?: MatchTree
  handleApparelClick?: () => Promise<void>
  handleSubmitPicksClick?: () => Promise<void>
  processing?: boolean
}

export const PlayBuilderButtons = (props: PlayBuilderButtonProps) => {
  const { matchTree, handleApparelClick, handleSubmitPicksClick, processing } =
    props
  const disableButtons = processing || (matchTree && !matchTree.allPicked())
  const disableApparel = disableButtons || !handleApparelClick
  const showSubmitPicks = handleSubmitPicksClick !== undefined
  const showAddToApparel = handleApparelClick !== undefined
  return (
    <>
      {showAddToApparel && (
        <AddToApparel
          handleApparelClick={handleApparelClick}
          disabled={disableApparel}
        />
      )}
      {showSubmitPicks && (
        <ActionButton
          variant="blue"
          onClick={handleSubmitPicksClick}
          disabled={disableButtons}
          fontSize={24}
          fontWeight={700}
        >
          <CircleCheckBrokenIcon style={{ height: 24 }} />
          Submit picks
        </ActionButton>
      )}
    </>
  )
}

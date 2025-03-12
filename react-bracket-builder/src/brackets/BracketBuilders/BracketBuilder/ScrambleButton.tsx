// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useState } from 'react'
import { ReactComponent as ScrambleIcon } from '../../shared/assets/scramble.svg'
import { MatchTree } from '../../shared/models/MatchTree'
import {
  resetTeams,
  scrambleTeams,
} from '../../shared/models/operations/ScrambleTeams'
import { ActionButton } from '../../shared/components/ActionButtons'

interface ScrambleButtonProps {
  matchTree: MatchTree
  setMatchTree: (matchTree: MatchTree) => void
  showPaginated?: boolean
  processing?: boolean
}

export const ScrambleButton = ({
  matchTree,
  setMatchTree,
  showPaginated = false,
  processing = false,
}: ScrambleButtonProps) => {
  const [scrambledIndices, setScrambledIndices] = useState<number[]>([])
  const scrambleDisabled =
    !matchTree || !matchTree.allTeamsAdded() || processing
  const showReset = !scrambleDisabled && scrambledIndices.length > 0

  const onScramble = () => {
    if (!matchTree) {
      return
    }
    let indices = scrambledIndices
    if (indices.length === 0) {
      // new array [0, 1, 2, ...]
      indices = Array.from(Array(matchTree.getNumTeams()).keys())
    }
    const newIndices = scrambleTeams(matchTree, indices)
    setScrambledIndices(newIndices)
    setMatchTree(matchTree)
  }

  const onReset = () => {
    if (!matchTree || scrambledIndices.length === 0) {
      return
    }
    resetTeams(matchTree, scrambledIndices)
    setScrambledIndices([])
    setMatchTree(matchTree)
  }

  return (
    <div className="tw-flex tw-flex-col tw-justify-center tw-gap-10">
      <ActionButton
        className={showPaginated ? '' : 'tw-self-center'}
        variant="blue"
        onClick={onScramble}
        paddingX={16}
        paddingY={12}
        disabled={scrambleDisabled}
      >
        <ScrambleIcon />
        <span className="tw-font-500 tw-text-20 tw-uppercase tw-font-sans">
          Scramble Team Order
        </span>
      </ActionButton>
      {showReset && (
        <ActionButton
          className="tw-self-center"
          backgroundColor="transparent"
          onClick={onReset}
        >
          <span className="tw-font-500 tw-text-16 tw tw-uppercase tw-font-sans tw-underline tw-text-red">
            Reset
          </span>
        </ActionButton>
      )}
    </div>
  )
}

import React, { useState } from 'react'
import darkBracketBg from '../../../shared/assets/bracket-bg-dark.png'
import { MatchTree } from '../../../shared/models/MatchTree'
import { ActionButton } from '../../../shared/components/ActionButtons'
import { PickableBracket } from '../../../shared/components/Bracket'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { StartPageProps } from '../../PaginatedBuilderBase/types'
import { BustablePlayPageButtons } from '../buttons'

interface BustStartPageProps {
  handlePlayBracket: () => void
  handleAddApparel: () => void
  handleBustPlay: () => void
  thumbnailUrl: string
  matchTree?: MatchTree
  screenWidth: number
}

export const BustStartPage = (props: BustStartPageProps) => {
  const {
    handlePlayBracket,
    handleAddApparel,
    handleBustPlay,
    matchTree,
    screenWidth,
  } = props

  return (
    <div
      className={`wpbb-reset tw-flex tw-uppercase tw-min-h-screen tw-bg-no-repeat tw-bg-top tw-bg-cover tw-dark `}
      style={{ backgroundImage: `url(${darkBracketBg})` }}
    >
      <div
        className="tw-flex tw-flex-col tw-justify-center tw-px-20 tw-m-auto"
        style={{ maxWidth: screenWidth }}
      >
        {matchTree && (
          <ScaledBracket
            BracketComponent={PickableBracket}
            matchTree={matchTree}
          />
        )}
        <BustablePlayPageButtons
          handleBustPlay={handleBustPlay}
          handleAddApparel={handleAddApparel}
          handlePlayBracket={handlePlayBracket}
          size="small"
        />
      </div>
    </div>
  )
}

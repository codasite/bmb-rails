import React, { useState } from 'react'
import darkBracketBg from '../../../shared/assets/bracket-bg-dark.png'
import { MatchTree } from '../../../shared/models/MatchTree'
import { ActionButton } from '../../../shared/components/ActionButtons'
import { PickableBracket } from '../../../shared/components/Bracket'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { StartPageProps } from '../../PaginatedBuilderBase/types'

interface BustStartPageProps extends StartPageProps {
  handleJoin: () => void
  handleAddToApparel: () => void
  handleBust: () => void
}

export const BustStartPage = (props: BustStartPageProps) => {
  const { handleJoin, handleAddToApparel, handleBust, matchTree } = props

  return (
    <div
      className={`wpbb-reset tw-flex tw-uppercase tw-min-h-screen tw-bg-no-repeat tw-bg-top tw-bg-cover tw-dark `}
      style={{ backgroundImage: `url(${darkBracketBg})` }}
    >
      <div className="tw-flex tw-flex-col tw-justify-center px-60 tw-max-w-[268px] tw-m-auto">
        {matchTree && (
          <ScaledBracket
            BracketComponent={PickableBracket}
            matchTree={matchTree}
          />
        )}
        <ActionButton
          darkMode={true}
          variant="yellow"
          size="small"
          onClick={onStart}
        >
          Update Picks
        </ActionButton>
      </div>
    </div>
  )
}

import React, { useState } from 'react'
import redBracketBg from '../../../shared/assets/bracket-bg-red.png'
import { MatchTree } from '../../../shared/models/MatchTree'
import { ActionButton } from '../../../shared/components/ActionButtons'
import { ResultsBracket } from '../../../shared/components/Bracket'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { StartPageProps } from '../../PaginatedBuilderBase/types'

interface BustStartPageProps extends StartPageProps {
  matchTree?: MatchTree
}

export const BustStartPage = (props: BustStartPageProps) => {
  const { onStart, matchTree } = props

  return (
    <div
      className={`wpbb-reset tw-flex tw-uppercase tw-min-h-screen tw-bg-no-repeat tw-bg-top tw-bg-cover tw-dark `}
      style={{ backgroundImage: `url(${redBracketBg})` }}
    >
      <div className="tw-flex tw-flex-col tw-justify-center px-60 tw-max-w-[268px] tw-m-auto">
        <h1 className="tw-text-center tw-text-48 tw-font-700 tw-w-">
          Who You Got?
        </h1>
        {matchTree && (
          <ScaledBracket
            BracketComponent={ResultsBracket}
            matchTree={matchTree}
          />
        )}
        <ActionButton
          darkMode={true}
          variant="red"
          size="small"
          onClick={onStart}
        >
          Update Picks
        </ActionButton>
      </div>
    </div>
  )
}

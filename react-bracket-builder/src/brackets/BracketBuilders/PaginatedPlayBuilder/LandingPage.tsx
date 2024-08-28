import React, { useState, useContext } from 'react'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import { MatchTree } from '../../shared/models/MatchTree'
import { ActionButton } from '../../shared/components/ActionButtons'
import { PickableBracket } from '../../shared/components/Bracket'
import { ScaledBracket } from '../../shared/components/Bracket/ScaledBracket'
import { BracketMetaContext } from '../../shared/context/context'
import { BracketHeaderTag } from '../BracketHeaderTag'

interface LandingPageProps {
  onStart: () => void
  matchTree?: MatchTree
  canPlay?: boolean
}

export const LandingPage = (props: LandingPageProps) => {
  const { onStart, matchTree, canPlay } = props
  const { title } = useContext(BracketMetaContext)

  return (
    <div
      className={`wpbb-reset tw-flex tw-uppercase tw-min-h-screen tw-bg-no-repeat tw-bg-top tw-bg-cover tw-dark `}
      style={{ backgroundImage: `url(${darkBracketBg})` }}
    >
      <div className="tw-flex tw-flex-col tw-justify-center tw-items-center px-60 tw-m-auto">
        <BracketHeaderTag
          text={`Voting Round ${matchTree.liveRoundIndex + 1}`}
          color="green"
        />
        <h1 className="tw-text-center tw-text-48 tw-font-700 tw-w-">{title}</h1>
        {matchTree && (
          <div className="tw-mt-60">
            <ScaledBracket
              BracketComponent={PickableBracket}
              matchTree={matchTree}
              paddingX={40}
              title=""
            />
          </div>
        )}
        {canPlay && (
          <ActionButton
            variant="green"
            size="small"
            onClick={onStart}
            className="tw-mt-80 tw-max-w-[268px] tw-w-full"
          >
            Start
          </ActionButton>
        )}
      </div>
    </div>
  )
}

import React, { useState, useContext } from 'react'
import redBracketBg from '../../../shared/assets/bracket-bg-red.png'
import { MatchTree } from '../../../shared/models/MatchTree'
import { ActionButton } from '../../../shared/components/ActionButtons'
import {
  BusterBracket,
  ResultsBracket,
} from '../../../shared/components/Bracket'
import { DarkModeContext } from '../../../shared/context'
import { ThemeSelector } from '../../../shared/components'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { bracketApi } from '../../../shared/api/bracketApi'
import { EndPageProps } from '../../PaginatedBuilderBase/types'
import { BusterVsBustee } from '../BusterVersusBustee'
import { BracketBusterContext } from '../context'

export const BustEndPage = (props: EndPageProps) => {
  const { matchTree, darkMode, processing, handleSubmit } = props
  const {
    busteeDisplayName,
    busteeThumbnail,
    busterDisplayName,
    busterThumbnail,
  } = useContext(BracketBusterContext)

  return (
    <div
      className={`wpbb-reset tw-min-h-screen tw-flex tw-flex-col tw-justify-center tw-items-center tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${redBracketBg})`,
      }}
    >
      <div className="tw-flex tw-flex-col tw-justify-between tw-items-center tw-max-w-[268px] tw-max-h-[500px] tw-mx-auto tw-flex-grow tw-my-60">
        <BusterVsBustee
          busteeDisplayName={busteeDisplayName}
          busteeThumbnail={busteeThumbnail}
          busterDisplayName={busterDisplayName}
          busterThumbnail={busterThumbnail}
        />
        {matchTree && (
          <ScaledBracket
            BracketComponent={BusterBracket}
            matchTree={matchTree}
          />
        )}
        <ActionButton
          variant="red"
          paddingX={60}
          paddingY={15}
          fontSize={20}
          onClick={handleSubmit}
          disabled={processing || !matchTree?.allPicked()}
        >
          Submit
        </ActionButton>
      </div>
    </div>
  )
}

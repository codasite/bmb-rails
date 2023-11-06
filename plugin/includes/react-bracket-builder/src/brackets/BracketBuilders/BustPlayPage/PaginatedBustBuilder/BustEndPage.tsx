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
import { getBustTrees } from '../utils'
import { DefaultEditButton } from '../../../shared/components/Bracket/BracketActionButtons'

export const BustEndPage = (props: EndPageProps) => {
  const { matchTree, darkMode, processing, handleSubmit, onEditClick } = props
  const { busterTree } = getBustTrees()
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
      <div className="tw-flex tw-flex-col tw-justify-between tw-max-w-[268px] tw-max-h-[500px] tw-mx-auto tw-flex-grow tw-my-60">
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
        <div className="tw-flex tw-flex-col tw-gap-10">
          <DefaultEditButton
            darkMode={darkMode}
            onClick={onEditClick}
            disabled={processing}
          />
          <ActionButton
            variant="red"
            size="small"
            onClick={handleSubmit}
            disabled={processing || !busterTree?.allPicked()}
          >
            Submit
          </ActionButton>
        </div>
      </div>
    </div>
  )
}

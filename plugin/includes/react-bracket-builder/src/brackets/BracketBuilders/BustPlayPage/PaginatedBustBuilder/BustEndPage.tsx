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

export const BustEndPage = (props: EndPageProps) => {
  const { matchTree, darkMode, processing, handleSubmit } = props

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
        {matchTree && (
          <ScaledBracket
            BracketComponent={BusterBracket}
            matchTree={matchTree}
          />
        )}
        <ActionButton
          variant="red"
          size="small"
          darkMode={darkMode}
          onClick={handleSubmit}
          disabled={processing || !matchTree?.allPicked()}
          fontSize={16}
          backgroundColor="yellow"
          textColor="dd-blue"
        >
          {matchTree.allPicked() ? 'Complete Bracket' : 'Update Picks'}
        </ActionButton>
      </div>
    </div>
  )
}

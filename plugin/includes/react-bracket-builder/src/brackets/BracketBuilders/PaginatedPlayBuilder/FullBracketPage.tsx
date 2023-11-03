import React, { useState, useContext } from 'react'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { MatchTree } from '../../shared/models/MatchTree'
import { ActionButton } from '../../shared/components/ActionButtons'
import { PickableBracket } from '../../shared/components/Bracket'
import { DarkModeContext } from '../../shared/context'
import { ThemeSelector } from '../../shared/components'
import { ScaledBracket } from '../../shared/components/Bracket/ScaledBracket'
import { Spinner } from '../../shared/components/Spinner'
import { ReactComponent as EditIcon } from '../../shared/assets/edit-icon.svg'

interface FullBracketPageProps {
  onEditClick: () => void
  onApparelClick: () => void
  matchTree?: MatchTree
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  processing?: boolean
}

export const FullBracketPage = (props: FullBracketPageProps) => {
  const {
    onEditClick,
    onApparelClick,
    matchTree,
    darkMode,
    setDarkMode,
    processing,
  } = props

  console.log('darkMode', darkMode)

  return (
    <div
      className={`wpbb-reset tw-min-h-screen tw-flex tw-flex-col tw-justify-center tw-items-center tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${darkMode ? darkBracketBg : lightBracketBg})`,
      }}
    >
      <div className="tw-flex tw-flex-col tw-justify-between tw-max-w-[268px] tw-max-h-[500px] tw-mx-auto tw-flex-grow tw-my-60">
        <ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
        {matchTree && (
          <ScaledBracket
            BracketComponent={PickableBracket}
            matchTree={matchTree}
          />
        )}
        <ActionButton
          variant="white"
          darkMode={darkMode}
          onClick={onEditClick}
          disabled={processing}
          borderWidth={1}
        >
          <EditIcon />
          <span>Edit</span>
        </ActionButton>
        <ActionButton
          variant="small-green"
          darkMode={darkMode}
          onClick={onApparelClick}
          disabled={processing || !matchTree?.allPicked()}
        >
          {processing ? (
            <Spinner fill="white" height={32} width={32} />
          ) : (
            'Add to Apparel'
          )}
        </ActionButton>
      </div>
    </div>
  )
}

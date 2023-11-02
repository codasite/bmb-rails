import React, { useState, useContext } from 'react'
import darkBracketBg from '../../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../../shared/assets/bracket-bg-light.png'
import { MatchTree } from '../../../shared/models/MatchTree'
import { ActionButton } from '../../../shared/components/ActionButtons'
import { ResultsBracket } from '../../../shared/components/Bracket'
import { DarkModeContext } from '../../../shared/context'
import { ThemeSelector } from '../../../shared/components'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { bracketApi } from '../../../shared/api/bracketApi'


interface FullBracketPageProps {
  matchTree?: MatchTree
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  processing?: boolean
  myBracketsUrl?: string
  handleUpdatePicks?: () => void
}

export const FullBracketPage = (props: FullBracketPageProps) => {
  const { myBracketsUrl, matchTree, darkMode, setDarkMode, processing, handleUpdatePicks } = props
  const [notifyParticipants, setNotifyParticipants] = useState(true)
  const [bracketId, setBracketId] = useState(0)

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
            BracketComponent={ResultsBracket}
            matchTree={matchTree}
          />
        )}
        <ActionButton
          variant="small-yellow"
          darkMode={darkMode}
          onClick={handleUpdatePicks}
          disabled={processing || !matchTree?.allPicked()}
          fontSize={16}
          backgroundColor='yellow'
          textColor='dd-blue'
        >
          {matchTree.allPicked() ? 'Complete Bracket' : 'Update Picks'}
        </ActionButton>
      </div>
    </div>
  )
}

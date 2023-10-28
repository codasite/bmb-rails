import React from 'react'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { PickableBracket } from '../../shared/components/Bracket'
import { ThemeSelector } from '../../shared/components'
import { ActionButton } from '../../shared/components/ActionButtons'
import { PlayBuilderProps } from './types'
import { Spinner } from '../../shared/components/Spinner'

export const PlayBuilder = (props: PlayBuilderProps) => {
  const {
    darkMode,
    setDarkMode,
    matchTree,
    setMatchTree,
    handleApparelClick,
    processing,
  } = props

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${darkMode ? darkBracketBg : lightBracketBg})`,
      }}
    >
      {matchTree && (
        <div
          className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto`}
        >
          <div className="tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center">
            <ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
          </div>
          <PickableBracket matchTree={matchTree} setMatchTree={setMatchTree} />
          <div className="tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center">
            <ActionButton
              variant="big-green"
              darkMode={darkMode}
              onClick={handleApparelClick}
              disabled={processing || !matchTree.allPicked()}
              height={72}
              width={405}
            >
              {processing ? (
                <Spinner fill="white" height={50} width={50} />
              ) : (
                'Add to Apparel'
              )}
            </ActionButton>
          </div>
        </div>
      )}
    </div>
  )
}

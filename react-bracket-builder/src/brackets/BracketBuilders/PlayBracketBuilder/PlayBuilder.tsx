import React from 'react'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { PickableBracket } from '../../shared/components/Bracket'
import { ThemeSelector } from '../../shared/components'
import { ActionButton } from '../../shared/components/ActionButtons'
import { PlayBuilderProps } from './types'
import { AddToApparel } from '../AddToApparel'
import { CircleCheckBrokenIcon } from '../../shared'
import SubmitPicksRegisterModal from './SubmitPicksRegisterModal'
import { PlayBuilderButtons } from './PlayBuilderButtons'
import SubmitPicksPaymentModal from './SubmitPicksPaymentModal'

export const PlayBuilder = (props: PlayBuilderProps) => {
  const {
    darkMode,
    setDarkMode,
    matchTree,
    setMatchTree,
    handleApparelClick,
    handleSubmitPicksClick,
    processing,
    showRegisterModal,
    setShowRegisterModal,
    showPaymentModal,
    setShowPaymentModal,
  } = props
  const disableButtons = processing || (matchTree && !matchTree.allPicked())
  const disableApparel = disableButtons || !handleApparelClick
  const showSubmitPicks = handleSubmitPicksClick !== undefined
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
          className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto tw-pb-80`}
        >
          <div className="tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center">
            <ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
          </div>
          <PickableBracket matchTree={matchTree} setMatchTree={setMatchTree} />
          <div className="tw-px-24 tw-mt-60 tw-flex tw-gap-15 tw-flex-col tw-items-stretch tw-self-stretch">
            <PlayBuilderButtons {...props} />
          </div>
        </div>
      )}
    </div>
  )
}

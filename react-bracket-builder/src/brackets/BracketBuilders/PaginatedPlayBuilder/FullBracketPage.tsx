import React, { useState, useContext } from 'react'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { MatchTree } from '../../shared/models/MatchTree'
import { ActionButton } from '../../shared/components/ActionButtons'
import { PickableBracket } from '../../shared/components/Bracket'
import { DarkModeContext } from '../../shared/context/context'
import { ThemeSelector } from '../../shared/components'
import { ScaledBracket } from '../../shared/components/Bracket/ScaledBracket'
import { Spinner } from '../../shared/components/Spinner'
import { ReactComponent as EditIcon } from '../../shared/assets/edit-icon.svg'
import SubmitPicksRegisterModal from '../PlayBracketBuilder/SubmitPicksRegisterModal'

interface FullBracketPageProps {
  onEditClick?: () => void
  onApparelClick: () => void
  matchTree?: MatchTree
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  processing?: boolean
  canEdit?: boolean
  showRegisterModal?: boolean
  setShowRegisterModal?: (showRegisterModal: boolean) => void
}

export const FullBracketPage = (props: FullBracketPageProps) => {
  const {
    onEditClick,
    onApparelClick,
    matchTree,
    darkMode,
    setDarkMode,
    processing,
    showRegisterModal,
    setShowRegisterModal,
  } = props

  const canEdit = !!onEditClick

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
      <SubmitPicksRegisterModal
        show={showRegisterModal}
        setShow={setShowRegisterModal}
      />
      <div className="tw-flex tw-flex-col tw-justify-between tw-items-center tw-mx-auto tw-flex-grow tw-mt-60 tw-mb-80">
        <ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
        {matchTree && (
          <ScaledBracket
            BracketComponent={PickableBracket}
            matchTree={matchTree}
          />
        )}
        <div className="tw-flex tw-self-stretch tw-flex-col tw-gap-10 tw-max-w-[268px] tw-mx-auto tw-w-full">
          {canEdit && (
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
          )}
          <ActionButton
            variant="green"
            size="small"
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
    </div>
  )
}

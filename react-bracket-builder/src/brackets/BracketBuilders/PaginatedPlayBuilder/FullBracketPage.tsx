import React from 'react'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { MatchTree } from '../../shared/models/MatchTree'
import { ActionButton } from '../../shared/components/ActionButtons'
import { PickableBracket } from '../../shared/components/Bracket'
import { ThemeSelector } from '../../shared/components'
import { ScaledBracket } from '../../shared/components/Bracket/ScaledBracket'
import { ReactComponent as EditIcon } from '../../shared/assets/edit-icon.svg'
import SubmitPicksRegisterModal from '../PlayBracketBuilder/SubmitPicksRegisterModal'
import { AddToApparel } from '../AddToApparel'
import { CircleCheckBrokenIcon } from '../../shared'

interface FullBracketPageProps {
  onEditClick?: () => void
  onApparelClick: () => Promise<void>
  handleSubmitPicksClick?: () => Promise<void>
  showSubmitPicksButton?: boolean
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
    handleSubmitPicksClick,
    showSubmitPicksButton,
    matchTree,
    darkMode,
    setDarkMode,
    processing,
    showRegisterModal,
    setShowRegisterModal,
  } = props

  const canEdit = !!onEditClick
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
        <div className="tw-flex tw-self-stretch tw-flex-col tw-gap-10 tw-items-stretch tw-px-4">
          {canEdit && (
            <ActionButton
              variant="white"
              darkMode={darkMode}
              onClick={onEditClick}
              disabled={processing}
              borderWidth={0}
            >
              <EditIcon />
              <span>Edit</span>
            </ActionButton>
          )}
          <AddToApparel
            handleApparelClick={onApparelClick}
            disabled={processing || !matchTree?.allPicked()}
          />
          {showSubmitPicksButton && (
            <ActionButton
              variant="blue"
              onClick={handleSubmitPicksClick}
              disabled={processing || !matchTree.allPicked()}
              fontSize={24}
              fontWeight={700}
            >
              <CircleCheckBrokenIcon style={{ height: 24 }} />
              Submit picks
            </ActionButton>
          )}
        </div>
      </div>
    </div>
  )
}

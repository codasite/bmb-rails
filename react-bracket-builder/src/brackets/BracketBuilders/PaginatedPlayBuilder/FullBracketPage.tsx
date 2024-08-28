import React, { useContext } from 'react'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { ActionButton } from '../../shared/components/ActionButtons'
import { PickableBracket } from '../../shared/components/Bracket'
import { ThemeSelector } from '../../shared/components'
import { ScaledBracket } from '../../shared/components/Bracket/ScaledBracket'
import { ReactComponent as EditIcon } from '../../shared/assets/edit-icon.svg'
import { PlayBuilderButtons } from '../PlayBracketBuilder/PlayBuilderButtons'
import { PlayBuilderProps } from '../PlayBracketBuilder/types'
import { DarkModeContext } from '../../shared/context/context'
import { BracketHeaderTag } from '../BracketHeaderTag'

interface FullBracketPageProps extends PlayBuilderProps {
  onEditClick?: () => void
}

export const FullBracketPage = (props: FullBracketPageProps) => {
  const { onEditClick, matchTree } = props
  const { darkMode } = useContext(DarkModeContext)

  const canEdit = !!onEditClick
  const processing = props.processingAddToApparel || props.processingSubmitPicks
  return (
    <div
      className={`wpbb-reset tw-min-h-screen tw-flex tw-flex-col tw-justify-center tw-items-center tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover ${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${darkMode ? darkBracketBg : lightBracketBg})`,
      }}
    >
      <div className="tw-flex tw-flex-col tw-justify-between tw-items-center tw-mx-auto tw-flex-grow tw-mt-60 tw-mb-80">
        <div className="tw-self-center">
          <div className="tw-mb-10">
            <BracketHeaderTag
              text={`Voting Round ${matchTree.liveRoundIndex + 1}`}
              color="green"
            />
          </div>
          <ThemeSelector />
        </div>

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
              onClick={onEditClick}
              disabled={processing}
              borderWidth={0}
            >
              <EditIcon />
              <span>Edit</span>
            </ActionButton>
          )}
          <PlayBuilderButtons {...props} />
        </div>
      </div>
    </div>
  )
}

// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext } from 'react'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { ActionButton } from '../../shared/components/ActionButtons'
import { PickableBracket } from '../../shared/components/Bracket'
import { ScaledBracket } from '../../shared/components/Bracket/ScaledBracket'
import { ReactComponent as EditIcon } from '../../shared/assets/edit-icon.svg'
import { PlayBuilderButtons } from '../PlayBracketBuilder/PlayBuilderButtons'
import { PlayBuilderProps } from '../PlayBracketBuilder/types'
import { DarkModeContext } from '../../shared/context/context'
import { BracketHeaderTag } from '../BracketHeaderTag'
import { ThemeSelector } from '../../../ui/ThemeSelector'
import { VotingBracket } from '../../../features/VotingBracket/VotingBracket'
import { BracketBackground } from '../../shared/components/BracketBackground'

interface FullBracketPageProps extends PlayBuilderProps {
  onEditClick?: () => void
}

export const FullBracketPage = (props: FullBracketPageProps) => {
  const { onEditClick, matchTree } = props
  const { darkMode } = useContext(DarkModeContext)

  const canEdit = !!onEditClick
  const processing = props.processingAddToApparel || props.processingSubmitPicks
  return (
    <BracketBackground className="tw-flex tw-flex-col tw-justify-center tw-items-center tw-py-48">
      <div className="tw-flex tw-flex-col tw-justify-between tw-items-center tw-grow">
        <div className="tw-self-center">
          <div className="tw-mb-10">
            {matchTree.isVoting && (
              <BracketHeaderTag
                text={`Voting Round ${matchTree.liveRoundIndex + 1}`}
                color="green"
              />
            )}
          </div>
          <ThemeSelector />
        </div>

        {matchTree && (
          <ScaledBracket
            BracketComponent={
              matchTree.isVoting ? VotingBracket : PickableBracket
            }
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
    </BracketBackground>
  )
}

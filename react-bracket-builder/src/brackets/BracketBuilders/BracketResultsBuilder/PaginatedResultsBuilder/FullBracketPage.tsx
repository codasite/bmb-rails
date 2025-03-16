// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext } from 'react'
import darkBracketBg from '../../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../../shared/assets/bracket-bg-light.png'
import { MatchTree } from '../../../shared/models/MatchTree'
import { ActionButton } from '../../../shared/components/ActionButtons'
import { ResultsBracket } from '../../../shared/components/Bracket'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { ReactComponent as EditIcon } from '../../../shared/assets/edit-icon.svg'
import { Checkbox } from '../Checkbox'
import { BracketResultsBuilderContext } from '../context'
import { DarkModeContext } from '../../../shared/context/context'
import { BracketBackground } from '../../../shared/components/BracketBackground'

interface FullBracketPageProps {
  matchTree?: MatchTree
  processing?: boolean
  handleUpdatePicks: () => void
  onEditClick: () => void
}

export const FullBracketPage = (props: FullBracketPageProps) => {
  const { matchTree, processing, onEditClick, handleUpdatePicks } = props

  const { notifyParticipants, toggleNotifyParticipants } = useContext(
    BracketResultsBuilderContext
  )
  const { darkMode } = useContext(DarkModeContext)

  return (
    <BracketBackground className="tw-flex tw-flex-col tw-justify-center tw-items-stretch tw-py-48 tw-gap-30">
      {matchTree && (
        <ScaledBracket
          BracketComponent={ResultsBracket}
          matchTree={matchTree}
        />
      )}
      <div className="tw-flex tw-flex-col tw-gap-30">
        <div className="tw-flex tw-flex-col tw-gap-10">
          <ActionButton
            variant="white"
            onClick={onEditClick}
            disabled={processing}
            borderWidth={1}
          >
            <EditIcon />
            <span>Edit</span>
          </ActionButton>
          <ActionButton
            variant="green"
            size="small"
            onClick={handleUpdatePicks}
            disabled={processing}
            fontSize={16}
          >
            {matchTree.allPicked() ? 'Complete Bracket' : 'Update Results'}
          </ActionButton>
        </div>
        <div className="tw-flex tw-items-center tw-justify-center tw-gap-[16px]">
          <Checkbox
            id="notify-participants-check"
            checked={notifyParticipants}
            onChange={toggleNotifyParticipants}
            height={24}
            width={24}
          />
          <label
            htmlFor="notify-participants-check"
            className="tw-font-500 tw-text-16 tw-items-center"
          >
            Notify Participants
          </label>
        </div>
      </div>
    </BracketBackground>
  )
}

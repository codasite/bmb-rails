import React, { useState, useContext } from 'react'
import darkBracketBg from '../../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../../shared/assets/bracket-bg-light.png'
import { MatchTree } from '../../../shared/models/MatchTree'
import { ActionButton } from '../../../shared/components/ActionButtons'
import { ResultsBracket } from '../../../shared/components/Bracket'
import { DarkModeContext } from '../../../shared/context'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { bracketApi } from '../../../shared/api/bracketApi'
import { ReactComponent as EditIcon } from '../../../shared/assets/edit-icon.svg'
import checkIcon from '../../../shared/assets/check.svg'

const CustomCheckbox = (props: any) => {
  const { id, checked, onChange } = props

  const baseStyles = [
    'tw-appearance-none',
    'tw-h-24',
    'tw-w-24',
    'tw-rounded-8',
    'tw-cursor-pointer',
  ]

  const uncheckedStyles = ['tw-border', 'tw-border-solid', 'tw-border-white']

  const checkedStyles = ['tw-bg-white', 'tw-bg-no-repeat', 'tw-bg-center']

  const styles = baseStyles
    .concat(checked ? checkedStyles : uncheckedStyles)
    .join(' ')

  return (
    <input
      type="checkbox"
      id={id}
      className={styles}
      checked={checked}
      onChange={onChange}
      style={{ backgroundImage: checked ? `url(${checkIcon})` : 'none' }}
    />
  )
}

interface FullBracketPageProps {
  matchTree?: MatchTree
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  processing?: boolean
  myBracketsUrl?: string
  handleUpdatePicks: () => void
}

export const FullBracketPage = (props: FullBracketPageProps) => {
  const {
    myBracketsUrl,
    matchTree,
    darkMode,
    setDarkMode,
    processing,
    handleUpdatePicks,
  } = props
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
        {matchTree && (
          <ScaledBracket
            BracketComponent={ResultsBracket}
            matchTree={matchTree}
          />
        )}
        <ActionButton
          variant="yellow"
          size="small"
          darkMode={darkMode}
          onClick={handleUpdatePicks}
          disabled={processing || !matchTree?.allPicked()}
          fontSize={16}
        >
          {matchTree.allPicked() ? 'Complete Bracket' : 'Update Picks'}
        </ActionButton>
        <div className="tw-flex tw-items-center tw-justify-center tw-gap-[16px]">
          <CustomCheckbox
            id="notify-participants-check"
            checked={notifyParticipants}
            onChange={() => setNotifyParticipants(!notifyParticipants)}
          />
          <label
            htmlFor="notify-participants-check"
            className="tw-font-500 tw-text-16 tw-items-center"
          >
            Notify Participants
          </label>
        </div>
      </div>
    </div>
  )
}

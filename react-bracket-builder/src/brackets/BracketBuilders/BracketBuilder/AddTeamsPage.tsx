// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext, useState } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { ReactComponent as ArrowNarrowLeft } from '../../shared/assets/arrow-narrow-left.svg'
import iconBackground from '../../shared/assets/bmb_icon_white_02.png'
import { AddTeamsBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import { ReactComponent as SaveIcon } from '../../shared/assets/save.svg'
import { getBracketWidth } from '../../shared/components/Bracket/utils'
import { DatePicker } from '../../shared/components/DatePicker'
import { WindowDimensionsContext } from '../../shared/context/WindowDimensionsContext'
import { WithWindowDimensions } from '../../shared/components/HigherOrder/WithWindowDimensions'
import {
  resetTeams,
  scrambleTeams,
} from '../../shared/models/operations/ScrambleTeams'
import { ReactComponent as ScrambleIcon } from '../../shared/assets/scramble.svg'
import { BracketBuilderHeader } from './BracketBuilderHeader'
import { PaginatedAddTeamsBracket } from '../../shared/components/Bracket/PaginatedAddTeamsBracket'
import { AddTeamsStartPage } from './PaginatedAddTeams/AddTeamsStartPage'
import { AddTeamsPages } from './PaginatedAddTeams/AddTeamsPages'
import { AddTeamsEndPage } from './PaginatedAddTeams/AddTeamsEndPage'
import { PaginatedBuilder } from '../PaginatedBuilderBase/PaginatedBuilder'
import { BracketBackground } from '../../shared/components/BracketBackground'

interface AddTeamsPageProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  handleSaveBracket: () => void
  handleBack: () => void
  month?: string
  setMonth?: (month: string) => void
  year?: string
  setYear?: (year: string) => void
  processing?: boolean
}
const AddTeamsPage = (props: AddTeamsPageProps) => {
  const {
    matchTree,
    setMatchTree,
    handleSaveBracket,
    handleBack,
    month,
    setMonth,
    year,
    setYear,
    processing,
  } = props
  const [dateError, setDateError] = useState(false)
  const [scrambledIndices, setScrambledIndices] = useState<number[]>([])
  const createDisabled =
    !matchTree || !matchTree.allTeamsAdded() || dateError || processing
  const scrambleDisabled =
    !matchTree || !matchTree.allTeamsAdded() || processing
  const showReset = !scrambleDisabled && scrambledIndices.length > 0
  const { width: windowWidth } = useContext(WindowDimensionsContext)
  const showPaginated = windowWidth < getBracketWidth(matchTree.rounds.length)
  function onScramble() {
    if (!matchTree) {
      return
    }
    let indices = scrambledIndices
    if (indices.length === 0) {
      // new array [0, 1, 2, ...]
      indices = Array.from(Array(matchTree.getNumTeams()).keys())
    }
    const newIndices = scrambleTeams(matchTree, indices)
    setScrambledIndices(newIndices)
    setMatchTree(matchTree)
  }
  function onReset() {
    if (!matchTree || scrambledIndices.length === 0) {
      return
    }
    resetTeams(matchTree, scrambledIndices)
    setScrambledIndices([])
    setMatchTree(matchTree)
  }
  const showNew = window.location.pathname.includes('copy-new')
  return (
    <BracketBackground>
      <BracketBuilderHeader />
      <div className="sm:tw-px-60 tw-mb-16">
        <div className="tw-flex tw-p-16">
          <a
            href="#"
            className="tw-flex tw-gap-10 tw-items-center"
            onClick={handleBack}
          >
            <ArrowNarrowLeft />
            <span className="tw-font-500 tw-text-16 sm:tw-text-20 tw-text-white ">
              Create Bracket
            </span>
          </a>
        </div>
      </div>
      <div
        className={`tw-flex tw-flex-col tw-w-full tw-max-w-screen-lg tw-mx-auto ${
          showPaginated ? 'tw-gap-30' : 'tw-gap-60'
        }`}
      >
        <div>
          <div
            className={`tw-flex tw-flex-col tw-justify-center tw-items-center tw-dark ${
              showPaginated ? 'tw-overflow-hidden' : ''
            }`}
          >
            {matchTree && !showPaginated && (
              <AddTeamsBracket
                matchTree={matchTree}
                setMatchTree={setMatchTree}
              />
            )}
            {matchTree &&
              showPaginated &&
              (showNew ? (
                <PaginatedBuilder
                  matchTree={matchTree}
                  setMatchTree={setMatchTree}
                  handleSubmit={handleSaveBracket}
                  processing={processing}
                  BracketPagesComponent={AddTeamsPages}
                  EndPageComponent={AddTeamsEndPage}
                  StartPageComponent={AddTeamsStartPage}
                />
              ) : (
                <PaginatedAddTeamsBracket
                  matchTree={matchTree}
                  setMatchTree={setMatchTree}
                />
              ))}
          </div>
        </div>
        <div className="tw-flex tw-flex-col tw-justify-center tw-gap-10">
          <ActionButton
            className={showPaginated ? '' : 'tw-self-center'}
            variant="blue"
            onClick={onScramble}
            paddingX={16}
            paddingY={12}
            disabled={scrambleDisabled}
          >
            <ScrambleIcon />
            <span className="tw-font-500 tw-text-20 tw-uppercase tw-font-sans">
              Scramble Team Order
            </span>
          </ActionButton>
          {showReset && (
            <ActionButton
              className="tw-self-center"
              backgroundColor="transparent"
              onClick={onReset}
            >
              <span className="tw-font-500 tw-text-16 tw tw-uppercase tw-font-sans tw-underline tw-text-red">
                Reset
              </span>
            </ActionButton>
          )}
        </div>
        <div className="tw-flex tw-flex-col tw-gap-60 tw-max-w-[510px] tw-w-full tw-mx-auto">
          <DatePicker
            month={month}
            year={year}
            handleMonthChange={(month) => setMonth(month)}
            handleYearChange={(year) => setYear(year)}
            onHasError={(error) => setDateError(true)}
            onErrorCleared={() => setDateError(false)}
            showTitle={true}
          />
        </div>
        <ActionButton
          variant="green"
          gap={16}
          disabled={createDisabled}
          onClick={handleSaveBracket}
        >
          <SaveIcon />
          <span className="tw-font-500 tw-text-20 tw-uppercase tw-font-sans">
            Save
          </span>
        </ActionButton>
      </div>
    </BracketBackground>
  )
}

const Wrapped = WithWindowDimensions(AddTeamsPage)
export { Wrapped as AddTeamsPage }

// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext, useState } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { ReactComponent as ArrowNarrowLeft } from '../../shared/assets/arrow-narrow-left.svg'
import { AddTeamsBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import { ReactComponent as SaveIcon } from '../../shared/assets/save.svg'
import { getBracketWidth } from '../../shared/components/Bracket/utils'
import { DatePicker } from '../../shared/components/DatePicker'
import { WindowDimensionsContext } from '../../shared/context/WindowDimensionsContext'
import { WithWindowDimensions } from '../../shared/components/HigherOrder/WithWindowDimensions'
import { BracketBuilderHeader } from './BracketBuilderHeader'
import { AddTeamsFullBracketPage } from './PaginatedAddTeams/AddTeamsFullBracketPage'
import { AddTeamsPages } from './PaginatedAddTeams/AddTeamsPages'
import { PaginatedBuilder } from '../PaginatedBuilderBase/PaginatedBuilder'
import { BracketBackground } from '../../shared/components/BracketBackground'
import { ScrambleButton } from './ScrambleButton'
import { BracketMetaContext } from '../../shared/context/context'
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
  const { bracketMeta } = useContext(BracketMetaContext)
  const createDisabled =
    !matchTree ||
    !matchTree.allTeamsAdded() ||
    dateError ||
    processing ||
    bracketMeta?.title.length === 0
  const { width: windowWidth } = useContext(WindowDimensionsContext)
  const showPaginated = windowWidth < getBracketWidth(matchTree.rounds.length)
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
            {matchTree && showPaginated && (
              <PaginatedBuilder
                matchTree={matchTree}
                setMatchTree={setMatchTree}
                BracketPagesComponent={AddTeamsPages}
                EndPageComponent={AddTeamsFullBracketPage}
                initialPage={createDisabled ? 'bracket' : 'end'}
              />
            )}
          </div>
        </div>
        {matchTree && setMatchTree && matchTree.rounds.length < 7 && (
          <ScrambleButton
            matchTree={matchTree}
            setMatchTree={setMatchTree}
            showPaginated={showPaginated}
            processing={processing}
          />
        )}
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

import React from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { ReactComponent as ArrowNarrowLeft } from '../../shared/assets/arrow-narrow-left.svg'
import iconBackground from '../../shared/assets/bmb_icon_white_02.png'
import { AddTeamsBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import { ReactComponent as SaveIcon } from '../../shared/assets/save.svg'
import { useWindowDimensions } from '../../../utils/hooks'
import { PaginatedAddTeamsBracket } from '../../shared/components/Bracket/PaginatedAddTeamsBracket'
import { getBracketWidth } from '../../shared/utils'
import { DatePicker } from '../../shared/components/DatePicker'

interface AddTeamsPageProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  handleSaveBracket: () => void
  handleBack: () => void
  month?: string
  setMonth?: (month: string) => void
  year?: string
  setYear?: (year: string) => void
}
export const AddTeamsPage = (props: AddTeamsPageProps) => {
  const {
    matchTree,
    setMatchTree,
    handleSaveBracket,
    handleBack,
    month,
    setMonth,
    year,
    setYear,
  } = props
  const createDisabled =
    !matchTree || !matchTree.allTeamsAdded() || !month || !year
  const { width: windowWidth } = useWindowDimensions()
  const showPaginated = windowWidth < getBracketWidth(matchTree.rounds.length)
  return (
    <div
      className="tw-flex tw-flex-col tw-gap-1 tw-pt-30 tw-pb-60 tw-bg-no-repeat tw-bg-top tw-bg-cover tw-px-20"
      style={{ background: `url(${iconBackground}), #000225` }}
    >
      <div className="tw-px-30 sm:tw-px-60 tw-mb-16">
        <div className="tw-flex tw-p-16">
          <a
            href="#"
            className="tw-flex tw-gap-10 tw-items-center"
            onClick={handleBack}
          >
            <ArrowNarrowLeft />
            <span className="tw-font-500 tw-text-20 tw-text-white ">
              Create Bracket
            </span>
          </a>
        </div>
      </div>
      <div className="tw-flex tw-flex-col tw-gap-60 tw-w-full tw-max-w-screen-lg tw-mx-auto">
        <div>
          <div
            className={`tw-flex tw-flex-col tw-justify-center tw-items-center tw-dark`}
          >
            {matchTree && !showPaginated && (
              <AddTeamsBracket
                matchTree={matchTree}
                setMatchTree={setMatchTree}
              />
            )}
            {matchTree && showPaginated && (
              <PaginatedAddTeamsBracket
                matchTree={matchTree}
                setMatchTree={setMatchTree}
              />
            )}
          </div>
        </div>
        {/* <div className="tw-flex tw-flex-col tw-gap-[46px] tw-max-w-screen-lg tw-m-auto tw-w-full"> */}
        <div className="tw-flex tw-flex-col tw-gap-60">
          <DatePicker
            handleMonthChange={(month) => setMonth(month)}
            handleYearChange={(year) => setYear(year)}
            showTitle={true}
            // backgroundColorClass={'tw-bg-greyBlue'}
            backgroundColorClass={'tw-bg-lightGreyBlue'}
            selectMenuPlacement="top"
          />
          {/* <ActionButton className='tw-self-center' variant='blue' onClick={handleBack} paddingX={16} paddingY={12}>
					<ShuffleIcon />
					<span className='tw-font-500 tw-text-20 tw-uppercase tw-font-sans'>Scramble Team Order</span>
				</ActionButton> */}
        </div>
        <ActionButton
          variant="blue"
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
    </div>
  )
}

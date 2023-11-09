import React from 'react'
import { PaginatedBracketProps } from '../types'
import { AddTeamsBracket } from './AddTeamsBracket'
import { getTeamHeight, getTeamWidth } from './utils'
import { ActionButton } from '../ActionButtons'
import { ReactComponent as ChevronRight } from '../../assets/chevron-right.svg'
import { ReactComponent as ChevronLeft } from '../../assets/chevron-left.svg'
import { ReactComponent as EditIcon } from '../../assets/edit-icon.svg'
import { getLeftMatches } from '../../models/operations/GetMatchSections'
import { ScaledBracket } from './ScaledBracket'

export const PaginatedAddTeamsBracket = (props: PaginatedBracketProps) => {
  const [page, setPage] = React.useState(0)
  const [showFullBracket, setShowFullBracket] = React.useState(false)
  const numRounds = props.matchTree.rounds.length
  const numColumns = numRounds * 2 - 1
  const columnWidth = getTeamWidth(0)
  const spaceBetweenColumns = 30
  const bracketWidth =
    numColumns * columnWidth + (numColumns - 1) * spaceBetweenColumns
  const moveOver = columnWidth + spaceBetweenColumns
  const rounds = props.matchTree.rounds
  const columnsToPaginate = [0, numColumns - 1]
  const currentColumn = columnsToPaginate[page]
  const columnOffset = numRounds - 1 - currentColumn
  const columnsToRender = [currentColumn - 1, currentColumn, currentColumn + 1]
  let offset = moveOver * columnOffset
  if (currentColumn == 0) {
    offset -= moveOver / 2
  } else {
    offset += moveOver / 2
  }
  let nextDisabled = false
  const leftMatches = getLeftMatches(rounds)
  if (leftMatches) {
    for (const matches of leftMatches.slice(0, 2)) {
      for (const match of matches) {
        if (!match) {
          continue
        }
        if (!match.left && !match.getTeam1()) {
          nextDisabled = true
          break
        }
        if (!match.right && !match.getTeam2()) {
          nextDisabled = true
          break
        }
      }
    }
  }
  if (showFullBracket) {
    return (
      <div className="tw-flex tw-flex-col tw-gap-40">
        <ScaledBracket
          matchTree={props.matchTree}
          BracketComponent={AddTeamsBracket}
        />
        <ActionButton
          variant="white"
          className="tw-w-full tw-mb-12"
          paddingX={10}
          borderWidth={1}
          onClick={() => {
            setShowFullBracket(false)
            setPage(0)
          }}
        >
          <EditIcon />
          Edit
        </ActionButton>
      </div>
    )
  }
  return (
    <>
      {!showFullBracket && (
        <div
          className={`tw-relative tw-left-[${offset}px] ${
            numRounds < 3 ? 'tw-mt-60' : ''
          }`}
        >
          <AddTeamsBracket
            matchTree={props.matchTree}
            setMatchTree={props.setMatchTree}
            getBracketWidth={() => bracketWidth}
            getTeamWidth={() => getTeamWidth(0)}
            getTeamHeight={() => getTeamHeight(0)}
            getTeamGap={() => 10}
            getFirstRoundMatchGap={() => 15}
            columnsToRender={columnsToRender}
            renderWinnerAndLogo={false}
          />
        </div>
      )}
      <div className="tw-flex tw-flex-col tw-gap-16 tw-w-full">
        <div
          className={`tw-flex tw-justify-center tw-gap-80 tw-text-white/70 tw-font-600 tw-my-8 ${
            page == 0 ? '' : 'tw-flex-row-reverse'
          }`}
        >
          <p>Round 1</p>
          <p>Round 2</p>
        </div>
        <div className={'tw-flex tw-gap-8 tw-mb-12'}>
          {page == 0 && (
            <ActionButton
              variant="white"
              borderWidth={1}
              onClick={() => setPage(page + 1)}
              disabled={nextDisabled}
              className="tw-grow"
            >
              Next
              <ChevronRight />
            </ActionButton>
          )}
          {page == 1 && (
            <>
              <ActionButton
                variant="white"
                paddingX={10}
                borderWidth={1}
                onClick={() => setPage(page - 1)}
              >
                <ChevronLeft />
              </ActionButton>
              <ActionButton
                variant="white"
                fontSize={14}
                className="tw-grow"
                borderWidth={1}
                disabled={!props.matchTree.allTeamsAdded()}
                onClick={() => setShowFullBracket(true)}
              >
                View full bracket
              </ActionButton>
            </>
          )}
        </div>
      </div>
    </>
  )
}

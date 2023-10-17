import React from 'react'
import { PaginatedBracketProps } from '../types'
import { AddTeamsBracket } from './AddTeamsBracket'
import {
  getBracketWidth as getDefaultBracketWidth,
  getTeamWidth,
} from '../../utils'
import { BlueButton } from '../ActionButtons'

export const PaginatedAddTeamsBracket = (props: PaginatedBracketProps) => {
  const [page, setPage] = React.useState(0)
  const disableNext = (currentRoundMatches) =>
    currentRoundMatches.some((match) => match && !match.isPicked())
  const numRounds = props.matchTree.rounds.length
  console.log('numRounds', numRounds)
  const bracketWidth = getDefaultBracketWidth(props.matchTree.rounds.length)
  console.log('bracketWidth', bracketWidth)
  // rounds * 2 - 1 is amount of columns.
  // width of column is bracketwidth / amount of columns
  // move to left most column would be
  // move over amount of columns is rounds - 1
  const numColumns = numRounds * 2 - 1
  const columnWidth = getTeamWidth(numRounds)
  const numColumnsToFirst = numRounds - 1
  const spaceFromLeft = numColumnsToFirst * columnWidth
  console.log('spaceFromLeft', spaceFromLeft)
  const halfWidth = bracketWidth / 2
  console.log('halfWidth', halfWidth)
  const totalColumnWidth = columnWidth * numColumns
  const spaceBetweenColumns = bracketWidth - totalColumnWidth
  const numberOfSpaces = numColumns - 1
  const amountPerSpace = spaceBetweenColumns / numberOfSpaces
  console.log('amountPerSpace', amountPerSpace)
  const moveOver = columnWidth + amountPerSpace
  // const moveOver = 0
  // draw a line in the middle of the screen
  // matchTree to columns that we care about

  const columnsThatWeCareAbout = []
  const currentColumn = page
  const columnOffset = numRounds - 1 - currentColumn

  // page to column

  return (
    <div className={'tw-relative tw-overflow-hidden tw-pt-4'}>
      {page > 0 && (
        <BlueButton
          className={'tw-fixed tw-top-1/2 tw-left-32 tw-z-50'}
          paddingY={32}
          onClick={() => setPage(page - 1)}
        >
          {'<'}
        </BlueButton>
      )}
      <BlueButton
        className={'tw-fixed tw-top-1/2 tw-right-32 tw-z-50'}
        paddingY={32}
        onClick={() => setPage(page + 1)}
      >
        {'>'}
      </BlueButton>
      <div className={`tw-relative tw-left-[${moveOver * columnOffset}px]`}>
        <AddTeamsBracket
          matchTree={props.matchTree}
          setMatchTree={props.setMatchTree}
          page={page}
          setPage={setPage}
          disableNext={disableNext}
        />
      </div>
    </div>
  )
}

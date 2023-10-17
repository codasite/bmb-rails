import React from 'react'
import { PaginatedBracketProps } from '../types'
import { AddTeamsBracket } from './AddTeamsBracket'
import {
  getBracketWidth as getDefaultBracketWidth,
  getTeamWidth,
} from '../../utils'
import { ActionButton } from '../ActionButtons'
import {
  getFinalMatches,
  getLeftMatches,
  getRightMatches,
} from '../../models/operations/GetMatchSections'

export const PaginatedAddTeamsBracket = (props: PaginatedBracketProps) => {
  const [page, setPage] = React.useState(0)
  const numRounds = props.matchTree.rounds.length
  const bracketWidth = getDefaultBracketWidth(props.matchTree.rounds.length)
  const numColumns = numRounds * 2 - 1
  const columnWidth = getTeamWidth(numRounds)
  const totalColumnWidth = columnWidth * numColumns
  const spaceBetweenColumns = bracketWidth - totalColumnWidth
  const numberOfSpaces = numColumns - 1
  const amountPerSpace = spaceBetweenColumns / numberOfSpaces
  const moveOver = columnWidth + amountPerSpace
  const rounds = props.matchTree.rounds
  const matchesInColumns = [
    ...getLeftMatches(rounds),
    ...getFinalMatches(rounds),
    ...getRightMatches(rounds).reverse(),
  ]
  const columnsToPaginate = []
  for (const [columnIndex, matches] of matchesInColumns.entries()) {
    for (const match of matches) {
      // Only paginate columns that have editable matches
      // which have either no left or right match
      if (match && (match.left === null || match.right === null)) {
        columnsToPaginate.push(columnIndex)
        break
      }
    }
  }
  const currentColumn = columnsToPaginate[page]
  const columnOffset = numRounds - 1 - currentColumn
  return (
    <div className={'tw-relative tw-overflow-x-hidden tw-pt-4'}>
      {page > 0 && (
        <ActionButton
          className={'tw-fixed tw-top-1/2 tw-left-32 tw-z-50'}
          paddingY={32}
          paddingX={8}
          backgroundColor={'blue'}
          borderRadius={4}
          textColor={'white'}
          onClick={() => setPage(page - 1)}
        >
          {'<'}
        </ActionButton>
      )}
      {page + 1 < columnsToPaginate.length && (
        <ActionButton
          className={'tw-fixed tw-top-1/2 tw-right-32 tw-z-50'}
          paddingY={32}
          paddingX={8}
          backgroundColor={'blue'}
          borderRadius={4}
          textColor={'white'}
          onClick={() => setPage(page + 1)}
        >
          {'>'}
        </ActionButton>
      )}
      <div className={`tw-relative tw-left-[${moveOver * columnOffset}px]`}>
        <AddTeamsBracket
          matchTree={props.matchTree}
          setMatchTree={props.setMatchTree}
          page={page}
          setPage={setPage}
        />
      </div>
    </div>
  )
}

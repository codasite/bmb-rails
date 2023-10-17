import React from 'react'
import { PaginatedBracketProps } from '../types'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'
import { EditableTeamSlotSwitch } from '../TeamSlot'

export const PaginatedAddTeamsBracket = (props: PaginatedBracketProps) => {
  const [page, setPage] = React.useState(0)
  const disableNext = (currentRoundMatches) =>
    currentRoundMatches.some((match) => match && !match.isPicked())

  return (
    <PaginatedDefaultBracket
      page={page}
      setPage={setPage}
      disableNext={() => false}
      TeamSlotComponent={EditableTeamSlotSwitch}
      {...props}
    />
  )
}

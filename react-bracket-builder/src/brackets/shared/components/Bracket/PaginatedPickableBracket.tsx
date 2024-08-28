import React, { useEffect } from 'react'
import { PaginatedBracketProps, PaginatedDefaultBracketProps } from '../types'
import { PickableBracket } from './PickableBracket'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'
import { VotingTeamSlot } from '../../../../features/VotingBracket/VotingTeamSlot'
import { TeamSlotToggle } from '../TeamSlot'

export const PaginatedPickableBracket = (props: PaginatedBracketProps) => {
  const { matchTree } = props
  const [page, setPage] = React.useState(0)
  const newProps: PaginatedDefaultBracketProps = {
    ...props,
    page,
    setPage,
  }

  return (
    <PickableBracket
      BracketComponent={PaginatedDefaultBracket}
      TeamSlotComponent={matchTree.isVoting ? VotingTeamSlot : TeamSlotToggle}
      {...newProps}
    />
  )
}

import { PaginatedBracketProps } from '../types'
import { PickableBracket } from './PickableBracket'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'
import { VotingTeamSlot } from '../../../../features/VotingBracket/VotingTeamSlot'
import { TeamSlotToggle } from '../TeamSlot'

export const PaginatedPickableBracket = (props: PaginatedBracketProps) => {
  const { matchTree } = props

  return (
    <PickableBracket
      BracketComponent={PaginatedDefaultBracket}
      TeamSlotComponent={matchTree.isVoting ? VotingTeamSlot : TeamSlotToggle}
      {...props}
    />
  )
}

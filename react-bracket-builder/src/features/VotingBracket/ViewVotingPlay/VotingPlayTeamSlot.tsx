import {
  BaseTeamSlot,
  TeamSlotToggle,
} from '../../../brackets/shared/components/TeamSlot'
import { TeamSlotProps } from '../../../brackets/shared/components/types'
import { PercentageTeamSlot } from '../../MostPopularPicks/PercentageTeamSlot'
import { useVotingPlayTrees } from './getVotingPlayTrees'

export const VotingPlayTeamSlot = (props: TeamSlotProps) => {
  const { mostPopularPicksTree } = useVotingPlayTrees()
  if (props.teamPosition === 'winner') {
    return (
      <BaseTeamSlot backgroundColor="white" textColor="dd-blue" {...props} />
    )
  }
  const mppMatch = mostPopularPicksTree.getMatch(
    props.match.roundIndex,
    props.match.matchIndex
  )
  return (
    <PercentageTeamSlot
      {...props}
      teamSlot={<TeamSlotToggle {...props} />}
      chipColor="green"
      showLoserPopularity={true}
      match={mppMatch}
    />
  )
}

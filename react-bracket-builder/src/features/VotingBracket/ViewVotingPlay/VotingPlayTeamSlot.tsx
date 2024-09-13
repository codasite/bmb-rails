import {
  BaseTeamSlot,
  TeamSlotToggle,
} from '../../../brackets/shared/components/TeamSlot'
import { TeamSlotProps } from '../../../brackets/shared/components/types'
import { PercentageTeamSlot } from '../../MostPopularPicks/PercentageTeamSlot'
import { useVotingPlayTrees } from './useVotingPlayTrees'

export const VotingPlayTeamSlot = (props: TeamSlotProps) => {
  const { playTree } = useVotingPlayTrees()
  const playMatch = playTree.getMatch(
    props.match.roundIndex,
    props.match.matchIndex
  )
  if (props.teamPosition === 'winner') {
    return (
      <TeamSlotToggle
        {...props}
        match={playMatch}
        team={playMatch.getWinner()}
      />
    )
  }
  let team = props.team
  if (props.match.roundIndex > props.matchTree.liveRoundIndex) {
    team = playMatch.getTeam(props.teamPosition)
  }
  return (
    <PercentageTeamSlot
      {...props}
      teamSlot={<TeamSlotToggle {...props} match={playMatch} team={team} />}
      chipColor="green"
      showLoserPopularity={true}
    />
  )
}

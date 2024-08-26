import {
  BaseTeamSlot,
  InactiveTeamSlot,
  TeamSlotToggle,
} from '../../brackets/shared/components/TeamSlot'
import { TeamSlotProps } from '../../brackets/shared/components/types'

export const VotingTeamSlot = (props: TeamSlotProps) => {

  if (props.match.roundIndex === props.matchTree.liveRoundIndex) {
    return <TeamSlotToggle {...props} />
  }
  return <TeamSlotToggle {...props} onTeamClick={undefined} />
}

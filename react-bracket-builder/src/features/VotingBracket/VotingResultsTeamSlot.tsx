import {
  BaseTeamSlot,
  TeamSlotToggle,
} from '../../brackets/shared/components/TeamSlot'
import { TeamSlotProps } from '../../brackets/shared/components/types'
import { PopularityTeamSlot } from '../MostPopularPicks/PopularityTeamSlot'

export const VotingResultsTeamSlot = (props: TeamSlotProps) => {
  if (props.teamPosition === 'winner') {
    return (
      <TeamSlotToggle
        {...props}
        activeTeamSlot={
          <BaseTeamSlot
            backgroundColor="green"
            textColor="dd-blue"
            {...props}
          />
        }
      />
    )
  }
  let team = props.team
  if (isNaN(props.match._pick?.popularity)) {
    team = null
  }
  return (
    <PopularityTeamSlot
      teamSlot={
        <TeamSlotToggle
          activeTeamSlot={
            <BaseTeamSlot borderWidth={2} borderColor="green" {...props} />
          }
          {...props}
          team={team}
        />
      }
      chipColor="green"
      showLoserPopularity={true}
      {...props}
    />
  )
}

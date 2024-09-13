import {
  BaseTeamSlot,
  TeamSlotToggle,
} from '../../brackets/shared/components/TeamSlot'
import { TeamSlotProps } from '../../brackets/shared/components/types'
import { PercentageTeamSlot } from '../MostPopularPicks/PercentageTeamSlot'

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
  return (
    <PercentageTeamSlot
      teamSlot={
        <TeamSlotToggle
          activeTeamSlot={
            <BaseTeamSlot borderWidth={2} borderColor="green" {...props} />
          }
          {...props}
        />
      }
      chipColor="green"
      showLoserPopularity={true}
      {...props}
    />
  )
}

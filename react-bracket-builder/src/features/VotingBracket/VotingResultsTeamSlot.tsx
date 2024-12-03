import { useContext } from 'react'
import {
  BaseTeamSlot,
  TeamSlotToggle,
} from '../../brackets/shared/components/TeamSlot'
import { TeamSlotProps } from '../../brackets/shared/components/types'
import { PopularityTeamSlot } from '../MostPopularPicks/PopularityTeamSlot'
import { BracketMetaContext } from '../../brackets/shared/context/context'

export const VotingResultsTeamSlot = (props: TeamSlotProps) => {
  const { isOpen } = useContext(BracketMetaContext)
  let team = props.team
  if (
    isNaN(props.match._pick?.popularity) ||
    (props.teamPosition === 'winner' && isOpen)
  ) {
    team = null
  }
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
        team={team}
      />
    )
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

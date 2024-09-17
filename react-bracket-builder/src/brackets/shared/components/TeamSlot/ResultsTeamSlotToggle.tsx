import { TeamSlotProps } from '../types'
import { BaseTeamSlot } from './BaseTeamSlot'
import { TeamSlotToggle } from './TeamSlotToggle'

export const ResultsTeamSlotToggle = (props: TeamSlotProps) => {
  return (
    <TeamSlotToggle
      {...props}
      activeTeamSlot={
        <BaseTeamSlot
          {...props}
          textColor={'dd-blue'}
          backgroundColor={'green'}
        />
      }
    />
  )
}

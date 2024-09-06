import { useContext } from 'react'
import { TeamSlotToggle } from '../../brackets/shared/components/TeamSlot'
import { TeamSlotProps } from '../../brackets/shared/components/types'
import { DarkModeContext } from '../../brackets/shared/context/context'

export const VotingTeamSlot = (props: TeamSlotProps) => {
  const { darkMode } = useContext(DarkModeContext)
  if (props.match.roundIndex === props.matchTree.liveRoundIndex) {
    return <TeamSlotToggle {...props} />
  }
  return (
    <TeamSlotToggle
      {...props}
      borderColor={darkMode ? 'white/25' : 'dd-blue/25'}
      textColor={darkMode ? 'white/75' : 'dd-blue/50'}
      onTeamClick={undefined}
    />
  )
}

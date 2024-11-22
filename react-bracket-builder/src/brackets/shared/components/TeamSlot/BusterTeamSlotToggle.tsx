// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'
import { getBustTrees } from '../../../BracketBuilders/BustPlayPage/utils'

export const BusterTeamSlotToggle = (props: TeamSlotProps) => {
  const { busteeTree } = getBustTrees()
  const roundIndex = props.match.roundIndex
  const matchIndex = props.match.matchIndex

  const busterMatch = props.match
  const busteeMatch = busteeTree.rounds[roundIndex].matches[matchIndex]

  const busterTeam = props.team
  const busteeTeam =
    props.teamPosition === 'left'
      ? busteeMatch.getTeam1()
      : busteeMatch.getTeam2()

  const busterPicked = busterTeam && busterMatch.getWinner() === busterTeam
  const busteePicked = busteeTeam && busteeMatch.getWinner() === busteeTeam

  // If current team is null, set it to the bustee team
  const team = props.team ?? busteeTeam

  // if both buster and bustee picked, show red box with blue border
  if (busterPicked && busteePicked && team && team.equals(busteeTeam)) {
    return (
      <BaseTeamSlot
        textColor={'white'}
        backgroundColor={'red'}
        borderColor={'blue'}
        {...props}
      />
    )
  } else if (busterPicked) {
    // if only buster picked, show red box
    return (
      <BaseTeamSlot {...props} textColor={'white'} backgroundColor={'red'} />
    )
  } else if (busteePicked && (team ? team.equals(busteeTeam) : true)) {
    // if only bustee picked, show blue border
    return <InactiveTeamSlot {...props} borderColor="blue" />
  }

  // if neither buster nor bustee picked, show inactive
  return <InactiveTeamSlot {...props} />
}

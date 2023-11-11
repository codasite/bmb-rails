import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { ActiveTeamSlot } from './ActiveTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'
import {
  BusteeMatchTreeContext,
  BusterMatchTreeContext,
} from '../../context/context'
import { getBustTrees } from '../../../BracketBuilders/BustPlayPage/utils'

export const BusterTeamSlotToggle = (props: TeamSlotProps) => {
  const { team, match, teamPosition } = props

  const { busteeTree } = getBustTrees()
  const roundIndex = match.roundIndex
  const matchIndex = match.matchIndex

  const busterMatch = match
  const busteeMatch = busteeTree.rounds[roundIndex].matches[matchIndex]

  const busterTeam = team
  const busteeTeam =
    teamPosition === 'left' ? busteeMatch.getTeam1() : busteeMatch.getTeam2()

  const busterPicked = busterTeam && busterMatch.getWinner() === busterTeam
  const busteePicked = busteeTeam && busteeMatch.getWinner() === busteeTeam

  // If current team is null, set it to the bustee team
  props.team = team ? team : busteeTeam

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

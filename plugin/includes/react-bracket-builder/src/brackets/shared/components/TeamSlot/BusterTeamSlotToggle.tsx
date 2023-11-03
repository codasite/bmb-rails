import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { ActiveTeamSlot } from './ActiveTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'
import { BusteeMatchTreeContext, BusterMatchTreeContext } from '../../context'
import { getBustTrees } from '../../../BracketBuilders/BustPlayPage/utils'

export const BusterTeamSlotToggle = (props: TeamSlotProps) => {
  const { team, match } = props

  const { busterTree, busteeTree } = getBustTrees()
  const teamPosition = team && team === match.getTeam1() ? 'left' : 'right'
  const roundIndex = match.roundIndex
  const matchIndex = match.matchIndex

  const busterMatch = busterTree.rounds[roundIndex].matches[matchIndex]
  const busteeMatch = busteeTree.rounds[roundIndex].matches[matchIndex]

  const busterTeam =
    teamPosition === 'left' ? busterMatch.getTeam1() : busterMatch.getTeam2()
  const busteeTeam =
    teamPosition === 'left' ? busteeMatch.getTeam1() : busteeMatch.getTeam2()

  const busterPicked = busterTeam && busterMatch.getWinner() === busterTeam
  const busteePicked = busteeTeam && busteeMatch.getWinner() === busteeTeam

  if (busterPicked && busteePicked && team.equals(busteeTeam)) {
    return (
      <BaseTeamSlot
        textColor={'white'}
        backgroundColor={'red'}
        borderColor={'blue'}
        {...props}
      />
    )
  } else if (busterPicked) {
    return (
      <BaseTeamSlot textColor={'white'} backgroundColor={'red'} {...props} />
    )
  } else if (busteePicked && team.equals(busteeTeam)) {
    return <InactiveTeamSlot borderColor="blue" {...props} />
  }

  return <InactiveTeamSlot {...props} />
}

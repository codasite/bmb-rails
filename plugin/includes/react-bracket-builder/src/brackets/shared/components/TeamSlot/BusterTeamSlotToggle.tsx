import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { ActiveTeamSlot } from './ActiveTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'
import { BusteeMatchTreeContext, BusterMatchTreeContext } from '../../context'

export const BusterTeamSlotToggle = (props: TeamSlotProps) => {
  const { team, match } = props

  const { matchTree: busteeMatchTree } = useContext(BusteeMatchTreeContext)
  const { matchTree: busterMatchTree } = useContext(BusterMatchTreeContext)

  const picked = team && match.getWinner() === team ? true : false
  const teamPosition = team && team === match.getTeam1() ? 'left' : 'right'
  const roundIndex = match.roundIndex
  const matchIndex = match.matchIndex

  const busterMatch = busterMatchTree.rounds[roundIndex].matches[matchIndex]
  const busteeMatch = busteeMatchTree.rounds[roundIndex].matches[matchIndex]

  const busterTeam =
    teamPosition === 'left' ? busterMatch.getTeam1() : busterMatch.getTeam2()
  const busteeTeam =
    teamPosition === 'left' ? busteeMatch.getTeam1() : busteeMatch.getTeam2()

  const busterPicked = busterTeam && busterMatch.getWinner() === busterTeam
  const busteePicked = busteeTeam && busteeMatch.getWinner() === busteeTeam

  if (busterPicked && busteePicked && team.equals(busteeTeam)) {
    console.log('BustableTeamSlotToggle both picked', team, busterTeam)
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

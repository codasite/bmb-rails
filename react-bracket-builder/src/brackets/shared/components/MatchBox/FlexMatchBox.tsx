// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { MatchBoxProps, TeamPosition } from '../types'
import { Nullable } from '../../../../utils/types'
//@ts-ignore
import { FlexTeamSlot } from '../TeamSlot'
import { Team } from '../../models/Team'

const FlexMatchGap = (props: any) => {
  return (
    <div className="tw-flex-grow tw-flex-shrink tw-flex-basis-10 tw-max-h-[16px] tw-min-h-[4px]"></div>
  )
}

export const FlexMatchBox = (props: MatchBoxProps) => {
  const {
    match,
    matchPosition,
    matchTree,
    setMatchTree,
    TeamSlotComponent = FlexTeamSlot,
    teamGap = 8,
    teamHeight = 24,
    onTeamClick,
  } = props

  const center = matchPosition === 'center'

  if (!match) {
    return <> </>
  }

  const getTeamSlot = (
    team: Nullable<Team> | undefined,
    teamPosition: TeamPosition
  ) => {
    return (
      <TeamSlotComponent
        team={team}
        match={match}
        matchTree={matchTree}
        setMatchTree={setMatchTree}
        matchPosition={matchPosition}
        teamPosition={teamPosition}
        height={teamHeight}
        onTeamClick={onTeamClick}
      />
    )
  }

  return (
    <div
      className={`tw-flex tw-flex-col tw-items-center tw-gap-[${teamGap}px]${
        center ? ' tw-pb-16' : ''
      }`}
    >
      {getTeamSlot(match.getTeam1(), 'left')}
      {getTeamSlot(match.getTeam2(), 'right')}
    </div>
  )
}

import React from 'react';
import { MatchNode, Round, Team } from '../models/MatchTree';
import { TeamSlot } from './TeamSlot'
import { Direction } from '../constants'
import { MatchBoxProps } from './types';
import { Nullable } from '../../../utils/types';

export const DefaultMatchBox = (props: MatchBoxProps) => {
	const {
		match,
		position,
		matchTree,
		setMatchTree,
		TeamSlotComponent,
		teamGap,
		teamHeight,
	} = props

	const center = position === 'center'
	const offset = teamHeight + teamGap

	const getTeamSlot = (team: Nullable<Team> | undefined) => {
		return (
			<TeamSlotComponent
				team={team}
				match={match}
				matchTree={matchTree}
				setMatchTree={setMatchTree}
				position={position}
				teamHeight={teamHeight}
			/>
		)
	}

	return (
		<div className={`tw-flex tw-flex-col tw-gap-[${teamGap}px] tw-translate-y-[${center ? -offset : 0}px]`}>
			{getTeamSlot(match?.team1)}
			{getTeamSlot(match?.team2)}
		</div>
	)
}
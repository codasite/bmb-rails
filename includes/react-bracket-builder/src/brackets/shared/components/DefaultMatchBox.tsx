import React from 'react';
import { MatchNode, Round, Team } from '../models/MatchTree';
import { TeamSlot } from './TeamSlot'
import { Direction } from '../constants'
import { MatchBoxProps } from './types';
import { Nullable } from '../../../utils/types';

export const DefaultMatchBox = (props: MatchBoxProps) => {
	const {
		match,
		matchPosition,
		matchTree,
		setMatchTree,
		TeamSlotComponent,
		teamGap,
		teamHeight,
	} = props

	const center = matchPosition === 'center'
	const offset = teamHeight + teamGap

	if (!match) {
		return (
			<div className={`tw-h-[${teamHeight * 2 + teamGap}px]`} />
		)
	}

	const getTeamSlot = (team: Nullable<Team> | undefined, teamPosition: string) => {
		return (
			<TeamSlotComponent
				team={team}
				match={match}
				matchTree={matchTree}
				setMatchTree={setMatchTree}
				matchPosition={matchPosition}
				teamPosition={teamPosition}
				teamHeight={teamHeight}
			/>
		)
	}

	return (
		<div className={`tw-flex tw-flex-col tw-gap-[${teamGap}px] tw-translate-y-[${center ? -offset : 0}px]`}>
			{getTeamSlot(match.team1, 'left')}
			{getTeamSlot(match.team2, 'right')}
		</div>
	)
}
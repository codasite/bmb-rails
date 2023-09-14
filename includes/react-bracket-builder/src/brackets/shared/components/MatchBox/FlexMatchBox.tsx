import React from 'react';
import { MatchNode, Round, Team, MatchTree } from '../../models/MatchTree';
import { Direction, bracketConstants } from '../../constants'
import { MatchBoxProps, TeamSlotProps } from '../types';
import { Nullable } from '../../../../utils/types';
import { getUniqueTeamClass } from '../../utils';
//@ts-ignore
import { ReactComponent as BracketLogo } from '../../assets/BMB-ICON-CURRENT.svg'
import { DefaultTeamSlot, FlexTeamSlot } from '../TeamSlot';

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
	const offset = teamHeight + teamGap

	if (!match) {
		return <>	</>
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
				height={teamHeight}
				onTeamClick={onTeamClick}
			/>
		)
	}


	return (
		<div className={`tw-flex tw-flex-col tw-gap-[${teamGap}px]${center ? ' tw-pb-16' : ''}`}>
			{getTeamSlot(match.getTeam1(), 'left')}
			{getTeamSlot(match.getTeam2(), 'right')}
		</div>
	)
}
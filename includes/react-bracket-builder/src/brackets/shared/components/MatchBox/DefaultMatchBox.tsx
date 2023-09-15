import React, { useContext } from 'react';
import { MatchNode, Round, Team, MatchTree } from '../../models/MatchTree';
import { Direction, bracketConstants } from '../../constants'
import { MatchBoxProps, TeamSlotProps } from '../types';
import { Nullable } from '../../../../utils/types';
import { getUniqueTeamClass } from '../../utils';
//@ts-ignore
import { ReactComponent as BracketLogo } from '../../assets/BMB-ICON-CURRENT.svg'
import { DefaultTeamSlot } from '../TeamSlot';
import { Bracket } from '../Bracket/Bracket';
import { BracketMetaContext } from '../../context';
import { FinalTeamSlot } from '../TeamSlot/FinalTeamSlot';
import { DefaultFinalMatchChildren } from './Children/DefaultFinalMatchChildren';

export const DefaultMatchBox = (props: MatchBoxProps) => {
	const {
		match,
		matchPosition,
		matchTree,
		setMatchTree,
		TeamSlotComponent = DefaultTeamSlot,
		MatchBoxChildComponent = DefaultFinalMatchChildren,
		teamGap = 20,
		teamHeight = 28,
		onTeamClick,
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
				height={teamHeight}
				onTeamClick={onTeamClick}
			/>
		)
	}


	return (
		<div className={`tw-flex tw-flex-col tw-gap-[${teamGap}px] tw-translate-y-[${center ? -offset : 0}px]`}>
			{getTeamSlot(match.getTeam1(), 'left')}
			{getTeamSlot(match.getTeam2(), 'right')}
			<MatchBoxChildComponent
				match={match}
				matchTree={matchTree}
				matchPosition={matchPosition}
			/>
		</div>
	)
}
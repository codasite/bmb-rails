import React from 'react';
import { MatchNode, Round, Team } from '../models/MatchTree';
import { TeamSlot } from './TeamSlot'
import { Direction } from '../constants'
import { MatchBoxProps } from './types';
import { Nullable } from '../../../utils/types';
import { getUniqueTeamClass } from '../utils';

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

	const getFinalMatchChildren = () => {
		const winnerClass = getUniqueTeamClass(match.roundIndex, match.matchIndex, 'winner')
		return (
			<div className='tw-flex tw-flex-col tw-gap-16 tw-absolute tw-bottom-[150px] tw-items-center tw-left-[50%] tw-translate-x-[-50%]'>
				<span className='tw-text-64 tw-font-700 tw-whitespace-nowrap'>Bracket Title</span>
				<div className={`${winnerClass} tw-h-[52px] tw-w-[257px] tw-border tw-border-solid tw-border-white `}>
					<span className='tw-text-36 tw-font-700 tw-text-dd-blue dark:tw-text-white'>
						{match.getWinner()?.name}
					</span>
				</div>
			</div>
		)
	}

	return (
		<div className={`tw-flex tw-flex-col tw-gap-[${teamGap}px] tw-translate-y-[${center ? -offset : 0}px]`}>
			{getTeamSlot(match.getTeam1(), 'left')}
			{getTeamSlot(match.getTeam2(), 'right')}
			{match.parent === null &&
				getFinalMatchChildren()
			}
		</div>
	)
}
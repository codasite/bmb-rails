import React from 'react';
import { MatchNode, Round, Team } from '../models/MatchTree';
import { TeamSlot } from './TeamSlot'
import { Direction } from '../constants'
import { MatchBoxProps } from './types';
import { Nullable } from '../../../utils/types';
import { getUniqueTeamClass } from '../utils';
//@ts-ignore
import { ReactComponent as BracketLogo } from '../assets/BMB-ICON-CURRENT.svg'

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
				height={teamHeight}
			/>
		)
	}

	const getFinalMatchChildren = () => {
		return (
			<>
				<div className='tw-flex tw-flex-col tw-gap-16 tw-absolute tw-bottom-[150px] tw-items-center tw-left-[50%] tw-translate-x-[-50%]'>
					<span className='tw-text-64 tw-font-700 tw-whitespace-nowrap'>Bracket Title</span>
					<TeamSlotComponent
						team={match.getWinner()}
						match={match}
						matchTree={matchTree}
						matchPosition={matchPosition}
						teamPosition={'winner'}
						height={52}
						width={257}
						fontSize={36}
						fontWeight={700}
					/>
				</div>
				{/* <div className='tw-absolute tw-bottom-0 tw-left-[50%] tw-translate-x-[-50%] tw-text-black/20 dark:tw-text-white/20 '> */}
				<div className='tw-absolute tw-left-[50%] tw-translate-x-[-50%]'>
					{/* <div> */}

					<BracketLogo className={'tw-w-[154px]'} />
				</div>
				{/* </div> */}

			</>
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
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

export const DefaultMatchBox = (props: MatchBoxProps) => {
	const {
		match,
		matchPosition,
		matchTree,
		setMatchTree,
		TeamSlotComponent = DefaultTeamSlot,
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
			{match.parent === null &&
				// <FinalMatchChildren
				// 	match={match}
				// 	matchTree={matchTree}
				// 	matchPosition={matchPosition}
				// 	TeamSlotComponent={TeamSlotComponent}
				// />
				<>
					<div className={`tw-flex tw-flex-col tw-gap-16 tw-absolute tw-bottom-[${winnerContainerBottom[numRounds]}px] tw-items-center tw-left-[50%] tw-translate-x-[-50%]`}>
						<span className='tw-text-64 tw-font-700 tw-whitespace-nowrap tw-text-dd-blue dark:tw-text-white'>{bracketTitle}</span>
						<FinalTeamSlot
							match={match}
							matchTree={matchTree}
						/>
					</div>
					{/* <div className='tw-absolute tw-bottom-0 tw-left-[50%] tw-translate-x-[-50%] tw-text-black/20 dark:tw-text-white/20 '> */}
					<div className={`tw-absolute tw-flex tw-flex-col tw-gap-20 tw-justify-between tw-items-center tw-left-[50%] tw-translate-x-[-50%] tw-bottom-[${bracketLogoBottom[numRounds]}px] tw-text-dd-blue dark:tw-text-white tw-text-36 tw-font-700 tw-whitespace-nowrap `}>
						<span>{sloganText}</span>
						<BracketLogo className={'tw-w-[124px] tw-text-black/25 dark:tw-text-white'} />
						<span>{bracketDate}</span>
					</div>
				</>
			}
		</div>
	)
}
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
import { FinalMatchChildrenProps } from '../types';


export const FinalMatchChildren = (props: FinalMatchChildrenProps) => {
	const {
		match,
		matchTree,
		matchPosition,
		TeamSlotComponent,
		sloganText = 'Who You Got?',
		bracketLogoBottom = bracketConstants.bracketLogoBottom,
		winnerContainerBottom = bracketConstants.winnerContainerBottom,
	} = props

	const numRounds = matchTree.rounds.length

	const bracketMeta = useContext(BracketMetaContext)

	const {
		title: bracketTitle,
		date: bracketDate,
	} = bracketMeta

	return (
		<>
			<div className={`tw-flex tw-flex-col tw-gap-16 tw-absolute tw-bottom-[${winnerContainerBottom[numRounds]}px] tw-items-center tw-left-[50%] tw-translate-x-[-50%]`}>
				<span className='tw-text-64 tw-font-700 tw-whitespace-nowrap tw-text-dd-blue dark:tw-text-white'>{bracketTitle}</span>
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
			<div className={`tw-absolute tw-flex tw-flex-col tw-gap-20 tw-justify-between tw-items-center tw-left-[50%] tw-translate-x-[-50%] tw-bottom-[${bracketLogoBottom[numRounds]}px] tw-text-dd-blue dark:tw-text-white tw-text-36 tw-font-700 tw-whitespace-nowrap `}>
				<span>{sloganText}</span>
				<BracketLogo className={'tw-w-[124px] tw-text-black/25 dark:tw-text-white'} />
				<span>{bracketDate}</span>
			</div>
		</>
	)
}
import React, { useContext } from 'react';
import { MatchNode, Round, Team, MatchTree } from '../../../models/MatchTree';
import { Direction, bracketConstants } from '../../../constants'
import { MatchBoxChildProps, MatchBoxProps, TeamSlotProps } from '../../types';
import { Nullable } from '../../../../../utils/types';
import { getUniqueTeamClass } from '../../../utils';
//@ts-ignore
import { ReactComponent as BracketLogo } from '../../assets/BMB-ICON-CURRENT.svg'
import { DefaultTeamSlot } from '../../TeamSlot';
import { Bracket } from '../../Bracket/Bracket';
import { BracketMetaContext } from '../../../context';
import { FinalTeamSlot } from '../../TeamSlot/FinalTeamSlot';

interface WinnerContainerProps extends MatchBoxChildProps {
	bottom?: number[],
}

export const WinnerContainer = (props: WinnerContainerProps) => {
	const {
		match,
		matchTree,
		bottom = bracketConstants.winnerContainerBottom,
	} = props

	const numRounds = matchTree.rounds.length
	const bracketMeta = useContext(BracketMetaContext)

	const {
		title: bracketTitle,
	} = bracketMeta

	return (
		<div className={`tw-flex tw-flex-col tw-gap-16 tw-absolute tw-bottom-[${bottom[numRounds]}px] tw-items-center tw-left-[50%] tw-translate-x-[-50%]`}>
			{/* <span className='tw-text-64 tw-font-700 tw-whitespace-nowrap tw-text-dd-blue dark:tw-text-white'>{bracketTitle}</span> */}
			<span className='tw-text-64 tw-font-700 tw-whitespace-nowrap tw-text-dd-blue dark:tw-text-white'>HIHHIHHII</span>
			<FinalTeamSlot
				match={match}
				matchTree={matchTree}
			/>
		</div>
	)

}
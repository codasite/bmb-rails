import React, { useContext } from 'react';
import { bracketConstants } from '../../../constants'
import { MatchBoxChildProps } from '../../types';
//@ts-ignore
import { DefaultTeamSlot } from '../../TeamSlot';
import { BracketMetaContext } from '../../../context';

interface WinnerContainerProps extends MatchBoxChildProps {
	topText?: string,
	topTextFontSize?: number,
	topTextColor?: string,
	topTextColorDark?: string,
	bottom?: number[],
}

export const WinnerContainer = (props: WinnerContainerProps) => {
	const {
		match,
		matchTree,
		bottom = bracketConstants.winnerContainerBottom,
		TeamSlotComponent = DefaultTeamSlot,
		topText = 'Winner',
		topTextFontSize = 64,
		topTextColor = 'dd-blue',
		topTextColorDark = 'white',
	} = props

	const numRounds = matchTree.rounds.length

	return (
		<div className={`tw-flex tw-flex-col tw-gap-16 tw-absolute tw-bottom-[${bottom[numRounds]}px] tw-items-center tw-left-[50%] tw-translate-x-[-50%]`}>
			<span className={`tw-text-${topTextFontSize} tw-text-${topTextColor} dark:tw-text-${topTextColorDark} tw-font-700 tw-whitespace-nowrap`}>{topText}</span>
			<TeamSlotComponent
				match={match}
				matchTree={matchTree}
				team={match.getWinner()}
				teamPosition={'winner'}
				height={52}
				width={257}
				fontSize={36}
				fontWeight={700}
			/>
		</div>
	)

}
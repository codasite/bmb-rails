import React, { useContext } from 'react';
import { defaultBracketConstants } from '../../../constants'
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
		TeamSlotComponent = DefaultTeamSlot,
		topText = 'Winner',
		topTextFontSize = 48,
		topTextColor = 'dd-blue',
		topTextColorDark = 'white',
	} = props

	const numRounds = matchTree.rounds.length

	return (
		<div className={`tw-flex tw-flex-col tw-gap-24 tw-items-center`}>
			<span className={`tw-text-${topTextFontSize} tw-text-${topTextColor} dark:tw-text-${topTextColorDark} tw-font-700 tw-max-w-[700px] tw-text-center tw-leading-none`}>{topText}</span>
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
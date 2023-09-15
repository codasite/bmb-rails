import React, { useContext } from 'react';
import { MatchNode, Round, Team, MatchTree } from '../../../models/MatchTree';
import { Direction, bracketConstants } from '../../../constants'
import { MatchBoxChildProps, MatchBoxProps, TeamSlotProps } from '../../types';
import { Nullable } from '../../../../../utils/types';
import { getUniqueTeamClass } from '../../../utils';
//@ts-ignore
import { ReactComponent as BracketLogo } from '../../../assets/BMB-ICON-CURRENT.svg'
import { DefaultTeamSlot } from '../../TeamSlot';
import { Bracket } from '../../Bracket/Bracket';
import { BracketMetaContext } from '../../../context';
import { FinalTeamSlot } from '../../TeamSlot/FinalTeamSlot';

interface LogoContainerProps extends MatchBoxChildProps {
	sloganText?: string,
	bottom?: number[],
}

export const LogoContainer = (props: LogoContainerProps) => {
	const {
		matchTree,
		sloganText = 'Who You Got?',
		bottom = bracketConstants.bracketLogoBottom,
	} = props

	const numRounds = matchTree.rounds.length

	const { date: bracketDate } = useContext(BracketMetaContext)

	return (
		<div className={`tw-absolute tw-flex tw-flex-col tw-gap-20 tw-justify-between tw-items-center tw-left-[50%] tw-translate-x-[-50%] tw-bottom-[${bottom[numRounds]}px] tw-text-dd-blue dark:tw-text-white tw-text-36 tw-font-700 tw-whitespace-nowrap `}>
			<span>{sloganText}</span>
			<BracketLogo className={'tw-w-[124px] tw-text-black/25 dark:tw-text-white'} />
			<span>{bracketDate}</span>
		</div>
	)

}
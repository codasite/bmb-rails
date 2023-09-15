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
import { WinnerContainer } from './WinnerContainer';
import { LogoContainer } from './LogoContainer';


export const DefaultFinalMatchChildren = (props: MatchBoxChildProps) => {
	const {
		matchPosition,
	} = props

	return (
		matchPosition === 'center' ?
			<>
				<WinnerContainer {...props} />
				{/* <div className='tw-absolute tw-bottom-0 tw-left-[50%] tw-translate-x-[-50%] tw-text-black/20 dark:tw-text-white/20 '> */}
				<LogoContainer	{...props} />
			</>
			: <></>
	)
}
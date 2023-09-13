import React, { } from 'react';
import { Nullable } from '../../../utils/types';
import { Round, MatchNode } from '../models/MatchTree';
import { MatchBox } from './MatchBox';
import { DefaultTeamSlot, TeamSlot } from './TeamSlot'
//@ts-ignore
import { ReactComponent as BracketLogo } from '../assets/BMB-ICON-CURRENT.svg';
import { MatchColumnProps } from './types';
//@ts-ignore
import { Direction } from '../constants'
import { getMatchBoxHeight } from '../utils'
import { DefaultMatchBox } from './DefaultMatchBox';


export const DefaultMatchColumn = (props: MatchColumnProps) => {
	const {
		matches,
		matchPosition,
		matchTree,
		setMatchTree,
		MatchBoxComponent = DefaultMatchBox,
		TeamSlotComponent,
		matchGap,
		teamGap = 20,
		teamHeight = 28,
		onTeamClick,
	} = props

	return (
		<div className={`tw-flex tw-flex-col tw-justify-center tw-gap-[${matchGap}px]`}>
			{matches.map((match, index) => {
				return (
					<MatchBoxComponent
						key={index}
						match={match}
						matchPosition={matchPosition}
						matchTree={matchTree}
						setMatchTree={setMatchTree}
						TeamSlotComponent={TeamSlotComponent}
						teamGap={teamGap}
						teamHeight={teamHeight}
						onTeamClick={onTeamClick}
					/>
				)
			})}
		</div>
	)
}
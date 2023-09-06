import React, { } from 'react';
import { Nullable } from '../../../utils/types';
import { Round, MatchNode } from '../models/MatchTree';
import { MatchBox } from './MatchBox';
import { TeamSlot } from './TeamSlot'
//@ts-ignore
import { ReactComponent as BracketLogo } from '../assets/BMB-ICON-CURRENT.svg';
import { MatchColumnProps } from './types';
//@ts-ignore
import { Direction } from '../constants'
import { getMatchBoxHeight } from '../utils'


export const MatchColumnBase = (props: MatchColumnProps) => {
	const {
		matches,
		position,
		matchTree,
		setMatchTree,
		MatchBoxComponent,
		TeamSlotComponent,
	} = props

	return (
		<div>
			{matches.map((match, index) => {
				return (
					<MatchBoxComponent
						key={index}
						match={match}
						position={position}
						matchTree={matchTree}
						setMatchTree={setMatchTree}
						TeamSlotComponent={TeamSlotComponent}
					/>
				)
			})}
		</div>
	)
}
import React from 'react';
import { MatchNode, Round } from '../models/MatchTree';
import { TeamSlot } from './TeamSlot'
import { Direction } from '../constants'
import { MatchBoxProps } from './types';

export const MatchBoxBase = (props: MatchBoxProps) => {
	const {
		match,
		position,
		matchTree,
		setMatchTree,
		TeamSlotComponent
	} = props

	return (
		<div>
			<TeamSlotComponent
				team={match?.team1}
				match={match}
				matchTree={matchTree}
				setMatchTree={setMatchTree}
				position={position}
			/>
			<TeamSlotComponent
				team={match?.team2}
				match={match}
				matchTree={matchTree}
				setMatchTree={setMatchTree}
				position={position}
			/>
		</div>
	)
}
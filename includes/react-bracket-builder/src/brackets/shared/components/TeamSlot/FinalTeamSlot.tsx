import React from 'react';
import { MatchNode, Team } from '../../models/MatchTree';
import { TeamSlotProps } from '../types';
import { BaseTeamSlot } from './BaseTeamSlot';

export const FinalTeamSlot = (props: TeamSlotProps) => {
	const {
		match,
	} = props

	return (
		<BaseTeamSlot
			{...props}
			team={match.getWinner()}
			teamPosition={'winner'}
			height={52}
			width={257}
			fontSize={36}
			fontWeight={700}
		/>
	)
}

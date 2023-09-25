import React from 'react';
import { MatchNode, Team } from '../../models/MatchTree';
import { BracketProps } from '../types';
import { DefaultBracket } from '../Bracket/DefaultBracket';
import { TeamSlotToggle } from '../TeamSlot';
import { Nullable } from '../../../../utils/types';
import { PickableBracket } from './PickableBracket';
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket';


export const PaginatedPickableBracket = (props: BracketProps) => {
	const {
		matchTree,
		setMatchTree,
		BracketComponent = PickableBracket,
	} = props

	return (
		<BracketComponent
			BracketComponent={PaginatedDefaultBracket}
			{...props}
		/>
	)
};
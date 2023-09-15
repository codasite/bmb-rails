import React from 'react';
import { MatchNode, Team } from '../../models/MatchTree';
import { BracketProps } from '../types';
import { DefaultBracket } from './DefaultBracket';
import { EditableTeamSlotSwitch } from '../TeamSlot';


export const AddTeamsBracket = (props: BracketProps) => {
	return (
		<DefaultBracket
			{...props}
			TeamSlotComponent={EditableTeamSlotSwitch}
		/>
	)
};
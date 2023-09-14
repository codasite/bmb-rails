import React from 'react';
import { MatchNode, Team } from '../../models/MatchTree';
import { BracketProps } from '../types';
import { DefaultBracket } from '../Bracket/DefaultBracket';
import { TeamSlotToggle } from '../TeamSlot';


export const PickableBracket = (props: BracketProps) => {
	const {
		matchTree,
		setMatchTree,
	} = props

	const handleTeamClick = (match: MatchNode, team: Team) => {
		if (!match) {
			return;
		}
		if (!setMatchTree) {
			return
		}
		if (!team) {
			return;
		}
		match.pick(team);
		setMatchTree(matchTree);
	}

	return (
		<DefaultBracket
			{...props}
			TeamSlotComponent={TeamSlotToggle}
			onTeamClick={handleTeamClick}
		/>
	)
};
import React, { useContext } from 'react';
import { Round, MatchNode, Team } from '../models/MatchTree';
import { BracketLines, RootMatchLines } from './BracketLines'
import {
	getFirstRoundMatchGap,
	getMatchGap,
	getBracketHeight,
	getBracketWidth,
} from '../utils'
import { Nullable } from '../../../utils/types';
import { BracketProps } from './types';
import { DarkModeContext } from '../context';
import { DefaultBracket } from './DefaultBracket';
import { bracketConstants } from '../constants';
import { TeamSlotToggle } from './TeamSlot';


export const PickableBracket = (props: BracketProps) => {
	const {
		matchTree,
		setMatchTree,
	} = props

	// const numRounds = matchTree?.rounds.length;
	// const bracketHeight = getTargetHeight(numRounds);
	// const bracketWidth = getTargetWidth(numRounds);
	// const teamHeight = bracketConstants.teamHeight;
	// const teamGap = bracketConstants.teamGap;

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
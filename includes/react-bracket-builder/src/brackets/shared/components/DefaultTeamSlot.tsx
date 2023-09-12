import React, { useState, useContext } from 'react';
import { MatchNode, Round, Team } from '../models/MatchTree';
//@ts-ignore
import { getTeamClasses } from '../utils';
import { bracketConstants } from '../constants';
import { BracketContext } from '../context';
import { TeamSlotProps } from './types';
import { getUniqueTeamClass } from '../utils';

export const DefaultTeamSlot = (props: TeamSlotProps) => {
	const [editing, setEditing] = useState(false)
	const [textBuffer, setTextBuffer] = useState('')
	const bracket = useContext(BracketContext);

	const {
		team,
		match,
		matchTree,
		setMatchTree,
		matchPosition,
		teamPosition,
		height,
		width = 115,
		fontWeight = 500,
		fontSize = 16,
		getTeamClass = getUniqueTeamClass,
	} = props
	// console.log('winner', winner)
	const teamClass = getTeamClass(match.roundIndex, match.matchIndex, teamPosition)
	return (
		<div className={`${teamClass} tw-flex tw-justify-center tw-items-center tw-whitespace-nowrap tw-w-[${width}px] tw-h-[${height}px] tw-border-2 tw-border-solid tw-border-dd-blue/50 dark:tw-border-white/50 tw-text-14 tw-font-500 tw-text-dd-blue dark:tw-text-white`}>
			<span className={`tw-font-${fontWeight} tw-text-${fontSize}`}>{team ? team.name : ''}</span>
		</div>
	)

}
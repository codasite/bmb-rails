import React, { useState, useContext } from 'react';
import { MatchNode, Round, Team } from '../models/MatchTree';
//@ts-ignore
import { getTeamClasses } from '../utils';
import { bracketConstants } from '../constants';
import { BracketContext } from '../context';
import { TeamSlotProps } from './types';

export const DefaultTeamSlot = (props: TeamSlotProps) => {
	const [editing, setEditing] = useState(false)
	const [textBuffer, setTextBuffer] = useState('')
	const bracket = useContext(BracketContext);

	const {
		team,
		match,
		matchTree,
		setMatchTree,
		position,
		teamHeight,
	} = props
	// console.log('winner', winner)
	return (
		<div>
			<span className={`tw-flex tw-justify-center tw-items-center tw-whitespace-nowrap tw-w-[115px] tw-h-[${teamHeight}px] tw-border-2 tw-border-solid tw-border-white/50 tw-text-14 tw-font-500 tw-text-white/50`}>{team ? team.name : ''}</span>
		</div>
	)

}
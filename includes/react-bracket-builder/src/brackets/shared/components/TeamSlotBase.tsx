import React, { useState, useContext } from 'react';
import { MatchNode, Round, Team } from '../models/MatchTree';
//@ts-ignore
import { getTeamClasses } from '../utils';
import { bracketConstants } from '../constants';
import { BracketContext } from '../context';
import { TeamSlotProps } from './types';

export const TeamSlot = (props: TeamSlotProps) => {
	const [editing, setEditing] = useState(false)
	const [textBuffer, setTextBuffer] = useState('')
	const bracket = useContext(BracketContext);

	const {
		team,
		match,
		matchTree,
		setMatchTree,
	} = props
	// console.log('winner', winner)

}
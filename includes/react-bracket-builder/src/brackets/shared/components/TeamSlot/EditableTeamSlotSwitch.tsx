import React, { useState, useContext } from 'react';
//@ts-ignore
import { TeamSlotProps } from '../types';
import { InactiveTeamSlot } from './InactiveTeamSlot';
import { ActiveTeamSlot } from './ActiveTeamSlot';
import { EditableTeamSlot } from './EditableTeamSlot';

export const EditableTeamSlotSwitch = (props: TeamSlotProps) => {
	const {
		team,
		match,
		teamPosition,
		matchTree,
		setMatchTree,
	} = props

	const editable = teamPosition === 'left' ? match.left === null : match.right === null

	return (
		editable ? <EditableTeamSlot {...props} /> : <InactiveTeamSlot {...props} />
	)
}
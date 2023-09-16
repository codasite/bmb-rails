import React, { useState, useContext } from 'react';
//@ts-ignore
import { TeamSlotProps } from '../types';
import { InactiveTeamSlot } from './InactiveTeamSlot';
import { ActiveTeamSlot } from './ActiveTeamSlot';
import { EditableTeamSlot } from './EditableTeamSlot';
import { BaseTeamSlot } from './BaseTeamSlot';

const DisabledTeamSlot = (props: TeamSlotProps) => {

	return (
		<BaseTeamSlot
			{...props}
			borderColor='white/25'
		/>
	)

}

export const EditableTeamSlotSwitch = (props: TeamSlotProps) => {
	const {
		match,
		teamPosition,
	} = props

	const editable = teamPosition === 'left' ? match.left === null : match.right === null

	return (
		editable ? <EditableTeamSlot {...props} /> : <DisabledTeamSlot {...props} />
	)
}
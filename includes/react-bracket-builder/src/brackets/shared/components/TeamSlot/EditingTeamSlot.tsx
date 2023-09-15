import React, { useState, useContext } from 'react';
//@ts-ignore
import { TeamSlotProps } from '../types';
import { InactiveTeamSlot } from './InactiveTeamSlot';
import { ActiveTeamSlot } from './ActiveTeamSlot';
import { BaseTeamSlot } from './BaseTeamSlot';

export const EditableTeamSlot = (props: TeamSlotProps) => {
	const {
		team,
		match,
		teamPosition,
		matchTree,
		setMatchTree,
	} = props

	return (
		<BaseTeamSlot
			{...props}
			backgroundColor='white/15'
			borderColor='white/50'
			textColor='white'
			onTeamClick={() => { }}
		>
			<span>Add Team</span>
		</BaseTeamSlot>

	)

}
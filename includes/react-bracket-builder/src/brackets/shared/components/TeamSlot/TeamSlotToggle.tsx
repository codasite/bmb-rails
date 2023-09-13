import React, { useState, useContext } from 'react';
//@ts-ignore
import { TeamSlotProps } from '../types';
import { InactiveTeamSlot } from './InactiveTeamSlot';
import { ActiveTeamSlot } from './ActiveTeamSlot';

export const TeamSlotToggle = (props: TeamSlotProps) => {
	const {
		team,
		match,
		teamPosition,
	} = props

	const active = match.getWinner() === team ? true : false

	return (
		active ? <ActiveTeamSlot {...props} /> : <InactiveTeamSlot {...props} />
	)
}
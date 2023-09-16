import React, { useContext } from 'react';
//@ts-ignore
import { DarkModeContext } from '../../context';
import { TeamSlotProps } from '../types';
import { BaseTeamSlot } from './BaseTeamSlot';

export const InactiveTeamSlot = (props: TeamSlotProps) => {
	const darkMode = useContext(DarkModeContext);

	return (
		<BaseTeamSlot
			{...props}
			textColor={darkMode ? 'white' : 'dd-blue'}
			borderColor={darkMode ? 'white/50' : 'dd-blue/50'}
		/>
	)

}
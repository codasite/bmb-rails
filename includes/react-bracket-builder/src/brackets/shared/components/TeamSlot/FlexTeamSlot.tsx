import React, { useState, useContext } from 'react';
//@ts-ignore
import { TeamSlotProps } from '../types';
import { getUniqueTeamClass } from '../../utils';

export const FlexTeamSlot = (props: TeamSlotProps) => {
	const {
		team,
		match,
		teamPosition,
		height,
	} = props

	const active = false

	const baseStyles = [
		'tw-rounded-4',
		`tw-h-[${height}px]`,
	]

	const activeStyles = [
		'tw-bg-white/30',
	]

	const inactiveStyles = [
		'tw-bg-white/10'
	]

	const styles = [...baseStyles, ...(active ? activeStyles : inactiveStyles)].join(' ')

	return (
		<div className={styles}>
		</div>
	)

}
import React from 'react'
import { ActionButton, ActionButtonProps, ActionButtonBase } from '../ActionButtons'
import { BracketActionButtonProps } from '../types'

export const PaginatedBracketButtonBase = (props: ActionButtonProps) => {

	return (
		<ActionButtonBase
			borderRadius={8}
			fontWeight={700}
			fontSize={24}
			height={48}
			{...props}
		/>
	)
}


export const DefaultNextButton = (props: ActionButtonProps) => {
	const {
		disabled
	} = props
	const background = 'transparent'
	const border = disabled ? 'white/20' : 'white'
	const textColor = disabled ? 'white/20' : 'white'

	return (
		<PaginatedBracketButtonBase
			backgroundColor={background}
			textColor={textColor}
			borderColor={border}
			borderWidth={4}
			{...props}
		>Next</PaginatedBracketButtonBase>
	)
}

export const DefaultFinalButton = (props: ActionButtonProps) => {
	const {
		disabled
	} = props
	const background = disabled ? 'white/20' : 'white'
	const textColor = 'dd-blue'
	return (
		<PaginatedBracketButtonBase
			backgroundColor={background}
			textColor={textColor}
			width={300}
			{...props}
		>View Full Bracket</PaginatedBracketButtonBase>
	)
}
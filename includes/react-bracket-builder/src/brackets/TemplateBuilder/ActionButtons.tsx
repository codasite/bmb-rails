import React from 'react';

interface ActionButtonProps {
	disabled?: boolean
	onClick?: () => void
	children?: React.ReactNode
	backgroundColor?: string
	textColor?: string
	padding?: number
	paddingX?: number
	paddingY?: number
	gap?: number
	borderColor?: string
	borderWidth?: number
	borderRadius?: number
	variant?: string
	className?: string
}

export const ActionButtonBase = (props: ActionButtonProps) => {
	const {
		onClick,
		children,
		backgroundColor,
		textColor,
		padding,
		paddingX,
		paddingY,
		gap = 10,
		borderColor,
		borderWidth = 1,
		borderRadius,
		className,
		disabled,

	} = props

	const baseStyles = [
		'tw-flex',
		'tw-flex-row',
		'tw-items-center',
		'tw-justify-center',
	]

	if (!disabled) baseStyles.push('tw-cursor-pointer')
	if (backgroundColor) baseStyles.push(`tw-bg-${backgroundColor}`)
	if (textColor) baseStyles.push(`tw-text-${textColor}`)
	if (gap) baseStyles.push(`tw-gap-${gap}`)

	if (paddingX || paddingY) {
		if (paddingX) baseStyles.push(`tw-px-${paddingX}`)
		if (paddingY) baseStyles.push(`tw-py-${paddingY}`)
	}
	else if (padding) baseStyles.push(`tw-py-${padding}`)

	if (borderColor || borderWidth || borderRadius) {
		baseStyles.push('tw-border-solid')
		if (borderWidth) baseStyles.push(`tw-border${borderWidth > 1 ? '-' + borderWidth : ''}`)
		if (borderColor) baseStyles.push(`tw-border-${borderColor}`)
		if (borderRadius) baseStyles.push(`tw-rounded-${borderRadius}`)
	}

	const extra = className ? className.split(' ') : []

	const styles = [...baseStyles, ...extra].join(' ')

	return (
		<button
			className={styles}
			onClick={onClick}
			disabled={disabled}
		>
			{children}
		</button>
	)
}

export const GreenButton = (props: ActionButtonProps) => {
	const {
		disabled
	} = props
	const background = disabled ? 'transparent' : 'green/15'
	const border = disabled ? 'white/50' : 'green'
	const textColor = disabled ? 'white/50' : 'white'
	return (
		<ActionButtonBase
			{...props}
			backgroundColor={background}
			padding={16}
			textColor={textColor}
			borderRadius={8}
			borderColor={border}
		/>
	)
}

export const BlueButton = (props: ActionButtonProps) => {
	const {
		disabled
	} = props
	const background = disabled ? 'transparent' : 'blue/15'
	const border = disabled ? 'white/50' : 'blue'
	const textColor = disabled ? 'white/50' : 'white'

	return (
		<ActionButtonBase
			{...props}
			backgroundColor={background}
			padding={16}
			textColor={textColor}
			borderRadius={8}
			borderColor={border}
		/>
	)
}

export const ActionButton = (props: ActionButtonProps) => {
	const {
		variant
	} = props

	switch (variant) {
		case 'green':
			return <GreenButton {...props} />
		case 'blue':
			return <BlueButton {...props} />
		default:
			return <ActionButtonBase {...props} />
	}
}

import React from 'react';

interface ActionButtonProps {
	label: string;
	onClick: () => void;
	disabled?: boolean;
	loading?: boolean;
	variant?: 'primary' | 'secondary';
}

export const ActionButton = (props: ActionButtonProps) => {
	const {
		onClick,
		disabled,
		loading,
		label,
		variant,
	} = props;

	return (
		<button className={'wpbb-action-btn' + (disabled ? ' disabled' : '') + (variant === 'secondary' ? ' secondary' : '')} onClick={onClick} disabled={disabled}>
			{props.loading ?
				//@ts-ignore
				<Spinner variant='light' animation="border" role="status" style={{ borderWidth: '4px' }} />
				: label}
		</button>
	)
}
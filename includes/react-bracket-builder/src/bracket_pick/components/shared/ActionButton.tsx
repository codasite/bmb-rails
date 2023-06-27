import React from 'react';

interface ActionButtonProps {
	onClick: () => void;
	disabled?: boolean;
	loading?: boolean;
	label: string;
}

export const ActionButton = (props: ActionButtonProps) => {
	const {
		onClick,
		disabled,
		loading,
		label,
	} = props;

	return (
		<button className={'wpbb-action-btn' + (disabled ? ' disabled' : '')} onClick={onClick} disabled={disabled}>
			{props.loading ?
				//@ts-ignore
				<Spinner variant='light' animation="border" role="status" style={{ borderWidth: '4px' }} />
				: label}
		</button>
	)
}
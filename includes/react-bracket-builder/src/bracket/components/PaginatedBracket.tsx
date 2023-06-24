import React, { useState, useEffect, useRef, forwardRef } from 'react';
import { Button, InputGroup } from 'react-bootstrap';
import { Form } from 'react-bootstrap';
import { Nullable } from '../../utils/types';
import { MatchTree, Round, MatchNode, Team } from '../models/MatchTree';
import LineTo, { SteppedLineTo, Line } from 'react-lineto';
//@ts-ignore
import { ReactComponent as BracketLogo } from '../../assets/BMB-ICON-CURRENT.svg';

interface NavButtonProps {
	onClick?: () => void;
	disabled?: boolean;
	buttonLabel?: string;
	// loading?: boolean;
}

export const NavButton = (props: NavButtonProps) => {
	const {
		disabled,
		onClick = () => { },
		buttonLabel,
		// loading,
	} = props;
	return (
		<button className={'wpbb-nav-btn' + (disabled ? ' disabled' : '')} onClick={onClick} disabled={disabled}>
			{buttonLabel ? buttonLabel : 'NEXT'}
			{/* {props.loading ?
				//@ts-ignore
				<Spinner variant='light' animation="border" role="status" style={{ borderWidth: '4px' }} />
				: 'ADD TO APPAREL'} */}
		</button>
	)
}


interface PairedBracketProps {
	matchTree: MatchTree;
	bracketName?: string;
	canEdit?: boolean;
	canPick?: boolean;
	darkMode?: boolean;
	setMatchTree?: (matchTree: MatchTree) => void;
}


export const PaginatedBracket = (props: PairedBracketProps) => {
	const {
		matchTree,
		bracketName,
		canEdit,
		canPick,
		darkMode,
		setMatchTree,
	} = props;

	return (
		<div className='wpbb-paginated-bracket-container'>
			<div className='wpbb-paginated-bracket'>
				hiiiii
			</div>
		</div>
	)
}
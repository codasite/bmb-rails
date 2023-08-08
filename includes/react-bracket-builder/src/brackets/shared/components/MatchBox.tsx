import React from 'react';
import { MatchNode } from '../models/MatchTree';
import { TeamSlot } from './TeamSlot'
import { Direction } from '../constants'

interface MatchBoxProps {
	match: MatchNode | null;
	direction: Direction;
	height: number;
	spacing?: number;
	updateTeam?: (left: boolean, name: string) => void;
	pickTeam?: (left: boolean) => void;
	roundIndex: number;
	matchIndex: number;
	bracketName?: string;
	children?: React.ReactNode;
}

export const MatchBox = (props: MatchBoxProps) => {
	const {
		match,
		direction,
		height,
		spacing = 0,
		updateTeam,
		pickTeam,
		roundIndex,
		matchIndex,
		bracketName,
		children,
	} = props

	if (match === null) {
		return (
			<div className='wpbb-match-box-empty' style={{ height: height + spacing }} />
		)
	}
	let className: string;

	if (direction === Direction.TopLeft || direction === Direction.BottomLeft) {
		// Left side of the bracket
		className = 'wpbb-match-box-left'
	} else if (direction === Direction.TopRight || direction === Direction.BottomRight) {
		// Right side of the bracket
		className = 'wpbb-match-box-right'
	} else {
		className = 'wpbb-match-box-center'
	}

	const upperOuter = match.left === null
	const lowerOuter = match.right === null

	if (upperOuter && lowerOuter) {
		// First round
		className += '-outer'
	} else if (upperOuter) {
		// Upper bracket
		className += '-outer-upper'
	} else if (lowerOuter) {
		// Lower bracket
		className += '-outer-lower'
	}

	const team1Wins = match.result && match.result.id === match.team1?.id ? true : false
	const team2Wins = match.result && match.result.id === match.team2?.id ? true : false
	const finalMatch = roundIndex === 0 && matchIndex === 0
	const pickedWinner = match.result ? true : false
	// const pickedWinner = true;

	const winnerText = bracketName ? bracketName.split(' ')[0] + ' ' + bracketName.split(' ').slice(-1) : 'WINNER' // hack to get shortend bracket name. Should probably be a separate field

	return (
		<div className={className} style={{ height: height, marginBottom: spacing }}>
			{children}
			<TeamSlot
				winner={team1Wins}
				team={match.team1}
				updateTeam={updateTeam ? (name: string) => updateTeam(true, name) : undefined}
				pickTeam={pickTeam ? () => pickTeam(true) : undefined}
				roundIndex={roundIndex}
				matchIndex={matchIndex}
				left={true}
			/>
			<TeamSlot
				winner={team2Wins}
				team={match.team2}
				updateTeam={updateTeam ? (name: string) => updateTeam(false, name) : undefined}
				pickTeam={pickTeam ? () => pickTeam(false) : undefined}
				roundIndex={roundIndex}
				matchIndex={matchIndex}
				left={false}
			/>
		</div>
	)
}
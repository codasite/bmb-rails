import React from 'react';
import { MatchNode } from '../models/MatchTree';
import { TeamSlot } from './TeamSlot'
//@ts-ignore
import { ReactComponent as BracketLogo } from '../assets/BMB-ICON-CURRENT.svg';
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

	const team1Wins = match.result && match.result === match.team1 ? true : false
	const team2Wins = match.result && match.result === match.team2 ? true : false
	console.log('match', match)
	console.log('team1Wins', team1Wins)
	console.log('team2Wins', team2Wins)
	const finalMatch = roundIndex === 0 && matchIndex === 0
	const pickedWinner = match.result ? true : false
	// const pickedWinner = true;

	const winnerText = bracketName ? bracketName.split(' ')[0] + ' ' + bracketName.split(' ').slice(-1) : 'WINNER' // hack to get shortend bracket name. Should probably be a separate field
	// const winnerText = 'WINNER'
	// const winnerText = bracketName ? bracketName : 'WINNER'
	const getWinnerText = () => {
		if (bracketName) {
			const bracketNameSplit = bracketName.split(' ')
			if (bracketNameSplit.length > 1) {
				return `${bracketNameSplit[0]} ${bracketNameSplit[bracketNameSplit.length - 1]}`
			}
			return bracketName
		}
		return 'WINNER'
	}

	return (
		<div className={className} style={{ height: height, marginBottom: spacing }}>
			{finalMatch &&
				[<div className='wpbb-winner-container'>
					<span className={'wpbb-winner-text' + (pickedWinner ? ' visible' : ' invisible')}>{getWinnerText()}</span>
					<TeamSlot
						className={'wpbb-team wpbb-final-winner' + (pickedWinner ? ' wpbb-match-winner' : '')}
						team={match.result}
					/>
				</div>,
				<BracketLogo className="wpbb-bracket-logo" />
				]
			}
			<TeamSlot
				// className='wpbb-team1'
				winner={team1Wins}
				team={match.team1}
				updateTeam={updateTeam ? (name: string) => updateTeam(true, name) : undefined}
				pickTeam={pickTeam ? () => pickTeam(true) : undefined}
				roundIndex={roundIndex}
				matchIndex={matchIndex}
				left={true}
			/>
			<TeamSlot
				// className='wpbb-team2'
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